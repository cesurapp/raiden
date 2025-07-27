<?php

namespace App\Admin\Notification\Cron;

use App\Admin\Notification\Entity\Scheduler;
use App\Admin\Notification\Enum\SchedulerStatus;
use App\Admin\Notification\Repository\DeviceRepository;
use App\Admin\Notification\Repository\SchedulerRepository;
use App\Admin\Notification\Task\NotificationTask;
use Doctrine\ORM\AbstractQuery;
use Cesurapp\SwooleBundle\Cron\AbstractCronJob;
use Swoole\Coroutine;
use Swoole\Coroutine\WaitGroup;
use Symfony\Component\Uid\Uuid;

/**
 * Send Scheduled Notification.
 */
class SchedulerCron extends AbstractCronJob
{
    public string $TIME = '@EveryMinute';

    public function __construct(
        private readonly SchedulerRepository $repo,
        private readonly DeviceRepository $deviceRepo,
        private readonly NotificationTask $notificationTask,
    ) {
    }

    public function __invoke(): void
    {
        // Disable SQL Logger
        $this->repo->connection()->getConfiguration()->setMiddlewares([]);

        // Process
        $scheduler = $this->repo->getProcessed()->getQuery();
        foreach ($scheduler->toIterable() as $scheduled) {
            try {
                $this->repo->add($scheduled->setStatus(SchedulerStatus::PROCESSING));

                // Send
                $this->createPersistentNotificaiton($scheduled);
                $this->sendNotification($scheduled);

                $this->repo->merge($scheduled->setStatus(SchedulerStatus::SENDED));
            } catch (\Throwable) {
                $this->repo->merge($scheduled->setStatus(SchedulerStatus::ERROR));
            }
        }

        // Clear All
        $this->repo->em()->clear();
    }

    /**
     * Send Notification to All Devices.
     */
    private function sendNotification(Scheduler $scheduler): void
    {
        // Send Notification
        $batch = [];
        $devices = $scheduler->getDeviceQuery($this->deviceRepo->createQueryBuilder('q'))->getQuery();
        $notification = $scheduler->getNotification();
        foreach ($devices->toIterable([], AbstractQuery::HYDRATE_SIMPLEOBJECT) as $index => $device) {
            $batch[] = function () use ($notification, $device) {
                try {
                    return false !== call_user_func($this->notificationTask, [
                        'notification' => $notification,
                        'device' => $device,
                    ]);
                } catch (\Throwable) {
                    return false;
                }
            };

            // Wait 500 Item and Send
            if ($index > 0 && ($index % 500) === 0) {
                $this->initResult($scheduler, $batch);
            }
        }

        $this->initResult($scheduler, $batch);
    }

    private function initResult(Scheduler $scheduler, array &$batch): void
    {
        if (count($batch) > 0) {
            $result = $this->batch($batch);
            $batch = [];

            // Update Count
            $scheduler
                ->incDeliveredCount(count(array_filter($result, static fn ($i) => true === $i)))
                ->incFailedCount(count(array_filter($result, static fn ($i) => false === $i)));

            $this->repo->em()->clear();
        }
    }

    /**
     * Create Persistent Notification all Device User.
     */
    public function createPersistentNotificaiton(Scheduler $scheduler): void
    {
        if ($scheduler->isPersistNotification()) {
            go(function () use ($scheduler) {
                // Find User IDs
                $userIds = $scheduler
                    ->getDeviceQuery($this->deviceRepo->createQueryBuilder('q'))
                    ->distinct()
                    ->select('u.id')
                    ->getQuery();

                $notification = $scheduler->getNotification();
                $conn = $this->repo->connection();

                foreach ($userIds->toIterable([], AbstractQuery::HYDRATE_SCALAR) as $userId) {
                    $conn->createQueryBuilder()
                        ->insert('notification')
                        ->values([
                            'id' => '?',
                            'title' => '?',
                            'message' => '?',
                            'status' => '?',
                            'readed' => '?',
                            'data' => '?',
                            'owner_id' => '?',
                        ])
                        ->setParameters([
                            0 => Uuid::v7()->toString(),
                            1 => $notification->getTitle(),
                            2 => $notification->getMessage(),
                            3 => $notification->getStatus()->value,
                            4 => (int) $notification->isReaded(),
                            5 => json_encode($notification->getData(), JSON_THROW_ON_ERROR),
                            6 => $userId['id'],
                        ])
                        ->executeStatement();
                }
            });
        }
    }

    private function batch(array $tasks, float $timeout = -1): array
    {
        $wg = new WaitGroup(count($tasks));
        foreach ($tasks as $id => $task) {
            Coroutine::create(static function () use ($wg, &$tasks, $id, $task) {
                $tasks[$id] = null;
                $tasks[$id] = $task();
                $wg->done();
            });
        }
        $wg->wait($timeout);

        return $tasks;
    }
}
