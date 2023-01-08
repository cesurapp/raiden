# Media Bundle
It is used to keep track of files.
It is used to keep track of files. When media is deleted, it is also automatically removed from storage.
You can use the same media with multiple tables. You must create a new trait for each column.
Imagick compression, resizing, conversion support is available for image files.

### Commands

```shell
bin/console media:status     # View Media Storage Details
```

### Create Media Column

__Note:__ Copy the "MediaTrait" for the new column.

```php
use \Package\MediaBundle\Entity\MediaInterface;
use \Package\MediaBundle\Entity\MediaTrait;

class UserEntity implements MediaInterface {
    use MediaTrait;

    /**
     * For a single column, this is not necessary.
     */
    //public function getMediaColumns(): array {
    //    return ['media'];
    //}
}
```

### Upload Image

```php
use \Package\MediaBundle\Manager\MediaManager;

class ExampleController  {
    public function index(Request $request, MediaManager $manager): void {
        $images = $manager
            ->setImageCompress(true)         // Enable Image Compressor
            ->setImageConvertJPG(true)       // PNG to JPG Convertor
            ->setImageQuality(75)            // Default Image Quality
            ->setImageSize(1024,768)         // Maximum Image Size
            //->uploadFile($request)                         // HTTP File Upload
            //->uploadBase64($request, ['base64DataKey'])    // Json Base64 Image Upload
            ->uploadLink($request, ['imageLinkKey'])         // Image Link Upload
    }
}
```

### Imagick Helper

Compress JPG:

```php
\Package\MediaBundle\Compressor\Image::create(file_get_contents('image.jpg'))->save('save_path.jpg', 'jpg', 75);
```

Convert & Compress to JPG:

```php
\Package\MediaBundle\Compressor\Image::create(file_get_contents('image.png'))->save('save_path.jpg', 'jpg', 75);
```

Resize Aspect Ratio & Convert JPG:

```php
\Package\MediaBundle\Compressor\Image::create(file_get_contents('image.png'))->resize(100, 100)->output('jpg', 75);
```
