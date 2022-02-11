<?php

namespace Package\SwooleBundle\Task;

use Symfony\Component\DependencyInjection\ServiceLocator;

class TaskWorker
{
    public function __construct(private ServiceLocator $locator)
    {
    }

    public function handle(array $taskRequest): void
    {
        $task = $this->getTask($taskRequest);

        try {
            $task($taskRequest['payload']);
        } catch (\Exception $exception) {
            $this->failedTask($taskRequest, $exception);
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

    /**
     * Save to Failed Task.
     */
    private function failedTask(array $taskRequest, \Exception $exception): void
    {
    }
}
