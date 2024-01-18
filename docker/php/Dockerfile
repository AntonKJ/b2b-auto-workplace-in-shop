FROM php:7.2-fpm

RUN apt-get update && \
    apt-get -y install \
        gnupg2 && \
    apt-key update && \
    apt-get update && \
    apt-get -y install \
            g++ \
            git \
            curl \
            imagemagick \
            libcurl3-dev \
            libicu-dev \
            libfreetype6-dev \
            libjpeg-dev \
            libjpeg62-turbo-dev \
            libmagickwand-dev \
            libpq-dev \
            libpng-dev \
            libxml2-dev \
            libxslt-dev \
            libzip-dev \
            zlib1g-dev \
            default-mysql-client \
            openssh-client \
            nano \
            unzip \
            libcurl4-openssl-dev \
            libssl-dev \
        --no-install-recommends && \
        apt-get clean && \
        rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Install PHP extensions required for Yii 2.0 Framework
RUN docker-php-ext-configure gd \
        --with-freetype-dir=/usr/include/ \
        --with-png-dir=/usr/include/ \
        --with-jpeg-dir=/usr/include/ && \
    docker-php-ext-configure bcmath && \
    docker-php-ext-install \
        soap \
        zip \
        curl \
        bcmath \
        exif \
        gd \
        iconv \
        intl \
        mbstring \
        opcache \
        pdo_mysql \
        pdo_pgsql \
        mysqli \
        dom \
        xml \
        xsl \
        mysqli

# Install xdebug
RUN cd /tmp && \
    git clone git://github.com/xdebug/xdebug.git && \
    cd xdebug && \
    git checkout 52adff7539109db592d07d3f6c325f6ee2a7669f && \
    phpize && \
    ./configure --enable-xdebug && \
    make && \
    make install && \
    rm -rf /tmp/xdebug

# memcache
ENV MEMCACHED_DEPS libmemcached-dev git
RUN set -xe \
 && apt-get update \
 && apt-get install -y $MEMCACHED_DEPS \
 && curl https://codeload.github.com/php-memcached-dev/php-memcached/zip/php7 -o /tmp/memcached.zip \
 && mkdir -p /usr/src/php/ext \
 && unzip /tmp/memcached.zip -d /usr/src/php/ext \
 && docker-php-ext-configure /usr/src/php/ext/php-memcached-php7 \
     --disable-memcached-sasl \
 && docker-php-ext-install /usr/src/php/ext/php-memcached-php7 \
 && rm -rf /usr/src/php/ext/php-memcached-php7 /tmp/memcached.zip

 # Install composer
RUN curl -sS https://getcomposer.org/installer | php -- \
        --filename=composer \
        --install-dir=/usr/local/bin && \
        echo "alias composer='composer'" >> /root/.bashrc && \
        composer

RUN mkdir ~/.ssh && ln -s /run/secrets/host_ssh_key ~/.ssh/id_rsa
RUN chown -h $(id -u):$(id -g) ~/.ssh/id_rsa

#RUN cd /app/evolution && composer install --no-interaction -o