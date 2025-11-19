# Dockerfile pour BatiSaaS - Production optimisée
FROM php:8.2-apache

# Métadonnées
LABEL maintainer="BatiSaaS Team"
LABEL description="Application SaaS de gestion de chantiers BTP"
LABEL version="2.0.0"

# Installation des dépendances système
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libwebp-dev \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Installation des extensions PHP requises
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_mysql \
    mysqli \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    intl \
    opcache

# Configuration PHP pour production
RUN { \
    echo 'opcache.enable=1'; \
    echo 'opcache.memory_consumption=256'; \
    echo 'opcache.interned_strings_buffer=16'; \
    echo 'opcache.max_accelerated_files=10000'; \
    echo 'opcache.revalidate_freq=2'; \
    echo 'opcache.fast_shutdown=1'; \
    echo 'opcache.enable_cli=0'; \
    echo 'upload_max_filesize=50M'; \
    echo 'post_max_size=50M'; \
    echo 'memory_limit=512M'; \
    echo 'max_execution_time=300'; \
    echo 'date.timezone=Europe/Paris'; \
    } > /usr/local/etc/php/conf.d/custom.ini

# Activation des modules Apache
RUN a2enmod rewrite headers expires deflate ssl

# Configuration Apache pour BatiSaaS
RUN { \
    echo '<VirtualHost *:80>'; \
    echo '    ServerName batisaas.local'; \
    echo '    DocumentRoot /var/www/html/public'; \
    echo '    '; \
    echo '    <Directory /var/www/html/public>'; \
    echo '        Options -Indexes +FollowSymLinks'; \
    echo '        AllowOverride All'; \
    echo '        Require all granted'; \
    echo '    </Directory>'; \
    echo '    '; \
    echo '    ErrorLog ${APACHE_LOG_DIR}/batisaas_error.log'; \
    echo '    CustomLog ${APACHE_LOG_DIR}/batisaas_access.log combined'; \
    echo '</VirtualHost>'; \
    } > /etc/apache2/sites-available/batisaas.conf

# Activation du site et désactivation du site par défaut
RUN a2dissite 000-default.conf && a2ensite batisaas.conf

# Définir le répertoire de travail
WORKDIR /var/www/html

# Copier les fichiers de l'application
COPY --chown=www-data:www-data . /var/www/html/

# Création des dossiers nécessaires avec permissions
RUN mkdir -p \
    /var/www/html/public/uploads/photos \
    /var/www/html/public/uploads/documents \
    /var/www/html/public/uploads/logos \
    /var/www/html/public/uploads/thumbnails \
    /var/www/html/storage/logs \
    /var/www/html/storage/cache \
    /var/www/html/storage/sessions \
    /var/www/html/backups \
    && chown -R www-data:www-data \
    /var/www/html/public/uploads \
    /var/www/html/storage \
    /var/www/html/backups \
    /var/www/html/config \
    && chmod -R 775 \
    /var/www/html/public/uploads \
    /var/www/html/storage \
    /var/www/html/backups \
    /var/www/html/config

# Exposition du port 80
EXPOSE 80

# Healthcheck
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/health || exit 1

# Démarrage d'Apache
CMD ["apache2-foreground"]
