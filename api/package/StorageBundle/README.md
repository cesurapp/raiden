# Storage Bundle

It is used for file storage. There is no database connection.
Async-aws/s3 package is used for S3 connection.
Tests are available for each driver, any test without access codes is skipped.
You can use multiple drives at the same time. Use MediaBunle to keep track of files.

### Supported Drivers

* BackBlaze
* CloudFlare
* Local

### Configuration

```yaml
# config/packages/storage.yaml

storage:
  default: main
  devices:
    local:
      driver: local #backblaze, cloudflare, local
      root: "%kernel.project_dir%/storage/files"

    main:
      driver: s3
      root: /
      accessKey: "access_key"
      secretKey: "secret_key"
      bucket: "bucket_name"
      region: "auto"
      endPoint: "https://11d0547262baad2e6bdeb340e88f83be.r2.cloudflarestorage.com"

    backblaze:
      driver: backblaze
      root: /
      accessKey: "access_key"
      secretKey: "secret_key"
      bucket: "bucket_name"
      region: "auto"
```

### Tests

Tests for which access codes are not defined are skipped.

```xml
<!--phpunit.xml.dist-->

<!--BackBlaze Test Keys-->
<server name="BACKBLAZE_ACCESS_KEY" value="KEY"/>
<server name="BACKBLAZE_SECRET" value="KEY"/>
<server name="BACKBLAZE_BUCKET" value="KEY"/>

<!--CloudFlare Test Keys-->
<server name="CLOUDFLARE_R2_ACCESS_KEY" value="KEY"/>
<server name="CLOUDFLARE_R2_SECRET" value="KEY"/>
<server name="CLOUDFLARE_R2_ENDPOINT" value="KEY"/>
<server name="CLOUDFLARE_R2_BUCKET" value="unit-test"/>
```

### Usage

```php
public function controllerAction(\Package\StorageBundle\Storage\Storage $storage) {
    // Upload File
    $storage->upload('source_path', 'upload_path');
    
    // Write File
    $storage->write('upload_path', 'text_content', 'application/text');
    
    // Delete
    $storage->delete('file_path');
    
    // Check
    $storage->exists('file_path');
    
    // Read
    $storage->read('file_path')
}
```