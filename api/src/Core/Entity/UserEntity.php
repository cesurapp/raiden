<?php

namespace App\Core\Entity;

use App\Core\Repository\UserEntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Package\MediaBundle\Entity\MediaEntity;
use Package\MediaBundle\Entity\MediaTrait;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\NotNull;

#[ORM\Entity(repositoryClass: UserEntityRepository::class)]
#[ORM\HasLifecycleCallbacks]
class UserEntity implements MediaEntity
{
    use MediaTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id;

    #[NotNull]
    #[Assert\Length(min: 3, max: 10)]
    #[ORM\Column(type: 'string')]
    private ?string $name;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getMediaColumns(): array
    {
        return ['media'];
    }
}
