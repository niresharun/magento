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
    wget \
    nginx \
    supervisor \  
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install -j$(nproc) \
    intl \
    pdo_mysql \
    gd \
    zip

# Install Composer globally
RUN wget https://getcomposer.org/download/2.7.0/composer.phar && \
    mv composer.phar /usr/local/bin/composer && \
    chmod +x /usr/local/bin/composer

# Copy Magento source code
COPY . .

# Copy Nginx configuration
COPY ./nginx.conf /etc/nginx/conf.d/app.conf


# Copy Supervisor configuration
COPY ./supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Set permissions
RUN chown -R www-data:www-data /var/www/html

# Expose ports
EXPOSE 80 9000

# Start Supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
