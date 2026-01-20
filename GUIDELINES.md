# Raiden Admin Panel - Agent Guidelines

Reusable admin panel: **Symfony 8 + Swoole** (backend), **Quasar 2.7 + Vue 3** (frontend). Auto-generated TypeScript from PHP, role-based permissions, real-time notifications.

**Stack:**
- Backend: PHP 8.4 + Symfony 8 + Swoole
- Frontend: Vue 3 + Quasar 2.7 + TypeScript
- Database: PostgreSQL 14+
- Storage: Local/Cloudflare R2/BackBlaze B2

**⚠️ IMPORTANT: Read these bundle guidelines for detailed documentation:**
- **API Bundle (Resources, DTOs, Thor):** `api/vendor/cesurapp/api-bundle/GUIDELINES.md`
- **Swoole Bundle (Tasks, Crons, Server):** `api/vendor/cesurapp/swoole-bundle/GUIDELINES.md`
- **Storage Bundle (File management):** `api/vendor/cesurapp/storage-bundle/GUIDELINES.md`
- **Media Bundle (Image processing):** `api/vendor/cesurapp/media-bundle/GUIDELINES.md`

---

## 1. Backend Structure & Organization

### Directory Structure

```
api/src/Admin/{Module}/
├── Controller/      # REST endpoints
├── Dto/            # Request validation
├── Entity/         # Doctrine models
├── Repository/     # Query methods
├── Resource/       # API serializers (filtering/sorting)
├── Permission/     # Permission enums
├── Task/           # Async queue jobs
├── Cron/           # Scheduled jobs
├── Service/        # Business logic
└── EventListener/  # Event handlers
```

**Naming:**
- Controller: `{Feature}Controller.php`
- Entity: `{Model}.php`
- DTO: `{Action}Dto.php`
- Resource: `{Model}Resource.php`
- Permission: `{Feature}Permission.php`
- Task: `{Action}Task.php`
- Cron: `{Action}Cron.php`

---

## 2. Creating Features (Backend)

### 2.1 Entity

```php
#[ORM\Entity(repositoryClass: {Model}Repository::class)]
class {Model}
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private ?UuidV7 $id = null;

    public function __construct() { $this->id = new UuidV7(); }

    // Getters & setters...
}
```

**Traits:** `TimeStampTrait`, `CreatedAtTrait`, `OrganizationTrait`, `OwnerRemovalTrait`

### 2.2 Repository

```php
class {Model}Repository extends ApiServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, {Model}::class);
    }
}
```

### 2.3 DTO (Validation)

```php
class Create{Model}Dto extends ApiDto
{
    #[Assert\NotNull]
    #[Assert\Length(min: 3, max: 255)]
    public string $name;

    public function initObject(mixed $object = null): {Model}
    {
        return $object->setName($this->validated('name'));
    }
}
```

**Methods:** `validated()`, `initObject()`, `beforeValidated()`, `endValidated()`

### 2.4 Resource (Serializer + Filters)

```php
class {Model}Resource implements ApiResourceInterface
{
    public function toArray(mixed $item, mixed $optional = null): array
    {
        return [
            'id' => $item->getId()->toString(),
            'name' => $item->getName(),
            'created_at' => $item->getCreatedAt()->format(DATE_ATOM),
        ];
    }

    public function toResource(): array
    {
        return [
            'name' => [
                'type' => 'string',
                'filter' => static fn(QueryBuilder $b, string $a, string $d) =>
                    $b->andWhere("$a.name LIKE :name")->setParameter('name', "%$d%"),
                'table' => [
                    'label' => 'Name',
                    'sortable' => true,
                    'filter_input' => 'input',
                ],
            ],
        ];
    }
}
```

**Filter Inputs:** `input`, `number`, `range`, `date`, `daterange`, `checkbox`, `country`, `language`, `currency`

### 2.5 Controller (REST Endpoints)

```php
class {Model}Controller extends ApiController
{
    #[Thor(stack: '{Model}', title: 'List', response: [200 => ['data' => {Model}Resource::class]], isPaginate: true)]
    #[Route('/v1/admin/{model}', methods: ['GET'])]
    #[IsGranted({Model}Permission::LIST->value)]
    public function list(): ApiResponse
    {
        return ApiResponse::create()
            ->setQuery($this->repo->createQueryBuilder('q'))
            ->setPaginate()
            ->setResource({Model}Resource::class);
    }

    #[Thor(stack: '{Model}', title: 'Create', dto: Create{Model}Dto::class)]
    #[Route('/v1/admin/{model}', methods: ['POST'])]
    #[IsGranted({Model}Permission::CREATE->value)]
    public function create(Create{Model}Dto $dto): ApiResponse
    {
        $entity = $dto->initObject(new {Model}());
        $this->repo->add($entity);
        return ApiResponse::create()->setData($entity)->setResource({Model}Resource::class);
    }

    // show(), edit(), delete() methods...
}
```

### 2.6 Permission Enum

```php
enum {Model}Permission: string implements AdminPermissionInterface
{
    case LIST = 'ROLE_{MODEL}_LIST';
    case CREATE = 'ROLE_{MODEL}_CREATE';
    case EDIT = 'ROLE_{MODEL}_EDIT';
    case DELETE = 'ROLE_{MODEL}_DELETE';
}
```

**Register:** Add to `PermissionManager::registerPermissions()` → `$this->addPermission({Model}Permission::class);`

---

## 3. Async Operations (Swoole)

**Task (Queue):** Async operations (emails, image processing)
```php
class Send{Action}Task implements TaskInterface {
    public function __invoke(string $data): mixed {
        $payload = unserialize($data);
        // Process...
    }
}
// Dispatch: $taskHandler->dispatch(Send{Action}Task::class, ['email' => '...']);
```

**Cron (Scheduled):** Periodic tasks
```php
class Cleanup{Action}Cron extends AbstractCronJob {
    public string $TIME = '@daily'; // or '0 2 * * *' or '30' (seconds)
    public function __invoke(): void { /* logic */ }
}
```

**Process (Continuous):** Long-running workers (Redis SUBSCRIBE, Postgres LISTEN)
```php
class Redis{Action}Process extends AbstractProcessJob {
    public bool $ENABLE = true;
    public bool $RESTART = true;
    public function __invoke(): void { /* continuous operation */ }
}
```

**Commands:** `bin/console cron:list`, `bin/console task:failed:view`

---

## 4. TypeScript Generation

```bash
bin/console thor:extract ../admin/.api
```

**Generates:** API client, interfaces, DataTable schemas, enums from `#[Thor]` + DTO + Resource.

**Regenerate:** After API/DTO/Resource changes.

---

## 5. Frontend Structure

```
admin/src/
├── boot/          # App init (api, i18n, interceptors, guards, rules)
├── components/    # SimpleTable, SimpleDialog, SimpleEditor, Date, Language, Localization
├── pages/         # Admin/Layout.vue, Admin/{Feature}, Auth/*
├── router/        # index.ts, admin.ts, auth.ts
├── stores/        # AppStore.ts, AuthStore.ts
├── i18n/          # Translations
└── .api/          # Generated TypeScript
```

**Key Components:** `SimpleTable` (DataTable), `SimpleDialog` (modals), `SimpleEditor` (forms), Date/Language/Localization inputs

---

## 6. Creating Features (Frontend)

### 6.1 Route

```typescript
{
  path: '{feature}',
  component: () => import('pages/Admin/{Feature}/{Feature}Page.vue'),
  meta: {
    requireAuth: true,
    userType: [UserType.ADMIN],
    breadcrumb: '{Feature}',
    permission: [Permission.Admin{Feature}.LIST],
  },
}
```

### 6.2 Page (List + Editor)

```vue
<template>
  <SimpleTable
    :api-method="$api.admin{Feature}.list"
    :columns="columns"
    :create-editor="() => $refs.editor.show()"
    :edit-editor="(row) => $refs.editor.show(row.id)"
    :delete-method="$api.admin{Feature}.delete"
    :can-create="$authStore.hasPermission($permission.Admin{Feature}.CREATE)"
  />
  <{Feature}Editor ref="editor" @saved="$refs.table.refresh()" />
</template>
```

### 6.3 Editor (Create/Edit Dialog)

```vue
<template>
  <SimpleDialog :title="editId ? $t('Edit') : $t('Create')" @saved="save">
    <q-input v-model="form.name" :label="$t('Name')" :rules="[$rules.required]" />
  </SimpleDialog>
</template>

<script>
methods: {
  async show(id?) {
    this.editId = id || null;
    if (id) this.form = (await this.$api.admin{Feature}.show(id)).data;
    this.$refs.dialog.show();
  },
  async save() {
    if (this.editId) await this.$api.admin{Feature}.edit(this.editId, this.form);
    else await this.$api.admin{Feature}.create(this.form);
    this.$emit('saved');
  }
}
</script>
```

---

## 7. Commands

**Backend:**
```bash
bin/console server:watch                    # Dev server
bin/console user:create email pass --type=SUPERADMIN
bin/console thor:extract ../admin/.api      # Generate TS
composer test / qa:phpstan / qa:fix
```

**Frontend:**
```bash
yarn quasar dev / build / build-ios / build-android
```

---

## 8. Conventions

**Backend DO:** Extend `ApiController`, return `ApiResponse`, use `#[Thor]`, implement `ApiResourceInterface`, extend `ApiDto`, use UuidV7, `DATE_ATOM` dates, `setPaginate()`, auto-register via interfaces.

**Backend DON'T:** Validate in controllers, return raw arrays, bypass DTO validation, pass objects in tasks, block Swoole workers, forget TS regeneration.

**Frontend DO:** Use `$api`, `$authStore.hasPermission()`, `$t()`, `SimpleTable`, `SimpleDialog`, `$appStore.isBusy`, `notifySuccess()`/`notifyDanger()`.

**Frontend DON'T:** Bypass guards, hardcode strings, raw Axios, forget `requireAuth` meta.

---

## 9. Storage & Media

**Storage:** `$storage->upload()`, `download()`, `exists()`, `delete()`, `getUrl()`, `device('local')`

**Media:** `$mediaManager->uploadFile()`, `uploadBase64()`, `setImageCompress()`, `setImageConvertJPG()`, `setImageQuality()`, `setImageSize()`

**Entity:** Implement `MediaInterface`, use `MediaTrait`, define `getMediaColumns()`

---

## 10. Firebase Notifications

**Backend:** `.env` → `FIREBASE_DSN=firebase://<ServerKey>@default`

**Send:** `$pusher->send((new Notification())->setTitle()->setMessage()->setOwner())`

**Frontend:** Configure `.env` with Firebase keys, register device via `$api.adminDevice.register()`

---

## 11. Quick Reference

**Test:** Extend `WebTestCase`, use `composer test`, `qa:phpstan`

**Deploy:** Docker: `docker build/run`, GitHub Actions: push to `production` branch

**Key Files:**
- Backend: `config/packages/*.yaml`, `PermissionManager.php`, `UserType.php`
- Frontend: `boot/app.ts`, `axios-interceptor.ts`, `route-guard.ts`, `AuthStore.ts`

**Troubleshoot:**
- Swoole: `php -m | grep swoole`
- DB: `bin/console doctrine:schema:update --force`
- TS: Check `#[Thor]` syntax
- Frontend: Delete `node_modules`, `yarn install`

---

## 12. Feature Checklist

**Backend (7 steps):**
1. Entity + Repository
2. DTO (validation)
3. Resource (serialization)
4. Controller (REST)
5. Permission enum
6. Register permission in `PermissionManager`
7. Generate TypeScript: `bin/console thor:extract ../admin/.api`

**Frontend (2 steps):**
1. Route in `router/admin.ts`
2. Page + Editor components

**Agent Rules:**
- Follow naming conventions
- Use traits/abstracts/interfaces
- Always regenerate TS after backend changes
- Check permissions with `$authStore.hasPermission()`
- Use `SimpleTable`/`SimpleDialog`
- Use `$t()` for i18n
