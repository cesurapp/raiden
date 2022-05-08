## Storage Bundle

__Supported Drivers__

1. BackBlaze
2. S3
3. DOSpaces
4. Local
5. Wasabi
6. Linode

__Configuration__
```yaml
storage:
  default: main
  devices:
    local:
      driver: local
      root: "%kernel.project_dir%/storage/files"

    main:
      driver: s3 #s3, backblaze, linode, local, dospaces, wasabi
      root: "%kernel.project_dir%/storage/files"
      accessKey: "access_key"
      secretKey: "secret_key"
      bucket: "private_pdf"
      region: !php/const Package\StorageBundle\Driver\S3::US_EAST_1
      acl: !php/const Package\StorageBundle\Driver\S3::ACL_PRIVATE
```

__Tests__

phpunit.xml.dist
```xml
<server name="S3_ACCESS_KEY" value="KEY" />
<server name="S3_SECRET" value="KEY" />
```