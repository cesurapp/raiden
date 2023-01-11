FROM openswoole/swoole:22.0.0-php8.2-alpine

### Install Extensions
RUN docker-php-ext-install opcache pdo_pgsql intl
RUN apk add --no-cache --virtual .phpize-deps $PHPIZE_DEPS imagemagick-dev libtool \
    && pecl install imagick && docker-php-ext-enable imagick

### PHP Config
RUN touch "$PHP_INI_DIR/conf.d/custom.ini" \
    && echo "max_file_uploads=50;" >> "$PHP_INI_DIR/conf.d/custom.ini" \
    && echo "post_max_size=20M;" >> "$PHP_INI_DIR/conf.d/custom.ini" \
    && echo "upload_max_filesize=20M;" >> "$PHP_INI_DIR/conf.d/custom.ini" \
    && echo "memory_limit=512M;" >> "$PHP_INI_DIR/conf.d/custom.ini" \
    && echo "realpath_cache_size=16M;" >> "$PHP_INI_DIR/conf.d/custom.ini" \
    && echo "realpath_cache_ttl=600;" >> "$PHP_INI_DIR/conf.d/custom.ini" \
    && echo "opcache.enable=1;" >> "$PHP_INI_DIR/conf.d/custom.ini" \
    && echo "opcache.enable_cli=1;" >> "$PHP_INI_DIR/conf.d/custom.ini" \
    && echo "opcache.memory_consumption=512;" >> "$PHP_INI_DIR/conf.d/custom.ini" \
    && echo "opcache.interned_strings_buffer=64;" >> "$PHP_INI_DIR/conf.d/custom.ini" \
    && echo "opcache.max_accelerated_files=50000;" >> "$PHP_INI_DIR/conf.d/custom.ini" \
    && echo "opcache.validate_timestamps=0;" >> "$PHP_INI_DIR/conf.d/custom.ini" \
    && echo "opcache.preload=/app/config/preload.php;" >> "$PHP_INI_DIR/conf.d/custom.ini" \
    && echo "opcache.preload_user=root;" >> "$PHP_INI_DIR/conf.d/custom.ini" \
    && echo "opcache.jit=tracing;" >> "$PHP_INI_DIR/conf.d/custom.ini" \
    && echo "opcache.jit_buffer_size=256M;" >> "$PHP_INI_DIR/conf.d/custom.ini"

### Copy App
COPY . /app
WORKDIR /app

### Install Composer Dependency
RUN composer install --no-dev
RUN composer dump-autoload --no-dev --classmap-authoritative

### Run & Expose
CMD ["php", "bin/index.php"]
EXPOSE 80