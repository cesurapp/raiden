<?php

namespace App\Admin\Core\Entity;

use App\Admin\Core\Enum\UserType;
use App\Admin\Core\Permission\PermissionInterface;
use App\Admin\Core\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UlidGenerator;
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
    #[ORM\Column(type: 'ulid', unique: true)]
    #[ORM\CustomIdGenerator(class: UlidGenerator::class)]
    private ?Ulid $id;

    #[ORM\Column(type: 'string', length: 180, unique: true, nullable: true)]
    private ?string $email;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column(type: 'string', enumType: UserType::class)]
    private UserType $type = UserType::USER;

    #[ORM\Column(type: 'string')]
    private string $password;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $passwordRequestedAt = null;

    #[ORM\Column(type: 'string', unique: true, nullable: true)]
    private ?string $confirmationToken;

    #[ORM\Column(type: 'string', unique: true, nullable: true)]
    private ?string $resetToken;

    #[ORM\Column(type: 'boolean')]
    private bool $frozen = false;

    #[ORM\Column(type: 'boolean')]
    private bool $approved = false;

    #[ORM\Column(type: 'string', length: 3, nullable: true)]
    private ?string $language;

    #[ORM\Column(type: 'string', length: 50)]
    private string $firstName;

    #[ORM\Column(type: 'string', length: 50)]
    private string $lastName;

    #[ORM\Column(type: 'string', length: 20, unique: true, nullable: true)]
    private ?string $phone;

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

    public function getRoles(): array
    {
        $roles = $this->roles;

        return array_unique($roles);
    }

    public function hasRoles(PermissionInterface $permission): bool
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

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password, ?UserPasswordHasherInterface $hasher = null): self
    {
        $this->password = $hasher ? $hasher->hashPassword($this, $password) : $password;

        return $this;
    }

    public function getPasswordRequestedAt(): ?DateTimeImmutable
    {
        return $this->passwordRequestedAt;
    }

    public function setPasswordRequestedAt(?DateTimeImmutable $passwordRequestedAt): self
    {
        $this->passwordRequestedAt = $passwordRequestedAt;

        return $this;
    }

    public function isPasswordRequestExpired(int $ttl = 120): bool
    {
        return $this->getPasswordRequestedAt() instanceof \DateTimeImmutable && $this->getPasswordRequestedAt()->getTimestamp() + ($ttl * 60) > time();
    }

    public function isApproved(): bool
    {
        return $this->approved;
    }

    public function setApproved(bool $approved): self
    {
        $this->approved = $approved;

        return $this;
    }

    public function getConfirmationToken(): ?string
    {
        return $this->confirmationToken;
    }

    public function setConfirmationToken(?string $confirmationToken): self
    {
        $this->confirmationToken = $confirmationToken;

        return $this;
    }

    public function createConfirmationToken(): self
    {
        $this->confirmationToken = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');

        return $this;
    }

    public function getResetToken(): ?string
    {
        return $this->resetToken;
    }

    public function setResetToken(?string $resetToken): self
    {
        $this->resetToken = $resetToken;

        return $this;
    }

    public function createResetToken(): self
    {
        $this->resetToken = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');

        return $this;
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

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

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

    #[ORM\PrePersist]
    public function prePersist(LifecycleEventArgs $event): void
    {
        if (!$this->isApproved()) {
            $this->createConfirmationToken();
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
