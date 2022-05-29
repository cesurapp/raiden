<?php

namespace Package\MediaBundle\EventListener;

use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Package\MediaBundle\Entity\MediaEntity;

class MediaListener implements EventSubscriberInterface
{
    public function getSubscribedEvents(): array
    {
        return [Events::onFlush];
    }

    public function onFlush(OnFlushEventArgs $event): void
    {
        $em = $event->getEntityManager();
        $uow = $em->getUnitOfWork();

        // Insert

        // Update
        foreach ($uow->getScheduledEntityUpdates() as $entity) {

            if ($entity instanceof MediaEntity) {
                $columns = $entity->getMediaColumns();
                $changeSet = $uow->getEntityChangeSet($entity);

                dump($columns);
                dump($changeSet);
                foreach ($columns as $column) {
                    if (isset($changeSet[$column])) {
                        /** @var ArrayCollection $old */
                        [$old, $new] = $changeSet[$column]; // @phpstan-ignore-line
                        $diff = array_diff($old->toArray(), $new->toArray());
                        foreach ($diff as $item) {
                            // Remove
                            if ($old->contains($item)) {
                                $em->remove($item);
                            } else {
                                // Insert
                            }
                        }
                    }
                }
            }
        }
    }
}
