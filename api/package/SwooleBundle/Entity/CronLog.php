<?php

namespace Package\SwooleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Package\SwooleBundle\Repository\CronLogRepository;
use Symfony\Bridge\Doctrine\IdGenerator\UlidGenerator;

#[ORM\Entity(repositoryClass: CronLogRepository::class)]
class CronLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'ulid', unique: true)]
    #[ORM\CustomIdGenerator(class: UlidGenerator::class)]
    private ?string $id;

    private string $cron;

    private string $exception;

    private int $retryCount;

    private \DateTime $lastRetryAt;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getCron(): string
    {
        return $this->cron;
    }

    public function setCron(string $cron): void
    {
        $this->cron = $cron;
    }

    public function getException(): string
    {
        return $this->exception;
    }

    public function setException(string $exception): void
    {
        $this->exception = $exception;
    }

    public function getRetryCount(): int
    {
        return $this->retryCount;
    }

    public function setRetryCount(int $retryCount): void
    {
        $this->retryCount = $retryCount;
    }

    public function getLastRetryAt(): \DateTime
    {
        return $this->lastRetryAt;
    }

    public function setLastRetryAt(\DateTime $lastRetryAt): void
    {
        $this->lastRetryAt = $lastRetryAt;
    }
}
