FROM ubuntu:latest AS base
# FROM 857279058880.dkr.ecr.us-east-1.amazonaws.com/magento/247-p3:latest
ENV DEBIAN_FRONTEND noninteractive

#Install dependencies
RUN apt update
RUN apt install -y software-properties-common wget nginx nano curl mysql-client
RUN add-apt-repository -y ppa:ondrej/php
RUN apt update
RUN apt install -y php8.2\
    php8.2-cli\
    php8.2-common\
    php8.2-fpm\
    php8.2-mysql\
    php8.2-zip\
    php8.2-gd\
    php8.2-mbstring\
    php8.2-curl\
    php8.2-xml\
    php8.2-bcmath\
    php8.2-pdo\
    php8.2-intl\
    php8.2-soap
RUN apt install -y php8.2-fpm php8.2-cli
# Install Composer
RUN wget https://getcomposer.org/download/2.7.0/composer.phar && \
    mv composer.phar /usr/local/bin/composer && \
    chmod +x /usr/local/bin/composer

# Configure Nginx
RUN echo "\
    server {\n\
        listen 80;\n\
        listen [::]:80;\n\
        root /var/www/html/public;\n\
        add_header X-Frame-Options \"SAMEORIGIN\";\n\
        add_header X-Content-Type-Options \"nosniff\";\n\
        index index.php;\n\
        charset utf-8;\n\
        location / {\n\
            try_files \$uri \$uri/ /index.php?\$query_string;\n\
        }\n\
        location = /favicon.ico { access_log off; log_not_found off; }\n\
        location = /robots.txt  { access_log off; log_not_found off; }\n\
        error_page 404 /index.php;\n\
        location ~ \.php$ {\n\
            include snippets/fastcgi-php.conf;\n\
            fastcgi_pass unix:/run/php/php8.2-fpm.sock;\n\
            fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;\n\
            include fastcgi_params;\n\
        }\n\
        location ~ /\.(?!well-known).* {\n\
            deny all;\n\
        }\n\
    }\n" > /etc/nginx/sites-available/default


# Copy application code
RUN rm -rf /var/www/html/public/*
COPY ./public /var/www/html/public
WORKDIR /var/www/html

# Install Composer dependencies
RUN cd /var/www/html/public && composer install 
RUN cd /var/www/html/public && composer update
RUN cd /var/www/html/public && composer require laminas/laminas-mvc
RUN cd /var/www/html/public && composer require laminas/laminas-di
RUN cd /var/www/html/public && composer require colinmollenhour/cache-backend-file
#--no-dev --optimize-autoloader
#     # Copy the entrypoint script
# COPY entrypoint.sh /usr/local/bin/entrypoint.sh

# # Make the script executable
# RUN chmod +x /usr/local/bin/entrypoint.sh

# # Set the entrypoint
# ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
# Create startup script
RUN echo "\
    #!/bin/sh\n\
    echo \"Starting services...\"\n\
    service php8.2-fpm start\n\
    nginx -g \"daemon off;\" &\n\
    echo \"Ready.\"\n\
    tail -s 1 /var/log/nginx/*.log -f\n\
    " > /start.sh


# Set permissions
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html

EXPOSE 80

CMD ["sh", "/start.sh"]