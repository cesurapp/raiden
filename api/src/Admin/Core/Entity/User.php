<?php

namespace App\Admin\Core\Entity;

use App\Admin\Core\Enum\OtpType;
use App\Admin\Core\Enum\UserType;
use App\Admin\Core\Permission\PermissionInterface;
use App\Admin\Core\Repository\UserRepository;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UlidGenerator;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
#[ORM\HasLifecycleCallbacks]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use TimeStampTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: UlidType::NAME, unique: true)]
    #[ORM\CustomIdGenerator(class: UlidGenerator::class)]
    private ?Ulid $id = null;

    #[ORM\Column(type: 'string', length: 180, unique: true, nullable: true)]
    private ?string $email;

    #[ORM\Column(type: 'boolean')]
    private bool $emailApproved = false;

    #[ORM\Column(type: 'bigint', length: 20, unique: true, nullable: true)]
    private ?int $phone = null;

    #[ORM\Column(type: 'string', length: 2, nullable: true)]
    private ?string $phoneCountry;

    #[ORM\Column(type: 'boolean')]
    private bool $phoneApproved = false;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column(type: 'string', enumType: UserType::class)]
    private UserType $type = UserType::USER;

    #[ORM\Column(type: 'string')]
    private string $password;

    #[ORM\Column(type: 'boolean')]
    private bool $frozen = false;

    #[ORM\Column(type: 'string', length: 3, nullable: true)]
    private ?string $language = null;

    #[ORM\Column(type: 'string', length: 50)]
    private string $firstName;

    #[ORM\Column(type: 'string', length: 50)]
    private string $lastName;

    #[ORM\Column(type: 'json')]
    private array $meta = [];

    #[ORM\ManyToOne(targetEntity: Organization::class, cascade: ['persist'], inversedBy: 'users')]
    private ?Organization $organization = null;

    public function getId(): ?Ulid
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function isEmailApproved(): bool
    {
        return $this->emailApproved;
    }

    public function setEmailApproved(bool $emailApproved): self
    {
        $this->emailApproved = $emailApproved;

        return $this;
    }

    public function getPhone(): ?int
    {
        return $this->phone;
    }

    public function setPhone(null|int|string $phone): self
    {
        $this->phone = $phone ? (int) $phone : null;

        return $this;
    }

    public function getPhoneCountry(): ?string
    {
        return $this->phoneCountry;
    }

    public function setPhoneCountry(?string $phoneCountry): self
    {
        $this->phoneCountry = $phoneCountry;

        return $this;
    }

    public function isPhoneApproved(): bool
    {
        return $this->phoneApproved;
    }

    public function setPhoneApproved(bool $phoneApproved): self
    {
        $this->phoneApproved = $phoneApproved;

        return $this;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function hasRoles(PermissionInterface|UserType $permission): bool
    {
        return in_array($permission->value, $this->roles, true);
    }

    public function addRoles(PermissionInterface $role): self
    {
        $this->roles[] = $role->value;

        return $this;
    }

    /**
     * @param PermissionInterface[] $roles
     */
    public function setRoles(array $roles): self
    {
        $this->roles = array_map(static fn (PermissionInterface $permission) => $permission->value, $roles);

        return $this;
    }

    public function getType(): UserType
    {
        return $this->type;
    }

    public function setType(UserType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function hasType(UserType $type): bool
    {
        return $type === $this->type;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password, UserPasswordHasherInterface $hasher = null): self
    {
        $this->password = $hasher ? $hasher->hashPassword($this, $password) : $password;

        return $this;
    }

    public function isApproved(): bool
    {
        return $this->emailApproved || $this->phoneApproved;
    }

    public function isFrozen(): bool
    {
        return $this->frozen;
    }

    public function setFrozen(bool $frozen): self
    {
        $this->frozen = $frozen;

        return $this;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function setLanguage(?string $language): self
    {
        $this->language = $language;

        return $this;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getFullName(): string
    {
        return sprintf('%s %s', $this->getFirstName(), $this->getLastName());
    }

    public function getMeta(): array
    {
        return $this->meta;
    }

    public function addMeta(string $key, string|int|bool $value): self
    {
        $this->meta[$key] = $value;

        return $this;
    }

    public function removeMeta(string $key): self
    {
        if (array_key_exists($key, $this->meta)) {
            unset($this->meta[$key]);
        }

        return $this;
    }

    public function setMeta(array $meta): self
    {
        $this->meta = $meta;

        return $this;
    }

    public function getOrganization(): ?Organization
    {
        return $this->organization;
    }

    public function setOrganization(?Organization $organization): self
    {
        $this->organization = $organization;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function eraseCredentials(): void
    {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'email' => $this->email,
            'emailApproved' => $this->emailApproved,
            'phone' => $this->phone,
            'phoneApproved' => $this->phoneApproved,
            'phoneCountry' => $this->phoneCountry,
            'roles' => $this->roles,
            'type' => $this->type,
            'frozen' => $this->frozen,
            'language' => $this->language,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
        ];
    }

    #[ORM\PostPersist]
    public function postPersist(LifecycleEventArgs $event): void
    {
        $otpRepo = $event->getObjectManager()->getRepository(OtpKey::class);

        if (!$this->isEmailApproved() && $this->getEmail()) {
            $otpRepo->create($this, OtpType::EMAIL);
        }

        if (!$this->isPhoneApproved() && $this->getPhone()) {
            $otpRepo->create($this, OtpType::PHONE);
        }
    }

    #[ORM\PreFlush]
    public function preFlush(PreFlushEventArgs $event): void
    {
        $roles = array_diff($this->getRoles(), UserType::values());
        $roles[] = $this->getType()->value;

        $this->roles = array_unique($roles);
    }
}
