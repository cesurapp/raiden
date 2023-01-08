<?php

namespace Package\MediaBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Package\Library\Client;
use Package\MediaBundle\Compressor\Image;
use Package\MediaBundle\Entity\Media;
use Package\StorageBundle\Storage\Storage;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\Uid\Ulid;

class MediaManager
{
    private bool $imageCompress = true;
    private bool $imageConvertJPG = true;
    private int $imageQuality = 75;
    private int $imageHeight = 1280;
    private int $imageWidth = 720;

    public function __construct(
        private readonly Storage $storage,
        private readonly EntityManagerInterface $em,
        protected readonly LoggerInterface $logger
    ) {
    }

    /**
     * Enable|Disable Image Compressor.
     */
    public function setImageCompress(bool $compress): self
    {
        $this->imageCompress = $compress;

        return $this;
    }

    /**
     * PNG to JPG Converter Enable.
     */
    public function setImageConvertJPG(bool $convertJPG): self
    {
        $this->imageConvertJPG = $convertJPG;

        return $this;
    }

    /**
     * Change Image Quality.
     */
    public function setImageQuality(int $quality): self
    {
        $this->imageQuality = $quality;

        return $this;
    }

    /**
     * Change Max Image Size.
     */
    public function setImageSize(int $height, int $width): self
    {
        $this->imageHeight = $height;
        $this->imageWidth = $width;

        return $this;
    }

    /**
     * Upload HTTP File Request.
     *
     * @return Media[]
     */
    public function uploadFile(Request $request, ?array $keys = null): array
    {
        $data = $keys ? array_intersect_key($request->files->all(), array_flip($keys)) : $request->files->all();

        // Convert to Media Entity
        array_walk_recursive($data, function (&$item) {
            try {
                $item = $this->createMedia(
                    $item->getMimeType(),
                    $item->getExtension(),
                    $item->getContent(),
                    $item->getSize()
                );
            } catch (\Exception $exception) {
                $this->logger->error('HTTP File Upload Failed: '.$exception->getMessage());
            }
        });

        // Save
        $this->em->flush();

        return $data;
    }

    /**
     * @return Media[]
     */
    public function uploadBase64(Request $request, array $keys): array
    {
        $data = array_intersect_key($request->request->all(), array_flip($keys));

        // Convert to Media Entity
        array_walk_recursive($data, function (&$item) {
            try {
                $file = base64_decode($item);
                $mimeType = finfo_buffer(finfo_open(), $file, FILEINFO_MIME_TYPE);
                $extension = (new MimeTypes())->getExtensions($mimeType);

                $item = $this->createMedia($mimeType, $extension[0], $file, strlen($file));
            } catch (\Exception $exception) {
                $this->logger->error('Base64 File Upload Failed: '.$exception->getMessage());
            }
        });

        // Save
        $this->em->flush();

        return $data ?? []; // @phpstan-ignore-line
    }

    /**
     * @return Media[]
     */
    public function uploadLink(Request $request, array $keys): array
    {
        $data = array_intersect_key($request->request->all(), array_flip($keys));

        // Convert to Media Entity
        array_walk_recursive($data, function (&$item) {
            try {
                $file = Client::create($item)->get()->body;
                $mimeType = finfo_buffer(finfo_open(), $file, FILEINFO_MIME_TYPE);
                $extension = (new MimeTypes())->getExtensions($mimeType);

                $item = $this->createMedia($mimeType, $extension[0], $file, strlen($file));
            } catch (\Exception $exception) {
                $this->logger->error('Link File Upload Failed: '.$exception->getMessage());
            }
        });

        // Save
        $this->em->flush();

        return $data; // @phpstan-ignore-line
    }

    protected function createMedia(string $mimeType, string $extension, string $content, int $size): Media
    {
        // Convert JPG
        if ($this->imageConvertJPG) {
            $extension = match ($extension) {
                'png', 'jpeg' => 'jpg',
                default => $extension
            };
            $mimeType = match ($extension) {
                'jpg' => 'image/jpeg',
                default => $mimeType
            };
        }

        // Compress
        if ($this->imageCompress) {
            $content = $this->compress($content, $extension);
        }

        // Create Media
        $media = (new Media())
            ->setMime($mimeType)
            ->setStorage($this->storage->getStorageKey())
            ->setSize($size)
            ->setPath($this->getPath(Ulid::generate(), $extension));
        $this->em->persist($media);

        // Write Storage
        $this->storage->write($content, $media->getPath(), $media->getMime());

        return $media;
    }

    protected function getPath(string $fileName, string $extension): string
    {
        return strtolower(date('Y/m/d').'/'.$fileName.'.'.$extension);
    }

    protected function compress(string $data, string $extension): string
    {
        return match ($extension) {
            'jpg', 'jpeg', 'png' => Image::create($data)
                ->resize($this->imageHeight, $this->imageWidth)
                ->output($extension, $this->imageQuality),
            default => $data
        };
    }
}
