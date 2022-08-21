* UI -> Reload User -> ROLE_ADMIN
* UI -> Switch User -> ROLE_ALLOWED_TO_SWITCH
*
* Disable SuperAdmin Remove to Admin Type
* Disable SuperAdmin Edit to Admin Type
* Disable SuperAdmin Create to Admin Type
* Disable SuperAdmin Permission Edit
* 
* 
* DataTable
* -- Filters
* -- Excel Export
* -- Excel Import
*
* Cron Multiple Server Init
* Readme

## Requirement
* Composer 2+
* PHP 8.1+
  * Swoole 4.11+ (`pecl install openswoole`)
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
# MacOS Extension
brew install fswatch
brew install util-linux

# PECL Extension
pecl install uuid # directory => /opt/homebrew/opt/util-linux | /usr/local/Cellar/util-linux/2.37.3
pecl install openswoole # openssl - http2 - curl
pecl install imagick

cp .env .env.local
composer install-tool
composer install
bin/console doctrine:database:create
bin/console doctrine:schema:update
```
* ### Production
```shell
composer install dump-autoload --no-dev --classmap-authoritative
cp .env .env.local
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