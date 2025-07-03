# Raiden API

High concurrency, Swoole based, Symfony 7 Api

__Features__
* Swoole Http Server, Task Worker (Queue), Cron Worker [SwooleBundle](https://github.com/cesurapp/swoole-bundle)
* Auto generated __API__ documentation [ApiBundle](https://github.com/cesurapp/api-bundle)
* Api __TypeScript__ client generator
* Github actions auto deployment
* Horizontally scalable
* Firebase integration for notifications
* Firebase bulk notification sender
* Email/Phone verify system
* Cloudflare, Backblaze, Local drives for storage integration [StorageBundle](https://github.com/cesurapp/storage-bundle)
* Role based authentication
* Enum permission system
* Multi language support

__Requirement__
* Postgres 14+
* Composer 2+
* PHP 8.3+
  * Swoole 4.11+ (`pecl install swoole`) 
  * Imagick (`pecl install imagick`)
  * Intl (`pecl install intl`)
  * Php.ini
    * max_file_uploads=50
    * post_max_size=20M
    * upload_max_filesize=20M
    * memory_limit=512M
    * realpath_cache_size=16M
    * realpath_cache_ttl=600
    * opcache.enable=1
    * opcache.enable_cli=1
    * opcache.memory_consumption=256
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
pecl install swoole # openssl - http2 - curl - postgresql

# Configure Environment
cp .env .env.local

# Install Dependency
composer install

# Create PostgreSql Database
bin/console doctrine:database:create
bin/console doctrine:schema:update --force
```

#### Production using Docker

1. Clone the repository
 
   ```shell
   git clone --depth 1 <repo>
   composer install --no-dev
   composer dump-autoload --no-dev --classmap-authoritative
   ```
2. Configure Environment (Optional)

   ```shell
   cp .env .env.local
   ```
3. Build Dockerfile & Run

   ```shell
   docker build -t raiden-api .
   docker run -d -p 80:80 --restart always --name raiden-container raiden-api
   ```
4. Start Migration or Commands

   ```shell
   docker exec raiden-container "bin/console doctrine:database:create"
   docker exec raiden-container "bin/console doctrine:schema:update --force --complete"
   ```
#### Github Actions Deployment

>You can deploy to multiple servers. Just provide the IP list. The application image is first sent to the Github
Container Registry and then deployed to the servers via ssh.

1. Generate SSH key for Deployment

   ```shell
   ssh-keygen -t ed25519 -C "your_email@example.com"
   ```
2. Create Actions Secrets (Repo -> Settings -> Secret and Variables)

   ```shell
   SERVER_PRIVATEKEY: SSH Private Key
   ```
3. Create Host in Variables (Repo -> Settings -> Secret and Variables)

   ```shell
   APP_HOSTS: ["11.111.222.222"]
   ```
4. Create Application Env in Variables (Repo -> Settings -> Secret and Variables)

   ```shell
   APP_ENVS: [
     "APP_ENV=prod",
     "DATABASE_URL=postgres://cesur:@127.0.0.1:5432/raiden?charset=utf8&serverVersion=14"
     "LOCK_DSN=semaphore"
   ]
   ```
5. Create __Production__ branch and run __Deployer__ action.

#### Github Actions Staging Deploy

>You can deploy to single server. Just provide the IP address. The application image is first sent to the Github Container Registry and then deployed to the server via ssh. PostgreSql container is created automatically.

1. Generate SSH key for Deployment

   ```shell
   ssh-keygen -t ed25519 -C "your_email@example.com"
   ```
2. Create Actions Secrets (Repo -> Settings -> Secret and Variables)

   ```shell
   SERVER_PRIVATEKEY: SSH Private Key
   ```
3. Create Host in Variables (Repo -> Settings -> Secret and Variables)

   ```shell
   STAG_HOSTS: 11.111.222.222
   ```
4. Create Application Env in Variables (Repo -> Settings -> Secret and Variables)

   ```shell
   STAG_ENVS: [
     "POSTGRES_PASSWORD=123123123" 
     "APP_ENV=prod",
     "APP_SECRET=hsadgjh231",
     "APP_JWT_SECRET=askjdhask",
     "DATABASE_URL=postgresql+advisory://postgres:123123123@postgres:5432/postgres?serverVersion=14",
     "SERVER_HTTP_SETTINGS_WORKER_NUM=2",
     "SERVER_HTTP_SETTINGS_TASK_WORKER_NUM=2",
     "FIREBASE_DSN=firebase://KEY@default",
   ]
   ```
5. Create __Staging__ branch and run __Staging__ action.

Run Tests
--------------------
__Configure Local PHPUnit__

```shell
cp .phpunit.xml.dist .phpunit.xml
```

__Commands__

```shell
composer qa:phpstan    # PhpStan Analysis
composer qa:lint       # PhpCsFixer Linter
composer qa:fix        # PhpCsFixer Fix
composer test          # PhpUnit Test
composer test:stop     # PhpUnit Test stop first error
composer fix           # Run All QA & Test
```

Documentation
--------------------
### Packages

* [Swoole Server Bundle](https://github.com/cesurapp/swoole-bundle)
* [Storage Bundle](https://github.com/cesurapp/storage-bundle)
* [Media Bundle](https://github.com/cesurapp/media-bundle)
* [Api Bundle](https://github.com/cesurapp/api-bundle)

### Realtime Notification (Firebase)

__Configure__
1. Create Firebase Project [Open Firebase Console](https://console.firebase.google.com/)
2. Open -> Project Settings -> Cloud Messaging
3. Enable -> Cloud Messaging API (Legacy)
4. Configure .env ``FIREBASE_DSN=firebase://<ServerKey>@default``

__Send Notification__
```php
public function test(NotificationPusher $pusher)
{
    $pusher->send(
        (new Notification())
            ->setTitle('Test')
            ->setMessage('Test Message')
            ->setOwner($user) // Not Required
    )
    
    /*
     * Only Firebase sends it. It is not added to system notifications. 
     */
    $pusher->onlySend(
        (new \App\Admin\Notification\Entity\Device())
            ->setToken('Device Token')
            ->setType(\App\Admin\Notification\Enum\DeviceType::IOS),
        (new Notification())
            ->setTitle('Test')
            ->setMessage('Test Message')
            ->setOwner($user) // Not Required
    )
}
```

### Mail & Sms Pusher
__Send Mail__
```php
public function sendMail(MailPusher $pusher)
{
    $pusher->send(
        (new \Symfony\Component\Mime\Email())
            ->to('test@test.com')
            ->from('test@test.com')
            ->subject('Subject')
            ->html('Body')
    );
}
```

__Send SMS__
```php
public function sendSms(\App\Admin\Core\Service\SmsPusher $pusher)
{
    $pusher->send(5111111111, 90, 'Subject, Content');
}
```
