<?php

namespace Package\SwooleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Package\SwooleBundle\Repository\FailedTaskRepository;
use Symfony\Bridge\Doctrine\IdGenerator\UlidGenerator;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity(repositoryClass: FailedTaskRepository::class)]
class FailedTask
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'ulid', unique: true)]
    #[ORM\CustomIdGenerator(class: UlidGenerator::class)]
    private ?Ulid $id;

    #[ORM\Column(type: 'string')]
    private string $task;

    #[ORM\Column(type: 'json', nullable: true)]
    private array $payload;

    #[ORM\Column(type: 'text')]
    private string $exception;

    #[ORM\Column(type: 'smallint')]
    protected int $attempt = 0;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?Ulid
    {
        return $this->id;
    }

    public function getTask(): string
    {
        return $this->task;
    }

    public function setTask(string $task): self
    {
        $this->task = $task;

        return $this;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function setPayload(array $payload): self
    {
        $this->payload = $payload;

        return $this;
    }

    public function getException(): string
    {
        return $this->exception;
    }

    public function setException(string $exception): self
    {
        $this->exception = $exception;

        return $this;
    }

    public function getAttempt(): int
    {
        return $this->attempt;
    }

    public function setAttempt(int $attempt): self
    {
        $this->attempt = $attempt;

        return $this;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
