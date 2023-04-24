<?php

namespace App\Admin\Notification\Cron;

use App\Admin\Notification\Entity\Scheduler;
use App\Admin\Notification\Enum\SchedulerStatus;
use App\Admin\Notification\Repository\DeviceRepository;
use App\Admin\Notification\Repository\SchedulerRepository;
use App\Admin\Notification\Task\NotificationTask;
use Doctrine\ORM\AbstractQuery;
use OpenSwoole\Core\Coroutine\WaitGroup;
use Package\SwooleBundle\Cron\AbstractCronJob;
use Symfony\Component\Uid\Ulid;

/**
 * Send Scheduled Notification.
 */
class SchedulerCron extends AbstractCronJob
{
    public string $TIME = '@EveryMinute';

    public function __construct(
        private readonly SchedulerRepository $repo,
        private readonly DeviceRepository $deviceRepo,
        private readonly NotificationTask $notificationTask
    ) {
    }

    public function __invoke(): void
    {
        /** @var Scheduler[] $scheduler */
        $scheduler = $this->repo->getProcessed()->getQuery()->getResult();
        if (!$scheduler) {
            return;
        }

        // Disable SQL Logger
        $this->repo->connection()->getConfiguration()->setMiddlewares([]);

        // Process
        foreach ($scheduler as $scheduled) {
            try {
                $this->repo->add($scheduled->setStatus(SchedulerStatus::PROCESSING));
                $this->sendNotification($scheduled);
                $this->repo->add($scheduled->setStatus(SchedulerStatus::SENDED));
            } catch (\Throwable) {
                $this->repo->add($scheduled->setStatus(SchedulerStatus::ERROR));
            }
        }
        $this->repo->em()->clear();
    }

    /**
     * Send Notification to All Devices.
     */
    private function sendNotification(Scheduler $scheduler): void
    {
        // Send Notification
        $devices = $scheduler
            ->getDeviceQuery($this->deviceRepo->createQueryBuilder('q'))
            ->getQuery();

        $counter = 0;
        $waitGroup = new WaitGroup();
        foreach ($devices->toIterable([], AbstractQuery::HYDRATE_SIMPLEOBJECT) as $device) {
            ++$counter;
            go(function () use ($waitGroup, $device, $scheduler) {
                $waitGroup->add();
                usleep(1000);

                // Send
                try {
                    $result = call_user_func($this->notificationTask, [
                        'notification' => $scheduler->getNotification(),
                        'device' => $device,
                    ]);
                } catch (\Throwable) {
                    $result = false;
                }

                // Result Counter
                if (false !== $result) {
                    $scheduler->incDeliveredCount();
                } else {
                    $scheduler->incFailedCount();
                }

                $waitGroup->done();
            });

            // Wait 250 Item and Update Scheduler
            if (($counter % 250) === 0) {
                $counter -= 250;
                $waitGroup->wait($counter);
                $this->repo->add($scheduler);
            }
        }

        // Finish & Update Scheduler
        if ($counter > 0) {
            $waitGroup->wait($counter);
        }
    }

    /**
     * Create Persistent Notification all Device User.
     */
    public function createPersistentNotificaiton(Scheduler $scheduler): void
    {
        // Find User IDs
        $userIds = $scheduler
            ->getDeviceQuery($this->deviceRepo->createQueryBuilder('q'))
            ->distinct()
            ->select('u.id')
            ->getQuery();

        $notification = $scheduler->getNotification();
        $conn = $this->repo->connection();

        foreach ($userIds->toIterable([], 3) as $userId) {
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
                    0 => (new Ulid())->toRfc4122(),
                    1 => $notification->getTitle(),
                    2 => $notification->getMessage() ?? '',
                    3 => $notification->getStatus()->value,
                    4 => (int) $notification->isReaded(),
                    5 => json_encode($notification->getData(), JSON_THROW_ON_ERROR),
                    6 => $userId['id'],
                ])
                ->executeStatement();
        }
    }
}
