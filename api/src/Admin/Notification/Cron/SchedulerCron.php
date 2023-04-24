<?php

namespace App\Admin\Notification\Cron;

use App\Admin\Notification\Entity\Scheduler;
use App\Admin\Notification\Enum\SchedulerStatus;
use App\Admin\Notification\Repository\DeviceRepository;
use App\Admin\Notification\Repository\SchedulerRepository;
use App\Admin\Notification\Task\NotificationTask;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query;
use OpenSwoole\Core\Coroutine\WaitGroup;
use OpenSwoole\Coroutine;
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

        // Change Status from Processing
        foreach ($scheduler as $scheduled) {
            $scheduled->setStatus(SchedulerStatus::PROCESSING);
            $this->repo->add($scheduled);
        }

        // Process
        foreach ($scheduler as $scheduled) {
            $this->sendNotification($scheduled);

            /*if ($scheduled->isPersistNotification()) {
                $this->createPersistentNotificaiton($scheduled);
            }*/
        }
    }

    /**
     * Send Notification to All Devices.
     */
    private function sendNotification(Scheduler $scheduler): void
    {
        // Send Notification
        $devices = $scheduler
            ->getDeviceQuery($this->deviceRepo->createQueryBuilder('q'))
            ->getQuery()
            ->setHint(Query::HINT_INCLUDE_META_COLUMNS, true);

        $counter = 0;
        $success = 0;
        $waitGroup = new WaitGroup();
        foreach ($devices->toIterable([], AbstractQuery::HYDRATE_SIMPLEOBJECT) as $device) {
            ++$counter;
            go(function () use ($waitGroup, &$success, $device, $scheduler) {
                $waitGroup->add();

                // Send
                Coroutine::usleep(2);
                $result = call_user_func(
                    $this->notificationTask,
                    ['notification' => $scheduler->getNotification(), 'device' => $device]
                );
                if (false !== $result) {
                    ++$success;
                }

                $waitGroup->done();
            });
        }

        // Wait All Coroutine
        $waitGroup->wait($counter);

        // Update Scheduler
        $scheduler
            ->setStatus(SchedulerStatus::SENDED)
            ->setDeliveredCount($success)
            ->setFailedCount($counter - $success);
        $this->repo->add($scheduler);
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
