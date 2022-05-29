<?php

namespace Package\MediaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

trait MediaTrait
{
    #[ORM\Column(type: 'media', nullable: true)]
    private ?ArrayCollection $media;

    /**
     * @return ArrayCollection|Media[]
     */
    public function getMedia(): ?ArrayCollection
    {
        return $this->media;
    }

    public function addMedia(Media|array $media): self
    {
        if (!is_array($media)) {
            $media = [$media];
        }

        foreach ($media as $item) {
            $this->media->add($item);
        }

        return $this;
    }

    public function removeMedia(Media|array $media): self
    {
        if (!is_array($media)) {
            $media = [$media];
        }

        foreach ($media as $item) {
            if ($this->media->contains($item)) {
                $this->media->removeElement($item);
            }
        }

        return $this;
    }

    public function setMedia(ArrayCollection|array|null $media): self
    {
        if (is_array($media)) {
            $this->media = new ArrayCollection($media);
        } else {
            $this->media = $media;
        }

        return $this;
    }

    protected function getMediaColumns(): array
    {
        return ['media'];
    }
}
