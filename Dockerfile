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

# Increase PHP upload limits
RUN echo "upload_max_filesize = 256M\npost_max_size = 256M\nmemory_limit = 256M\nmax_execution_time = 300\nmax_input_time = 300\nerror_reporting = E_ALL & ~E_DEPRECATED & ~E_NOTICE" > /usr/local/etc/php/conf.d/uploads.ini

# Install dependencies
RUN apt-get update && apt-get install -y --no-install-recommends unzip curl \
    && rm -rf /var/lib/apt/lists/*

# Install Kale parent theme
RUN curl -L https://downloads.wordpress.org/theme/kale.latest-stable.zip -o /tmp/kale.zip \
    && unzip /tmp/kale.zip -d /var/www/html/wp-content/themes/ \
    && rm /tmp/kale.zip

# Install plugins from wordpress.org
RUN curl -L https://downloads.wordpress.org/plugin/kirki.latest-stable.zip -o /tmp/kirki.zip \
    && unzip /tmp/kirki.zip -d /var/www/html/wp-content/plugins/ \
    && rm /tmp/kirki.zip

RUN curl -L https://downloads.wordpress.org/plugin/recent-posts-widget-with-thumbnails.latest-stable.zip -o /tmp/rpwt.zip \
    && unzip /tmp/rpwt.zip -d /var/www/html/wp-content/plugins/ \
    && rm /tmp/rpwt.zip

RUN curl -L https://downloads.wordpress.org/plugin/wpforms-lite.latest-stable.zip -o /tmp/wpforms.zip \
    && unzip /tmp/wpforms.zip -d /var/www/html/wp-content/plugins/ \
    && rm /tmp/wpforms.zip

# Copy the Kale child theme
COPY kale-child/ /var/www/html/wp-content/themes/kale-child/

# Fix permissions
RUN chown -R www-data:www-data /var/www/html/wp-content

EXPOSE 80
