## Library

### => Swoole Coroutine HTTP Client

---

GET | DELETE Request:
```php
\Package\Library\Client::create('https://www.google.com')
    ->setHeaders(['Host' => 'www.app.test'])
    //->get(['key' => 'value'])
    //->delete(['key' => 'value'])
```

POST | PUT | PATCH Request:
```php
\Package\Library\Client::create('https://www.google.com')
    ->setHeaders(['Host' => 'www.app.test'])
    //->post(['key' => 'value'])
    //->put(['key' => 'value'])
    //->patch(['key' => 'value'])
```

Custom Request:
```php
\Package\Library\Client::create('https://www.google.com')
    ->setHeaders(['Host' => 'www.app.test'])
    ->setMethod('POST')
    ->setData(['key' => 'value'])
    ->setQuery(['key' => 'value'])
    ->execute();   
```


### => Imagick Helper

---

Compress JPG:
```php
\Package\Library\Image::create(file_get_contents('image.jpg'))->save('save_path.jpg', 'jpg', 75);
```

Convert & Compress to JPG:
```php
\Package\Library\Image::create(file_get_contents('image.png'))->save('save_path.jpg', 'jpg', 75);
```

Resize Aspect Ratio & Convert JPG:
```php
\Package\Library\Image::create(file_get_contents('image.png'))->resize(100, 100)->output('jpg', 75);
```