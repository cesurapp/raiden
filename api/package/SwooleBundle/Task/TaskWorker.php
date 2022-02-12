<?php

namespace Package\SwooleBundle\Task;

use Package\SwooleBundle\Repository\FailedTaskRepository;
use Symfony\Component\DependencyInjection\ServiceLocator;

class TaskWorker
{
    public function __construct(
        private ServiceLocator $locator,
        private FailedTaskRepository $failedTaskRepo
    ) {
    }

    public function handle(array $taskRequest): void
    {
        $task = $this->getTask($taskRequest);

        try {
            $task($taskRequest['payload']);
        } catch (\Exception $exception) {
            $this->failedTaskRepo->createTask($task, $taskRequest['payload'], $exception);
        }
    }

    /**
     * Find Task Instance.
     */
    private function getTask(array $taskRequest): TaskInterface
    {
        if (!isset($taskRequest['class'], $taskRequest['payload']) || !$this->locator->has($taskRequest['class'])) {
            throw new TaskNotFoundException();
        }

        return $this->locator->get($taskRequest['class']);
    }
}
