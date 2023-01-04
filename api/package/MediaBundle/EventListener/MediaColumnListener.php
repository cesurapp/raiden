<?php

namespace Package\MediaBundle\EventListener;

use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Package\MediaBundle\Entity\Media;
use Package\MediaBundle\Entity\MediaInterface;

class MediaColumnListener implements EventSubscriberInterface
{
    public function getSubscribedEvents(): array
    {
        return [Events::onFlush];
    }

    public function onFlush(OnFlushEventArgs $event): void
    {
        $em = $event->getObjectManager();
        $uow = $em->getUnitOfWork();

        // Remove
        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            if (!$entity instanceof MediaInterface) {
                continue;
            }

            foreach ($entity->getMediaColumns() as $column) {
                if ($media = $entity->{'get'.ucfirst($column)}()) {
                    foreach ($media as $item) {
                        $this->updateMedia($item, $em);
                    }
                }
            }
        }

        // Update
        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if (!$entity instanceof MediaInterface) {
                continue;
            }

            $changeSet = $uow->getEntityChangeSet($entity);
            foreach ($entity->getMediaColumns() as $column) {
                if (isset($changeSet[$column])) {
                    $diff = array_diff_key($changeSet[$column][0], $changeSet[$column][1]);

                    /** @var Media $item */
                    foreach ($diff as $id => $item) {
                        // Remove & Decrement Counter
                        if (isset($changeSet[$column][0][$id])) {
                            $this->updateMedia($item, $em);
                        }
                    }
                }
            }
        }
    }

    private function updateMedia(Media $media, EntityManagerInterface $em): void
    {
        $media->decrCounter();

        if (0 === $media->getCounter()) {
            $em->remove($media);
        } else {
            $em->persist($media);
        }
    }
}
