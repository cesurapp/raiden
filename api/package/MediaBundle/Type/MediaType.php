<?php

namespace Package\MediaBundle\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;
use Package\MediaBundle\Entity\Media;
use Symfony\Component\Uid\Ulid;

/**
 * Doctrine Media Type Stored in JSON.
 */
class MediaType extends Type
{
    private EntityManagerInterface $entityManager;

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getJsonTypeDeclarationSQL($column);
    }

    public function getName(): string
    {
        return 'media';
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return !$platform->hasNativeJsonType();
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if (null === $value) {
            return null;
        }

        try {
            $array = [];
            /** @var Media $item */
            foreach ($value as $item) {
                $array[] = $item->getId();
            }

            return json_encode($array, JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION);
        } catch (\JsonException $e) {
            throw ConversionException::conversionFailedSerialization($value, 'json', $e->getMessage(), $e);
        }
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?array
    {
        $array = [];

        if (null === $value || '' === $value) {
            return $array;
        }

        if (is_resource($value)) {
            $value = stream_get_contents($value);
        }

        try {
            $ids = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
            foreach ($ids as $id) {
                $array[$id] = $this->entityManager->getReference(Media::class, Ulid::fromString($id));
            }

            return $array;
        } catch (\JsonException $e) {
            throw ConversionException::conversionFailed($value, $this->getName(), $e);
        }
    }

    public function setEntityManager(EntityManagerInterface $em): void
    {
        $this->entityManager = $em;
    }
}
