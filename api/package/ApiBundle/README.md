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
```php
use \Package\ApiBundle\Response\ApiResourceInterface;
use \Package\ApiBundle\Thor\Attribute\ThorResource;

class UserResource implements ApiResourceInterface {
    #[ThorResource(data: [[
        'id' => 'string',
        'name' => 'string',
    ]])]
    public function toArray(object $item): array {
        return [
            'id' => $object->getId(),
            'name' => $object->getName()
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

### Generate Documentation
__View Documentation:__ http:://127.0.0.1:8000/thor
```shell
bin/console thor:generate # Generate Documentation to JSON File
```