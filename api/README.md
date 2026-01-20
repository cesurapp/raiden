# Raiden API

High concurrency, Swoole based, Symfony 8 API

__Features__
* Swoole HTTP Server, Task Worker (Queue), Cron Worker [SwooleBundle](https://github.com/cesurapp/swoole-bundle)
* Auto-generated API documentation [ApiBundle](https://github.com/cesurapp/api-bundle)
* API TypeScript client generator
* GitHub Actions auto deployment
* Horizontally scalable
* Firebase push notifications
* Email/SMS OTP verification
* Media upload & processing [MediaBundle](https://github.com/cesurapp/media-bundle)
* Storage: Cloudflare R2, Backblaze B2, Local [StorageBundle](https://github.com/cesurapp/storage-bundle)
* JWT authentication with refresh tokens
* Role-based access control (RBAC)
* Enum-based permission system
* Multi-language support (i18n)
* Organization/tenant management
* User switch & impersonation
* PHPUnit tests with fixtures

__Requirements__
* PostgreSQL 14+
* Composer 2+
* PHP 8.4+
  * Swoole 4.11+ (`pecl install swoole`)
  * GD extension (`--with-gd`)
  * Required extensions: intl, bcmath, curl, ctype, iconv
  * php.ini configuration:
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

Installation
--------------------
__Development (macOS)__

```shell
# macOS utilities (for hot-reload)
brew install fswatch util-linux

# PHP extensions
pecl install swoole # enable: openssl, http2, curl, postgresql

# Setup
cp .env .env.local
composer install

# Database
bin/console doctrine:database:create
bin/console doctrine:schema:update --force

# Run dev server (with hot-reload)
bin/console swoole:server:start
```

__Production (Docker)__

```shell
# Build
git clone --depth 1 <repo>
composer install --no-dev
composer dump-autoload --no-dev --classmap-authoritative

# Configure (optional)
cp .env .env.local

# Docker
docker build -t raiden-api .
docker run -d -p 80:80 --restart always --name raiden-api raiden-api

# Migrations
docker exec raiden-api bin/console doctrine:database:create
docker exec raiden-api bin/console doctrine:schema:update --force
```
__GitHub Actions Deployment__

Deploy to multiple servers via GitHub Container Registry.

1. Generate SSH key: `ssh-keygen -t ed25519`
2. Add to **Repository Secrets**: `SERVER_PRIVATEKEY`
3. Set **Variables**:
   ```json
   APP_HOSTS: ["11.111.222.222"]
   APP_ENVS: [
     "APP_ENV=prod",
     "DATABASE_URL=postgresql://user:pass@host:5432/db?serverVersion=14",
     "LOCK_DSN=semaphore"
   ]
   ```
4. Create `production` branch → Run **Deployer** action

__GitHub Actions Staging__

Deploy to single server with auto PostgreSQL container.

1. Generate SSH key
2. Add **Secret**: `SERVER_PRIVATEKEY`
3. Set **Variables**:
   ```json
   STAG_HOSTS: "11.111.222.222"
   STAG_ENVS: [
     "POSTGRES_PASSWORD=pass123",
     "APP_ENV=prod",
     "APP_SECRET=secret",
     "APP_JWT_SECRET=jwtsecret",
     "DATABASE_URL=postgresql://postgres:pass123@postgres:5432/postgres?serverVersion=14",
     "FIREBASE_DSN=firebase://KEY@default"
   ]
   ```
4. Create `staging` branch → Run **Staging** action

Testing & QA
--------------------
```shell
# Setup
cp phpunit.xml.dist phpunit.xml

# Commands
composer qa:phpstan    # Static analysis
composer qa:lint       # Check code style
composer qa:fix        # Fix code style
composer test          # Run tests
composer test:stop     # Stop on first failure
composer fix           # Run all QA + tests
```

Usage
--------------------
### Bundles

* [SwooleBundle](https://github.com/cesurapp/swoole-bundle) - HTTP server, task queue, cron
* [ApiBundle](https://github.com/cesurapp/api-bundle) - Auto docs, TS client generator
* [StorageBundle](https://github.com/cesurapp/storage-bundle) - Multi-driver file storage
* [MediaBundle](https://github.com/cesurapp/media-bundle) - Image/video processing

### Authentication

**Login**: `POST /api/login` → Returns `access_token` + `refresh_token`
**Refresh**: `POST /api/token/refresh` with `refresh_token`
**Register**: `POST /api/register` → Sends OTP
**Verify OTP**: `POST /api/verify-otp` → Activates account

### Push Notifications (Firebase)

**Setup**:
1. [Firebase Console](https://console.firebase.google.com/) → Create project
2. Project Settings → Cloud Messaging → Enable Legacy API
3. Set `.env`: `FIREBASE_DSN=firebase://SERVER_KEY@default`

**Send**:
```php
$pusher->send((new Notification())
    ->setTitle('Title')
    ->setMessage('Message')
    ->setOwner($user));
```

### Email & SMS

**Email**:
```php
$mailPusher->send((new Email())
    ->to('user@mail.com')
    ->subject('Subject')
    ->html('Body'));
```

**SMS**:
```php
$smsPusher->send(phoneNumber: 5111111111, countryCode: 90, message: 'OTP: 1234');
```

### OTP Verification

OTP codes expire in 15 minutes. Email/SMS sent via queue (async).

```php
// Generate OTP
$otpKey = (new OtpKey())
    ->setType(OtpType::EMAIL)
    ->setAddress('user@mail.com')
    ->setOtpKey(rand(100000, 999999))
    ->setExpiredAt(new \DateTimeImmutable('+15 minutes'));

// Verify
$valid = !$otpKey->isExpired() && !$otpKey->isUsed();
```

### Permissions

Enum-based permissions via `PermissionManager`:
```php
// Check permission
if ($permissionManager->has(CorePermission::USER_EDIT)) {
    // Allow action
}

// User types: SUPER_ADMIN, ADMIN, USER
```
