* Github CI Test
* 
* 
* Core -> JWT Auth
* Core -> User Management
* Core -> User Group Management
* Core -> Notification {SMS, MAIL, SYSTEM }
* Core -> Permissions
* Core -> UI
* 
* 
* * DataTable
* -- Filters
* -- Excel Export
* -- Excel Import
* 
* Upload Manager (Bundle)

## Requirement
* Composer 2+
* PHP 8.1+
  * Swoole 4.10+ (`pecl install openswoole`)
  * Xlswriter (`pecl install xlswriter`)
  * UUID (`pecl install uuid`)
  * Imagick (`pecl install imagick`)
  * Opcache
    * opcache.enable=1
    * opcache.enable_cli=1
    * opcache.memory_consumption=512
    * opcache.max_accelerated_files=50000
    * opcache.validate_timestamps=0 (ONLY PROD)
    * opcache.preload=../api/config/preload.php (ONLY PROD)
  * JIT
    * opcache.jit_buffer_size=256M
    * opcache.jit=tracing
* Postgres 14+

## 1. Install
* ### Dev for Macos
```shell
brew install fswatch
brew install util-linux && pecl install uuid # directory => /opt/homebrew/opt/util-linux | /usr/local/Cellar/util-linux/2.37.3
cp .env .env.local
composer install-tool
composer install
```
* ### Production
```shell
cp .env .env.local
composer install dump-autoload --no-dev --classmap-authoritative
```

## 2. Run Tests
```shell
composer analyse  # PHPStan Analysis
composer fix      # PHP-Cs-Fixer Fix
composer test     # PHPUnit Test
```

## 3. Commands
* ### Swoole HTTP Server
```shell
bin/console server:start  # Start HTTP Server
bin/console server:stop   # Stop Server
bin/console server:watch  # Start Server Watch Mode only Macos
bin/console server:status # Tracing Swoole Server
```
* ### Swoole Task (Queue)
```shell
bin/console task:list         # List Tasks
bin/console task:failed-view  # View Failed Tasks
bin/console task:failed-clear # Clear Failed Tasks
bin/console task:failed-retry # Send Failed Task to Swoole Task Worker
```
* ### Swoole Cron Jobs
```shell
bin/console cron:list # List Cron Jobs
```