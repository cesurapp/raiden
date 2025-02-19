FROM php:8.3.11-cli-alpine

### PHP Extension
ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN install-php-extensions swoole intl bcmath opcache pdo_pgsql @composer

### PHP Config
RUN touch "$PHP_INI_DIR/conf.d/custom.ini" \
    && echo "max_file_uploads=50;" >> "$PHP_INI_DIR/conf.d/custom.ini" \
    && echo "post_max_size=20M;" >> "$PHP_INI_DIR/conf.d/custom.ini" \
    && echo "upload_max_filesize=20M;" >> "$PHP_INI_DIR/conf.d/custom.ini" \
    && echo "memory_limit=256M;" >> "$PHP_INI_DIR/conf.d/custom.ini" \
    && echo "realpath_cache_size=16M;" >> "$PHP_INI_DIR/conf.d/custom.ini" \
    && echo "realpath_cache_ttl=600;" >> "$PHP_INI_DIR/conf.d/custom.ini" \
    && echo "opcache.enable=1;" >> "$PHP_INI_DIR/conf.d/custom.ini" \
    && echo "opcache.enable_cli=1;" >> "$PHP_INI_DIR/conf.d/custom.ini" \
    && echo "opcache.memory_consumption=512;" >> "$PHP_INI_DIR/conf.d/custom.ini" \
    && echo "opcache.interned_strings_buffer=64;" >> "$PHP_INI_DIR/conf.d/custom.ini" \
    && echo "opcache.max_accelerated_files=50000;" >> "$PHP_INI_DIR/conf.d/custom.ini" \
    && echo "opcache.validate_timestamps=0;" >> "$PHP_INI_DIR/conf.d/custom.ini" \
    && echo "opcache.jit=tracing;" >> "$PHP_INI_DIR/conf.d/custom.ini" \
    && echo "opcache.jit_buffer_size=256M;" >> "$PHP_INI_DIR/conf.d/custom.ini"
    #&& echo "opcache.preload=/app/config/preload.php;" >> "$PHP_INI_DIR/conf.d/custom.ini" \
    #&& echo "opcache.preload_user=root;" >> "$PHP_INI_DIR/conf.d/custom.ini"

### Copy App
COPY . /app
WORKDIR /app

### Install Composer Dependency
RUN composer install --no-dev && composer dump-autoload --no-dev --classmap-authoritative

### Run & Expose
CMD ["php", "bin/index.php"]
EXPOSE 80