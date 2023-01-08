# Raiden Admin
High concurrency, OpenSwoole based, Symfony 6 & Admin Dashboard. 

Requirement
--------------------
* Postgres 14+
* Composer 2+
* PHP 8.1+
  * Swoole 4.11+ (`pecl install openswoole`)
  * Imagick (`pecl install imagick`)
  * Php.ini
    * max_file_uploads=50
    * post_max_size=20M
    * upload_max_filesize=20M
    * memory_limit=512M
    * realpath_cache_size=16M
    * realpath_cache_ttl=600
    * opcache.enable=1
    * opcache.enable_cli=1
    * opcache.memory_consumption=512
    * opcache.interned_strings_buffer=64
    * opcache.max_accelerated_files=50000
    * opcache.validate_timestamps=0 (ONLY PROD)
    * opcache.preload=../api/config/preload.php (ONLY PROD)
    * opcache.preload_user=root (ONLY PROD)
    * opcache.jit=tracing
    * opcache.jit_buffer_size=256M

Install
--------------------
__Development for MacOS__
```shell
# MacOS Extension (Required for development server)
brew install fswatch
brew install util-linux

# Install PHP Extension
pecl install openswoole # openssl - http2 - curl - postgresql
pecl install imagick

# Configure Environment
cp .env .env.local
cp .server.php .server.local.php

# Install Dependency
composer install-tool # (PHPStan - PHPCs Fixer)
composer install

# Create PostgreSql Database
bin/console doctrine:database:create
bin/console doctrine:schema:update
```

__Production__
```shell
composer install --no-dev
composer dump-autoload --no-dev --classmap-authoritative
```

Run Tests
--------------------
__Configure Local PHPUnit__
```shell
cp .phpunit.xml.dist .phpunit.xml
```

__Commands__
```shell
composer analyse    # PHPStan Analysis
composer fix        # PHP-Cs-Fixer Fix
composer test       # PHPUnit Test
composer test-stop  # PHPUnit Test stop First Error
```

Packages
--------------------
* [Swoole Server](package/SwooleBundle/README.md)
* [Storage Bundle](package/StorageBundle/README.md)
* [Media Bundle](package/MediaBundle/README.md)
* [Api Bundle](package/ApiBundle/README.md)
* [Library (Coroutine HTTP Client)](package/Library/README.md)