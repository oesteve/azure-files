FROM php:7.2-cli


RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php composer-setup.php --filename=composer --install-dir=/usr/local/bin && \
    php -r "unlink('composer-setup.php');"

COPY . /usr/src/azure-files
WORKDIR /usr/src/azure-files

RUN apt-get update && apt-get install -y \
  git \
  zip

RUN composer install --prefer-dist

ENTRYPOINT [ "/usr/src/azure-files/docker-entrypoint.sh" ]
