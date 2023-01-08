# Swoole Bundle
Built-in Swoole http server, background jobs (Task), scheduled task (Cron) worker are available.
Failed jobs are saved in the database to be retried. Each server has built-in background task worker.
Scheduled tasks run simultaneously on all servers. It is not possible for tasks to run at the same time as locking is used.


### Package Configuration
```shell
# config/packages/swoole.yaml
swoole:
  failed_task_retry: '@EveryMinute10'
  failed_task_attempt: 1 # Failed Task Retry Count
```

### Server Commands
```shell
# Cron Commands
bin/console cron:list     # List cron jobs

# Server Commands
bin/console server:start  # Start http server
bin/console server:stop   # Stop http server
bin/console server:status # Status http server
bin/console server:watch  # Start http server for development mode (file watcher enabled)

# Task|Job Commands
bin/console task:list           # List registered tasks
bin/console task:failed:clear   # Clear all failed task
bin/console task:failed:retry   # Forced send all failed tasks to swoole task worker
bin/console task:failed:view    # Lists failed tasks
```

### Create Cron Job
You can use cron expression for scheduled tasks, or you can use predefined expressions.

```php
/**
 * Predefined Scheduling
 *
 * '@yearly'    => '0 0 1 1 *',
 * '@annually'  => '0 0 1 1 *',
 * '@monthly'   => '0 0 1 * *',
 * '@weekly'    => '0 0 * * 0',
 * '@daily'     => '0 0 * * *',
 * '@hourly'    => '0 * * * *',
 * '@EveryMinute'    => 'w* * * * *',
 * "@EveryMinute5'  => '*\/5 * * * *',
 * '@EveryMinute10'  => '*\/10 * * * *',
 * '@EveryMinute15'  => '*\/15 * * * *',
 * '@EveryMinute30'  => '*\/30 * * * *',```
 */
class ExampleJob implements \Package\SwooleBundle\Cron\AbstractCronJob {
    /**
     * @see AbstractCronJob
     */
    public string $TIME = '@EveryMinute10';

    /**
     * Cron is Enable|Disable.
     */
    public bool $ENABLE = true;
    
    /**
     * Cron Context 
     */
    public function __invoke(): void {
    
    }
}
```

### Create Task (Background Job or Queue)
Data passed to jobs must be of type string, int, bool, array, objects cannot be serialized.

Create: 
```php
class ExampleTask implements \Package\SwooleBundle\Task\TaskInterface {
    public function __invoke(mixed $data = null): void {
        
    }
}
```

Handle Task:
```php
public function hello(\Package\SwooleBundle\Task\TaskHandler $taskHandler) {
    $taskHandler->dispatch(ExampleTask::class, [
        'name' => 'Test',
        'invoke' => 'Data'
    ]);
}
```