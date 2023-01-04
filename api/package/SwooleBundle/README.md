## Swoole Bundle

### Package Configuration
```shell
# config/packages/swoole.yaml

swoole:
  failed_task_retry: '@EveryMinute10'
  failed_task_attempt: 1 # Failed Task Retry Count
```

### Commands
```shell
# Cron Commands
bin/console cron:list     # List Cron Jobs

# Server Commands
bin/console server:start  # Start Swoole Server
bin/console server:stop   # Stop Swoole Server
bin/console server:status # Status Swoole Server
bin/console server:watch  # Watch Swoole Server

# Task|Job Commands
bin/console task:list           # List Registered Tasks
bin/console task:failed:clear   # Watch Swoole Server
bin/console task:failed:retry   # Send all failed tasks to queue.
bin/console task:failed:view    # Lists failed tasks.
```

### Create Cron Job
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

### Create Task (Background Job)
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