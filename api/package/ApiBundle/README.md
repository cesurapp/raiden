## Api Bundle

### Create Api Response
```php
use \Package\ApiBundle\AbstractClass\AbstractApiController;
use \Package\ApiBundle\Response\ApiResponse;
use \Package\ApiBundle\Thor\Attribute\Thor;
use \Symfony\Component\Routing\Annotation\Route;

class TestController extends AbstractApiController {
    #[Thor(
        group: 'Login',
        desc: 'Login EndPoint',
        dto: LoginDto::class, 
        requireAuth: false, 
        paginate: true, 
        response: UserResource::class,
        query: [
            'name' => '?string',
            'filter' => [
                'id' => '?int',
                'name' => '?string',
                'fullName' => '?string',
            ],
        ]
    )]
    #[Route(name: 'Login', path: '/login', methods: ['POST'])]
    public function index(LoginDto $loginDto): ApiResponse {
        return ApiResponse::create()
            ->setData(['custom-data'])
            ->setQuery('QueryBuilder')
            ->setHTTPCache(60)  // Enable HTTP Cache
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
    public function toArray(object $item): array {
        return [
            'id' => $object->getId(),
            'name' => $object->getName()
        ]
    }
    
    public function toResource(): array {
        return [
              'id' => [
                'type' => 'string', // Typescript Type
                'filter' => static function (QueryBuilder $builder, string $alias, mixed $data) {}, // app.test?filter[id]=test
                'filter' => [
                    'min' => static function (QueryBuilder $builder, string $alias, mixed $data) {}, // app.test?filter[id][min]=test
                    'max' => static function (QueryBuilder $builder, string $alias, mixed $data) {}, // app.test?filter[id][max]=test
                ]
                'table' => [ // Typescript DataTable Types
                    'label' => 'ID',
                    'sortable' => true,
                    // 'exporter' => static fn($v) => $v, // Export Column Template
                    // 'sortable_field' => 'firstName', // Doctrine Getter Method
                    // 'sortable_field' => static fn (QueryBuilder $builder, string $direction) => $builder->orderBy('u.firstName', $direction),
                ],
            ]
        ]   
    }

}
```

### Create Form Validation
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

### Create Doctrine Filter
Filters are set according to the query parameter. Only matching records are filtered.

Sample request `http://example.test/v1/userlist?filter[id]=1&filter[createdAt][min]=10.10.2023`

```php
class UserRepository extends ApiServiceEntityRepository
{
    // Default Filter
    public static function filterDefault(): array
    {
        return [
            'id' => static function (QueryBuilder $builder, string $alias, string $data) {
                // Logic
            },
            'createdAt' => static function (QueryBuilder $builder, string $alias, array $data) {
                if (isset($data['min'])) {
                    // Logic
                }
                if (isset($data['max'])) {
                    // Logic
                }
            },
            'createdAt' => [
                'min' => static function (QueryBuilder $builder, string $alias, string $data) {
                    // Logic
                },
                'max' => static function (QueryBuilder $builder, string $alias, string $data) {
                    // Logic
                },
            ]
        ];
    }
    
    // Custom Filter
    public static function filterCustom(): array
    {
        return [];
    }
}
```
Call Filter:
```php
class AccountController extends AbstractApiController
{
    #[Thor(
        filter: \App\Admin\Core\Repository\UserRepository::class,
        filterId: 'custom' // default value is "default" -> optional
    )]
    public function showProfile(\Symfony\Component\HttpFoundation\Request $request, \App\Admin\Core\Repository\UserRepository $repository): ApiResponse
    {
        $filteredQuery = $repository->createFilteredQueryBuilder($request, 'default');
        
        // Call Custom Filter
        // $filteredQuery = $repository->createFilteredQueryBuilder($request, 'custom');
    }
}
```

### Generate Documentation
__View Documentation:__ http:://127.0.0.1:8000/thor
```shell
bin/console thor:generate # Generate Documentation to JSON File
```