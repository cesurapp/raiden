<?php

namespace Package\SwooleBundle\Task;

use Package\SwooleBundle\Repository\FailedTaskRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;

readonly class TaskWorker
{
    public function __construct(
        private ServiceLocator $locator,
        private LoggerInterface $logger,
        private FailedTaskRepository $failedTaskRepo,
    ) {
    }

    public function handle(array $taskRequest): void
    {
        try {
            $task = $this->getTask($taskRequest);
            $task($taskRequest['payload']);

            $this->logger->info('Success Task: '.$taskRequest['class'], $taskRequest);
        } catch (\Exception $exception) {
            $this->failedTaskRepo->createTask($taskRequest, $exception);
            $this->logger->critical('Failed Task: '.$taskRequest['class'].' Exception: '.$exception->getMessage(), $taskRequest);
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
     * Get All Tasks.
     */
    public function getAll(): \Traversable
    {
        foreach ($this->locator->getProvidedServices() as $id => $val) {
            yield $this->locator->get($id);
        }

        return null;
    }
}
