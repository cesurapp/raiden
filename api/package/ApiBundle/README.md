## Api Bundle

### Generate Documentation
__View Documentation:__ http:://127.0.0.1:8000/thor
```shell
bin/console thor:generate # Generate Documentation to JSON File
```

### Create Api Response
```php
use \Package\ApiBundle\AbstractClass\AbstractApiController;
use \Package\ApiBundle\Response\ApiResponse;
use \Package\ApiBundle\Thor\Attribute\Thor;
use \Symfony\Component\Routing\Annotation\Route;

class TestController extends AbstractApiController {
    #[Thor(
        group: 'Login|1',
        groupDesc: 'Global',
        desc: 'Login EndPoint',
        request: [
            'username' => 'string',
            'password' => 'string',
        ],
        response: [
            200 => ['data' => UserResource::class],
            BadCredentialsException::class,
            TokenExpiredException::class,
            AccessDeniedException::class
        ],
        dto: LoginDto::class, 
        requireAuth: false, 
        paginate: false, 
        order: 0
    )]
    #[Route(name: 'Login', path: '/login', methods: ['POST'])]
    public function getMethod(LoginDto $loginDto): ApiResponse {
        return ApiResponse::create()
            ->setData(['custom-data'])
            ->setQuery('QueryBuilder')
            ->setHTTPCache(60)  // Enable HTTP Cache
            ->setPaginate()     // Enable QueryBuilder Paginator
            ->setHeaders([])    // Custom Header
            ->setResource(UserResource::class)
    }
    
    #[Thor(
        group: 'Profile|2',
        groupDesc: 'Global',
        desc: 'Profile EndPoint',
        query: [
            'name' => '?string',
            'filter' => [
                'id' => '?int',
                'name' => '?string',
                'fullName' => '?string',
            ],
        ],
        response: [200 => ['data' => UserResource::class]],
        requireAuth: true, 
        paginate: false, 
        order: 0
    )]
    #[Route(name: 'GetExample', path: '/get', methods: ['GET'])]
    public function postMethod(): ApiResponse {
        $query = $userRepo->createQueryBuilder('q');
        
        return ApiResponse::create()
            ->setQuery($query)
            ->setPaginate()     // Enable QueryBuilder Paginator
            ->setHeaders([])    // Custom Header
            ->setResource(UserResource::class)
    }
}
```

### Create Api Resource
Filter and DataTable only work when pagination is enabled. Automatic TS columns are created for the table.
Export is automatically enabled for all tables.

```php
use \Package\ApiBundle\Response\ApiResourceInterface;

class UserResource implements ApiResourceInterface {
    public function toArray(object $item, mixed $optional = null): array {
        return [
            'id' => $object->getId(),
            'name' => $object->getName()
        ]
    }
    
    public function toResource(): array {
        return [
              'id' => [
                'type' => 'string', // Typescript Type -> ?string|?int|?boolean|?array|?object|NotificationResource::class|
                'filter' => static function (QueryBuilder $builder, string $alias, mixed $data) {}, // app.test?filter[id]=test
                'table' => [ // Typescript DataTable Types
                    'label' => 'ID',                     // DataTable Label
                    'sortable' => true,                  // DataTable Sortable Column   
                    'sortable_default' => true,          // DataTable Default Sortable Column
                    'sortable_desc' => true,             // DataTable Sortable DESC
                    'filter_input' => 'input',           // DataTable Add Filter Input Type -> input|number|date|daterange|checkbox|country|language
                   
                    // These fields are used in the backend. It doesn't transfer to the frontend. 
                    'exporter' => static fn($v) => $v,   // Export Column Template
                    'sortable_field' => 'firstName',     // Doctrine Getter Method
                    'sortable_field' => static fn (QueryBuilder $builder, string $direction) => $builder->orderBy('u.firstName', $direction),
                ],
            ],
            'created_at' => [
                'type' => 'string',
                'filter' => [
                    'from' => static function (QueryBuilder $builder, string $alias, mixed $data) {}, // app.test?filter[created_at][min]=test
                    'to' => static function (QueryBuilder $builder, string $alias, mixed $data) {}, // app.test?filter[created_at][max]=test
                ]
            ]
        ]   
    }

}
```

__Using Filter__

Filters are set according to the query parameter. Only matching records are filtered.

Sample request `http://example.test/v1/userlist?filter[id]=1&filter[createdAt][min]=10.10.2023`

### Create Form Validation
Backend dates are stored in UTC ATOM format. In GET requests you get dates in ATOM format. 
In POST|PUT requests, send dates in ATOM format, converted to UTC.

```php
use Package\ApiBundle\AbstractClass\AbstractApiDto;
use Package\ApiBundle\Thor\Attribute\ThorResource;
use Symfony\Component\Validator\Constraints as Assert;

class LoginDto extends AbstractApiDto {
    /**
     * Enable Auto Validation -> Default Enabled
     */
    protected bool $auto = true;
    
    /**
     * Form Fields
     */
    #[Assert\NotNull]
    public string|int|null|bool $name;

    #[Assert\Length(min: 3, max: 100)]
    public ?string $lastName;

    #[Assert\Length(min: 10, max: 100)]
    #[Assert\NotNull]
    public int $phone;

    #[Assert\NotNull]
    #[Assert\GreaterThan(new \DateTimeImmutable())]
    public \DateTimeImmutable $send_at;
    
    #[Assert\Optional([
        new Assert\Type('array'),
        new Assert\Count(['min' => 1]),
        new Assert\All([
            new Assert\Collection([
                'slug' => [
                    new Assert\NotBlank(),
                    new Assert\Type(['type' => 'string']),
                ],
                'label' => [
                    new Assert\NotBlank(),
                ],
            ]),
        ]),
    ])]
    #[ThorResource(data: [[
        'slug' => 'string',
        'label' => 'string|int|boolean',
    ]])]
    public ?array $data;
}
```

### Swoole Coroutine HTTP Client
Replaced Symfony HttpClientInteface with Swoole. It provides high performance.

Example:

```php
public function homeControllerAction(\Symfony\Contracts\HttpClient\HttpClientInterface $client){
    $client->request('POST', 'https://google.com') // Swoole Coroute HTTP Client
}
```

Without Dependency Injection:

GET | DELETE Request:

```php
\Package\ApiBundle\Client\SwooleClient::create('https://www.google.com')
    ->setHeaders(['Host' => 'www.app.test'])
    //->get(['key' => 'value'])
    //->delete(['key' => 'value'])
```

POST | PUT | PATCH Request:

```php
\Package\ApiBundle\Client\SwooleClient::create('https://www.google.com')
    ->setHeaders(['Host' => 'www.app.test'])
    //->post(['key' => 'value'])
    //->put(['key' => 'value'])
    //->patch(['key' => 'value'])
```

Custom Request:

```php
\Package\ApiBundle\Client\SwooleClient::create('https://www.google.com')
    ->setHeaders(['Host' => 'www.app.test'])
    ->setMethod('POST')
    ->setData(['key' => 'value'])
    ->setQuery(['key' => 'value'])
    ->execute();   
```