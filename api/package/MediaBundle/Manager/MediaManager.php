<?php

namespace Package\MediaBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Package\MediaBundle\Compressor\Image;
use Package\MediaBundle\Entity\Media;
use Package\StorageBundle\Storage\Storage;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\Ulid;

class MediaManager
{
    public function __construct(
        private Storage $storage,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger
    ) {
    }

    /**
     * Upload File|Base64|Link.
     *
     * @return Media[]|array
     */
    public function upload(Request $request, array $keys = []): ?array
    {
        $files = [
            ...$this->uploadFile($request),
          //  ...$this->uploadBase64($request, $keys),
          //  ...$this->uploadLink($request, $keys),
        ];

        // Save
        if ($files) {
            $this->entityManager->flush();
        }

        return $files;
    }

    public function uploadFile(Request $request): array
    {
        $files = [];

        if ($request->files->count() > 0) {
            /** @var UploadedFile $file */
            foreach ($request->files->all() as $file) {
                // Create Media
                $media = (new Media())
                    ->setMime($file->getMimeType())
                    ->setStorage($this->storage->getStorageKey())
                    ->setSize($file->getSize())
                    ->setPath($this->getPath(Ulid::generate(), $file->getExtension()));
                $this->entityManager->persist($media);

                // Write Storage
                $this->storage->write($media->getPath(), $file->getContent(), $media->getMime());

                $files[] = $media;
            }
        }

        return $files;
    }

    public function uploadBase64(Request $request, array $keys): array
    {
        $files = array_map(static function ($key) {
        }, $keys);

    }

    public function uploadLink(Request $request, array $keys): array
    {
    }

    /**
     * Read Media Content.
     */
    public function read(Media $media): string
    {
        return $this->storage->device($media->getStorage())->read($media->getPath());
    }

    /**
     * Delete Media.
     *
     * @param Media|Media[] $media
     */
    public function delete(Media|array $media): bool
    {
        if (!is_array($media)) {
            $media = [$media];
        }

        try {
            foreach ($media as $item) {
                $item->decrCounter();

                if (0 === $item->getCounter()) {
                    $this->storage->device($item->getStorage())->delete($item->getPath());
                    $this->entityManager->remove($item);
                } else {
                    $this->entityManager->persist($item);
                }
            }
        } catch (\Exception $exception) {
            $this->logger->error('Media not removed! Message: '.$exception->getMessage());

            return false;
        }

        $this->entityManager->flush();

        return true;
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
