* Excel Export - İmport -> STREAM
* 
* PHP Unit Test
* 
* Concurrent Process -> Test
* Timed Process
* 
* Gitlab CI


* Date Formatter
* Test -> PHP UNIT
* Translator -> SYMFONY TRANSLATOR


* Core -> Notification {SMS, MAIL, SYSTEM }
* Core -> JWT Auth
* Core -> UUID



## Commands
```shell
bin/console app:cache-clear                 # Flush Symfony & Doctrine Cache
bin/console server start|stop|restart|watch # Server Command
```

## Requirement
* pgSql 14+
* PHP 8.1+
  * Swoole 4.8+ (pecl install openswoole | swoole --enable-debug-log)
  * Xlswriter (pecl install xlswriter)
  * Opcache
    * opcache.enable=1
    * opcache.enable_cli=1
    * opcache.memory_consumption=512
    * opcache.max_accelerated_files=50000
    * opcache.validate_timestamps=0 (only prod)
    * opcache.preload=../api/config/preload.php (only prod)

## Development Install
```shell
brew install fswatch # MacOS File Watcher using Hot Reload Swoole Server
```

## Prod Install
```shell
composer install dump-autoload --no-dev --classmap-authoritative
```

## Tests
```shell
composer analyse  # PHPStan Analysis
composer test     # PHPUnit Test
```