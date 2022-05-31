### => Imagick Helper

---

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