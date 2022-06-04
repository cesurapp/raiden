## Swoole Bundle

### Commands
```shell
bin/console cron:list     # List Cron Jobs

bin/console server:start  # Start Swoole Server
bin/console server:stop   # Stop Swoole Server
bin/console server:status # Status Swoole Server
bin/console server:watch  # Watch Swoole Server

bin/console task:failed:clear   # Watch Swoole Server
bin/console task:failed:retry   # Send all failed tasks to queue.
bin/console task:failed:view    # Lists failed tasks.
bin/console task:list           # List Registered Tasks
```

### Create Cron Job
```php
class ExampleJob implements \Package\SwooleBundle\Cron\CronInterface {
    /**
     * @see CronInterface
     */
    public const TIME = '@EveryMinute10';

    /**
     * Cron is Enable|Disable.
     */
    public const ENABLE = true;
}
```

### Create Task 
```php
class ExampleTask implements \Package\SwooleBundle\Task\TaskInterface {
    public function __invoke(mixed $data = null): void {
        
    }
}
```