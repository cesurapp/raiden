## Api Bundle

### Create Api Response
```php
use \Package\ApiBundle\AbstractClass\AbstractApiController;
use \Package\ApiBundle\Response\ApiResponse;

class TestController extends AbstractApiController {
    public function index(): ApiResponse {
        return ApiResponse::create()
            ->setData(['custom-data'])
            ->setQuery('QueryBuilder')
            ->setHTTPCache(60)  // Enable HTTP Cache
            ->setPaginate()     // Enable QueryBuilder Paginator
            ->setHeaders([])    // Custom Header
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
     * Enable Auto Validation 
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