FROM wordpress:php8.2-apache

# Accept build args injected by Coolify
ARG WORDPRESS_DB_HOST
ARG WORDPRESS_DB_USER
ARG WORDPRESS_DB_PASSWORD
ARG WORDPRESS_DB_NAME

# Persist them as runtime environment variables
ENV WORDPRESS_DB_HOST=$WORDPRESS_DB_HOST
ENV WORDPRESS_DB_USER=$WORDPRESS_DB_USER
ENV WORDPRESS_DB_PASSWORD=$WORDPRESS_DB_PASSWORD
ENV WORDPRESS_DB_NAME=$WORDPRESS_DB_NAME

# Increase PHP upload limits (needed for migration plugins)
RUN echo "upload_max_filesize = 256M\npost_max_size = 256M\nmemory_limit = 256M\nmax_execution_time = 300\nmax_input_time = 300\nerror_reporting = E_ALL & ~E_DEPRECATED & ~E_NOTICE\ndisplay_errors = Off" > /usr/local/etc/php/conf.d/uploads.ini

# Install dependencies
RUN apt-get update && apt-get install -y --no-install-recommends unzip curl \
    && rm -rf /var/lib/apt/lists/*

# Install Kale parent theme
RUN curl -L https://downloads.wordpress.org/theme/kale.latest-stable.zip -o /tmp/kale.zip \
    && unzip /tmp/kale.zip -d /var/www/html/wp-content/themes/ \
    && rm /tmp/kale.zip

# Copy the Kale child theme
COPY kale-child/ /var/www/html/wp-content/themes/kale-child/

# Fix permissions
RUN chown -R www-data:www-data /var/www/html/wp-content

EXPOSE 80
