# Use PHP image with FPM
FROM php:8.2-fpm

# Set the working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libicu-dev \
    zlib1g-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    unzip \
    wget	
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install -j$(nproc) \
    intl \
    pdo_mysql \
    gd \
    zip

# Install Composer globally
#RUN apt update && apt install curl && \
#  curl -sS https://getcomposer.org/download/2.2.12/composer.phar \
#  && chmod +x composer.phar && mv composer.phar /usr/local/bin/composer
RUN wget https://getcomposer.org/download/2.7.0/composer.phar \
    mv composer.phar /usr/local/bin/composer \	
    chmod +x /usr/local/bin/composer	
# Copy Symfony application files
COPY ./ .

# Install dependencies
#RUN composer install --no-scripts --no-autoloader

# Expose port 9000 for PHP-FPM
EXPOSE 9000

CMD ["php-fpm"]
 
