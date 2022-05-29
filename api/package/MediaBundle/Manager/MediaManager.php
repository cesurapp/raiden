<?php

namespace Package\MediaBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Package\MediaBundle\Compressor\Image;
use Package\MediaBundle\Entity\Media;
use Package\StorageBundle\Storage\Storage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\Ulid;

class MediaManager
{
    public function __construct(private readonly Storage $storage, private readonly EntityManagerInterface $em)
    {
    }

    /**
     * Upload HTTP File Request.
     */
    public function uploadFile(Request $request, ?array $keys = null): array
    {
        $medias = [];

        if ($request->files->count() > 0) {
            if (null !== $keys) {
                foreach ($keys as $key) {
                    dump($request->files->get($key));
                }
            }
            dump($request->files->all());
           /* foreach ($request->files->all() as $file) {
                // Create Media
                $media = (new Media())
                    ->setMime($file->getMimeType())
                    ->setStorage($this->storage->getStorageKey())
                    ->setSize($file->getSize())
                    ->setPath($this->getPath(Ulid::generate(), $file->getExtension()));
                $this->em->persist($media);

                // Write Storage
                $this->storage->write($media->getPath(), $file->getContent(), $media->getMime());

                // Append
                $files[] = $media;
            }*/

            // Save
            $this->em->flush();
        }

        return $medias;
    }

    public function uploadBase64(Request $request, array $keys): array
    {
        $files = array_map(static function ($key) {
        }, $keys);
    }

    public function uploadLink(Request $request, array $keys): array
    {
    }

    protected function getPath(string $fileName, string $extension): string
    {
        return strtolower(date('Y/m/d').'/'.$fileName.'.'.$extension);
    }

    protected function compress(string $data, string $extension): bool|string|null
    {
        return Image::create($data)->output($extension);
    }
}
