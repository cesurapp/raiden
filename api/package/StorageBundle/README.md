## Storage Bundle

### Supported Drivers
1. BackBlaze
2. CloudFlare
3. Local

### Configuration
```yaml
storage:
  default: main
  devices:
    local:
      driver: local
      root: "%kernel.project_dir%/storage/files"
    
    main:
      driver: s3 #backblaze, cloudflare, local
      root: "%kernel.project_dir%/storage/files"
      accessKey: "access_key"
      secretKey: "secret_key"
      bucket: "private_pdf"
      region: "auto"
      endPoint: "https://11d0547262baad2e6bdeb340e88f83be.r2.cloudflarestorage.com"
```

### Tests
```xml
<!--phpunit.xml.dist-->

<server name="CLOUDFLARE_R2_ACCESS_KEY" value="KEY" />
<server name="CLOUDFLARE_R2_SECRET" value="KEY" />
<server name="CLOUDFLARE_R2_ENDPOINT" value="KEY" />
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