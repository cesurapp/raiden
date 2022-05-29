<?php

namespace Package\MediaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

trait MediaTrait
{
    #[ORM\Column(type: 'media', nullable: true)]
    private ?array $media = [];

    /**
     * @return array|Media[]
     */
    public function getMedia(): ?array
    {
        return $this->media;
    }

    public function addMedia(Media $media): self
    {
        if (!in_array($media, $this->media, true)) {
            $this->media[] = $media;
        }

        return $this;
    }

    public function removeMedia(Media $media): self
    {
        if ($key = array_search($media, $this->media, true)) {
            unset($this->media[$key]);
        }

        return $this;
    }

    public function setMedia(array $media): self
    {
        $this->media = [];

        foreach ($media as $item) {
            $this->media[] = $item;
        }

        return $this;
    }

    protected function getMediaColumns(): array
    {
        return ['media'];
    }
}
