FROM php:7.4.2-fpm-alpine
WORKDIR /app

# apk is only works for `alpine` images, for others it should be apt
RUN apk --update upgrade \
    && apk add --no-cache autoconf automake make gcc g++ bash icu-dev rabbitmq-c rabbitmq-c-dev vim \
    && pecl install amqp-1.9.4 \
    && pecl install apcu-5.1.18 \
    && pecl install xdebug \
    && echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_enable=on" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_autostart=off" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && docker-php-ext-install -j$(nproc) \
        bcmath \
        opcache \
        intl \
        pdo_mysql \
    && docker-php-ext-enable \
        amqp \
        apcu \
        opcache \
