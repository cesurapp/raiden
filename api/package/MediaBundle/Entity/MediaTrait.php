<?php

namespace Package\MediaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

trait MediaTrait
{
    #[ORM\Column(type: 'media', nullable: true)]
    private ?array $media = [];

    /**
     * @return Media[]|null
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

    public function addMedias(array $medias): self
    {
        array_walk_recursive($medias, fn (Media $media) => $this->addMedia($media));

        return $this;
    }

    public function removeMedia(Media $media): self
    {
        if ($key = array_search($media, $this->media, true)) {
            unset($this->media[$key]);
        }

        return $this;
    }

    public function setMedia(array $medias): self
    {
        $this->media = [];

        array_walk_recursive($medias, fn (Media $media) => $this->media[] = $media);

        return $this;
    }

    protected function getMediaColumns(): array
    {
        return ['media'];
    }
}
