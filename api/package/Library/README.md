## Library

### Swoole Coroutine HTTP Client

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