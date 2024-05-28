<?php

namespace App\Admin\Notification\Entity;

use App\Admin\Core\Entity\OwnerRemovalTrait;
use App\Admin\Notification\Enum\SchedulerStatus;
use App\Admin\Notification\Repository\SchedulerRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\IdGenerator\UlidGenerator;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity(repositoryClass: SchedulerRepository::class)]
class Scheduler
{
    use OwnerRemovalTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: UlidType::NAME, unique: true)]
    #[ORM\CustomIdGenerator(class: UlidGenerator::class)]
    private ?Ulid $id = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $campaignTitle;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $persistNotification = false;

    #[ORM\Column(type: Types::BIGINT)]
    private int $deliveredCount = 0;

    #[ORM\Column(type: Types::BIGINT)]
    private int $failedCount = 0;

    #[ORM\Column(type: Types::STRING, enumType: SchedulerStatus::class)]
    private SchedulerStatus $status = SchedulerStatus::INIT;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: false)]
    private ?\DateTimeImmutable $sendAt;

    #[ORM\Column(type: Types::JSON)]
    private string $notification;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $deviceFilter = null;

    public function getId(): ?Ulid
    {
        return $this->id;
    }

    public function getCampaignTitle(): ?string
    {
        return $this->campaignTitle;
    }

    public function setCampaignTitle(?string $title): self
    {
        $this->campaignTitle = $title;

        return $this;
    }

    public function isPersistNotification(): bool
    {
        return $this->persistNotification;
    }

    public function setPersistNotification(bool $persistent): self
    {
        $this->persistNotification = $persistent;

        return $this;
    }

    public function getDeliveredCount(): int
    {
        return $this->deliveredCount;
    }

    public function setDeliveredCount(int $count): self
    {
        $this->deliveredCount = $count;

        return $this;
    }

    public function incDeliveredCount(?int $count = null): self
    {
        if (null !== $count) {
            $this->deliveredCount += $count;
        } else {
            ++$this->deliveredCount;
        }

        return $this;
    }

    public function getFailedCount(): int
    {
        return $this->failedCount;
    }

    public function setFailedCount(int $count): self
    {
        $this->failedCount = $count;

        return $this;
    }

    public function incFailedCount(?int $count = null): self
    {
        if (null !== $count) {
            $this->failedCount += $count;
        } else {
            ++$this->failedCount;
        }

        return $this;
    }

    public function getStatus(): SchedulerStatus
    {
        return $this->status;
    }

    public function setStatus(SchedulerStatus $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getSendAt(): ?\DateTimeImmutable
    {
        return $this->sendAt;
    }

    public function setSendAt(?\DateTimeImmutable $sendAt): self
    {
        $this->sendAt = $sendAt;

        return $this;
    }

    public function getNotification(): Notification
    {
        return unserialize($this->notification);
    }

    public function setNotification(Notification $notification): self
    {
        $this->notification = serialize($notification);

        return $this;
    }

    public function getDeviceFilter(): ?array
    {
        return $this->deviceFilter;
    }

    public function setDeviceFilter(?array $deviceFilter): self
    {
        $this->deviceFilter = $deviceFilter;

        return $this;
    }

    public function getDeviceQuery(QueryBuilder $builder): QueryBuilder
    {
        $rootAlias = $builder->getRootAliases()[0];
        $builder->join("$rootAlias.owner", 'u');

        if ($this->getDeviceFilter()) {
            foreach ($this->getDeviceFilter() as $key => $value) {
                [$entity, $column] = explode('.', $key);
                $entity = match ($entity) {
                    'user' => 'u',
                    default => $rootAlias,
                };

                if (is_array($value)) {
                    if (array_is_list($value)) {
                        $builder->andWhere("$entity.$column IN(:$entity$column)")->setParameter($entity.$column, $value);
                    } else {
                        foreach ($value as $subkey => $subVal) {
                            $op = match ($subkey) {
                                'min', 'from' => '>=',
                                default => '<=',
                            };

                            $builder
                                ->andWhere("$entity.$column $op :$entity$column$subkey")
                                ->setParameter($entity.$column.$subkey, $subVal);
                        }
                    }
                } else {
                    $builder->andWhere("$entity.$column = :$entity$column")->setParameter($entity.$column, $value);
                }
            }
        }

        return $builder;
    }
}
