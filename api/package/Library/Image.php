<?php

namespace Package\Library;

use Imagick;
use ImagickDraw;
use ImagickPixel;

/**
 * Imagick Library.
 *
 * @see https://github.com/utopia-php/image
 *
 * @author  Ramazan APAYDIN
 */
class Image
{
    public const GRAVITY_CENTER = 'center';
    public const GRAVITY_TOP_LEFT = 'top-left';
    public const GRAVITY_TOP = 'top';
    public const GRAVITY_TOP_RIGHT = 'top-right';
    public const GRAVITY_LEFT = 'left';
    public const GRAVITY_RIGHT = 'right';
    public const GRAVITY_BOTTOM_LEFT = 'bottom-left';
    public const GRAVITY_BOTTOM = 'bottom';
    public const GRAVITY_BOTTOM_RIGHT = 'bottom-right';

    private Imagick $image;

    private int $width;

    private int $height;

    private int $borderWidth = 0;

    private String $borderColor = '';

    private int $rotation = 0;

    public function __construct(string $data)
    {
        $this->image = new Imagick();
        $this->image->readImageBlob($data);

        $this->width = $this->image->getImageWidth();
        $this->height = $this->image->getImageHeight();

        // Use metadata to fetch rotation. Will be perform right before exporting
        $orientationType = $this->image->getImageProperties()['exif:Orientation'] ?? $this->image->getImageOrientation();

        // Reference: https://docs.imgix.com/apis/rendering/rotation/orient
        // Mirror rotations are ignored, because we don't support mirroring
        if (!empty($orientationType)) {
            $this->rotation = match ((int) $orientationType) {
                3 => 180,
                6 => 90,
                8 => -90,
                default => 0
            };
        }
    }

    public static function getGravityTypes(): array
    {
        return [
            self::GRAVITY_CENTER,
            self::GRAVITY_TOP_LEFT,
            self::GRAVITY_TOP,
            self::GRAVITY_TOP_RIGHT,
            self::GRAVITY_LEFT,
            self::GRAVITY_RIGHT,
            self::GRAVITY_BOTTOM_LEFT,
            self::GRAVITY_BOTTOM,
            self::GRAVITY_BOTTOM_RIGHT,
        ];
    }

    public function crop(int $width, int $height, string $gravity = Image::GRAVITY_CENTER): Image
    {
        $originalAspect = $this->width / $this->height;

        if (empty($width)) {
            $width = (int) ($height * $originalAspect);
        }

        if (empty($height)) {
            $height = (int) ($width / $originalAspect);
        }

        if (empty($height) && empty($width)) {
            $height = $this->height;
            $width = $this->width;
        }

        $resizeWidth = $this->width;
        $resizeHeight = $this->height;
        if (self::GRAVITY_CENTER !== $gravity) {
            if ($width > $height) {
                $resizeWidth = $width;
                $resizeHeight = (int) ($width * $originalAspect);
            } else {
                $resizeWidth = (int) ($height * $originalAspect);
                $resizeHeight = $height;
            }
        }

        $x = $y = 0;
        switch ($gravity) {
            case self::GRAVITY_TOP_LEFT:
                break;
            case self::GRAVITY_TOP:
                $x = ($resizeWidth / 2) - ($width / 2);
                break;
            case self::GRAVITY_TOP_RIGHT:
                $x = $resizeWidth - $width;
                break;
            case self::GRAVITY_LEFT:
                $y = ($resizeHeight / 2) - ($height / 2);
                break;
            case self::GRAVITY_RIGHT:
                $x = $resizeWidth - $width;
                $y = ($resizeHeight / 2) - ($height / 2);
                break;
            case self::GRAVITY_BOTTOM_LEFT:
                $y = $resizeHeight - $height;
                break;
            case self::GRAVITY_BOTTOM:
                $x = ($resizeWidth / 2) - ($width / 2);
                $y = $resizeHeight - $height;
                break;
            case self::GRAVITY_BOTTOM_RIGHT:
                $x = $resizeWidth - $width;
                $y = $resizeHeight - $height;
                break;
            default:
                $x = ($resizeWidth / 2) - ($width / 2);
                $y = ($resizeHeight / 2) - ($height / 2);
                break;
        }
        $x = (int) $x;
        $y = (int) $y;

        if ('GIF' === $this->image->getImageFormat()) {
            $this->image = $this->image->coalesceImages();

            foreach ($this->image as $frame) {
                if (self::GRAVITY_CENTER === $gravity) {
                    $frame->cropThumbnailImage($width, $height);
                } else {
                    $frame->scaleImage($resizeWidth, $resizeHeight, false);
                    $frame->cropImage($width, $height, $x, $y);
                    $frame->thumbnailImage($width, $height);
                }
            }

            $this->image->deconstructImages();
        } else {
            foreach ($this->image as $frame) {
                if (self::GRAVITY_CENTER === $gravity) {
                    $this->image->cropThumbnailImage($width, $height);
                } else {
                    $this->image->scaleImage($resizeWidth, $resizeHeight, false);
                    $this->image->cropImage($width, $height, $x, $y);
                }
            }
        }
        $this->height = $height;
        $this->width = $width;

        return $this;
    }

    public function setBorder(int $borderWidth, string $borderColor): self
    {
        $this->borderWidth = $borderWidth;
        $this->borderColor = $borderColor;
        $this->image->borderImage($borderColor, $borderWidth, $borderWidth);

        return $this;
    }

    public function setBorderRadius(int $cornerRadius): self
    {
        $mask = new Imagick();
        $mask->newImage($this->width, $this->height, new ImagickPixel('transparent'), 'png');

        $rectwidth = ($this->borderWidth > 0 ? ($this->width - ($this->borderWidth + 1)) : $this->width - 1);
        $rectheight = ($this->borderWidth > 0 ? ($this->height - ($this->borderWidth + 1)) : $this->height - 1);

        $shape = new ImagickDraw();
        $shape->setFillColor(new ImagickPixel('black'));
        $shape->roundRectangle($this->borderWidth, $this->borderWidth, $rectwidth, $rectheight, $cornerRadius, $cornerRadius);

        $mask->drawImage($shape);
        $this->image->compositeImage($mask, Imagick::COMPOSITE_DSTIN, 0, 0);

        if ($this->borderWidth > 0) {
            $bc = new ImagickPixel();
            $bc->setColor($this->borderColor);

            $strokeCanvas = new Imagick();
            $strokeCanvas->newImage($this->width, $this->height, new ImagickPixel('transparent'), 'png');

            $shape2 = new ImagickDraw();
            $shape2->setFillColor(new ImagickPixel('transparent'));
            $shape2->setStrokeWidth($this->borderWidth);
            $shape2->setStrokeColor($bc);
            $shape2->roundRectangle($this->borderWidth, $this->borderWidth, $rectwidth, $rectheight, $cornerRadius, $cornerRadius);

            $strokeCanvas->drawImage($shape2);
            $strokeCanvas->compositeImage($this->image, Imagick::COMPOSITE_DEFAULT, 0, 0);

            $this->image = $strokeCanvas;
        }

        return $this;
    }

    public function setOpacity(float $opacity): self
    {
        if ((empty($opacity) && 0 != $opacity) || 1 == $opacity) {
            return $this;
        }
        $this->image->setImageAlpha($opacity);

        return $this;
    }

    /**
     * Rotates an image to $degree degree.
     */
    public function setRotation(int $degree): self
    {
        if (empty($degree) || 0 == $degree) {
            return $this;
        }

        $this->image->rotateImage('transparent', $degree);

        return $this;
    }

    public function setBackground(mixed $color): Image
    {
        $this->image->setImageBackgroundColor($color);
        $this->image = $this->image->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);

        return $this;
    }

    public function output(string $type, int $quality = 75): bool|string|null
    {
        return $this->save(null, $type, $quality);
    }

    public function save(string $path = null, string $type = '', int $quality = 75): string|bool|null
    {
        // Create directory with write permissions
        if (null !== $path && !\file_exists(\dirname($path))) {
            if (!@\mkdir(\dirname($path), 0755, true)) {
                throw new \RuntimeException('Can\'t create directory '.\dirname($path));
            }
        }

        // Apply original metadata rotation
        if (0 !== $this->rotation) {
            $this->image->rotateImage('transparent', $this->rotation);
        }

        switch ($type) {
            case 'jpg':
            case 'jpeg':
                $this->image->setImageCompressionQuality($quality);
                $this->image->setImageFormat('jpg');
                break;
            case 'gif':
                $this->image->setImageFormat('gif');
                break;
            case 'webp':
                try {
                    $this->image->setImageFormat('webp');
                } catch (\Throwable $th) {
                    $signature = $this->image->getImageSignature();
                    $temp = '/tmp/temp-'.$signature.'.'.\strtolower($this->image->getImageFormat());
                    $output = '/tmp/output-'.$signature.'.webp';

                    // save temp
                    $this->image->writeImages($temp, true);

                    // convert temp
                    \exec("cwebp -quiet -metadata none -q $quality $temp -o $output");

                    $data = \file_get_contents($output);

                    // load webp
                    if (empty($path)) {
                        return $data;
                    }

                    \file_put_contents($path, $data, LOCK_EX);

                    $this->image->clear();
                    $this->image->destroy();

                    //delete webp
                    \unlink($output);
                    \unlink($temp);

                    return null;
                }
                break;
            case 'png':
                /* Scale quality from 0-100 to 0-9 */
                $scaleQuality = round(($quality / 100) * 9);

                /* Invert quality setting as 0 is best, not 9 */
                $invertScaleQuality = (int) (9 - $scaleQuality);

                $this->image->setImageCompressionQuality($invertScaleQuality);
                $this->image->setImageFormat('png');
                break;
            default:
                throw new \RuntimeException('Invalid output type given');
        }

        if (empty($path)) {
            return $this->image->getImagesBlob();
        }

        $this->image->writeImages($path, true);

        $this->image->clear();
        $this->image->destroy();

        return true;
    }

    protected function getSizeByFixedHeight(int $newHeight): int
    {
        $ratio = $this->width / $this->height;
        $newWidth = $newHeight * $ratio;

        return (int) $newWidth;
    }

    protected function getSizeByFixedWidth(int $newWidth): int
    {
        $ratio = $this->height / $this->width;
        $newHeight = $newWidth * $ratio;

        return (int) $newHeight;
    }
}
