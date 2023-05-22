ARG PHP_VERSION=8.1
ARG WEBROOT=public

#
# CLI image
#
FROM uselagoon/php-${PHP_VERSION}-cli-drupal:latest as cli

ARG WEBROOT

COPY composer.json composer.lock /app/
# COPY patches /app/patches

RUN composer --ansi install --no-dev --optimize-autoloader --prefer-dist --no-progress

COPY . /app

RUN mkdir -p -v -m775 /app/public/sites/default/files && \
    chmod 0755 /app/public/sites/default && \
    chmod 0444 /app/public/sites/default/settings.php

# Define where the Drupal Root is located
ENV WEBROOT=${WEBROOT}

#
# Nginx image
#
FROM uselagoon/nginx-drupal:latest as nginx

ARG WEBROOT

COPY --from=cli /app /app

# Define where the Drupal Root is located
ENV WEBROOT=${WEBROOT}

#
# PHP-FPM image
#
FROM uselagoon/php-${PHP_VERSION}-fpm:latest as php

COPY --from=cli /app /app
