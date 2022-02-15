* Improvement Status Command
* Server Log Merge App
* 
* DTO
* ApiResponse
* ApiCacheResponse
* Paginator
* Data Filters
* Doctrine Shortcuts
* -- Count
* -- 
* -- 
* Gitlab CI

* DataTable
* -- Columns & Filter System
* -- Excel Export - İmport -> STREAM
* Core -> Notification {SMS, MAIL, SYSTEM }
* Core -> JWT Auth

## Requirement
* pgSql 14+
* PHP 8.1+
  * Swoole 4.9+ (pecl install openswoole)
  * Xlswriter (pecl install xlswriter)
  * UUID (pecl install uuid)
  * Opcache
    * opcache.enable=1
    * opcache.enable_cli=1
    * opcache.memory_consumption=512
    * opcache.max_accelerated_files=50000
    * opcache.validate_timestamps=0 (only prod)
    * opcache.preload=../api/config/preload.php (only prod)


## MacOS Dev Requirements
```shell
brew install fswatch
brew install util-linux
pecl install uuid # directory => /opt/homebrew/opt/util-linux | /usr/local/Cellar/util-linux/2.37.3
pecl install xlswriter
pecl install redis
pecl install openswoole
pecl install redis
```

## Prod Install
```shell
composer install dump-autoload --no-dev --classmap-authoritative
```

## Tests
```shell
composer analyse  # PHPStan Analysis
composer fix      # PHP-Cs-Fixer Fix
composer test     # PHPUnit Test
```

## Commands
```shell
bin/console app:cache-clear                 # Flush Symfony & Doctrine Cache
bin/console server start|stop|restart|watch # Server Command
```