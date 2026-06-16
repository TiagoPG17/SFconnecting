# Stage 1: compilar assets con Node
FROM node:20-alpine AS assets
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

# Stage 2: imagen PHP final
FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    curl unzip git libpng-dev libonig-dev libxml2-dev zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Microsoft ODBC Driver 18 (SQL Server Contiflex)
RUN curl -sSL https://packages.microsoft.com/keys/microsoft.asc \
        -o /usr/share/keyrings/microsoft-prod.asc \
    && echo "deb [arch=amd64 signed-by=/usr/share/keyrings/microsoft-prod.asc] https://packages.microsoft.com/debian/12/prod bookworm main" \
        > /etc/apt/sources.list.d/mssql-release.list \
    && apt-get update \
    && ACCEPT_EULA=Y apt-get install -y msodbcsql18 unixodbc-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd

# Permitir certificados SSL débiles del SQL Server (ODBC Driver 18 + OpenSSL)
RUN echo '\n[openssl_init]\nssl_conf = ssl_sect\n[ssl_sect]\nsystem_default = system_default_sect\n[system_default_sect]\nCipherString = DEFAULT@SECLEVEL=0\nMinProtocol = TLSv1' >> /etc/ssl/openssl.cnf
RUN pecl install sqlsrv-5.12.0 pdo_sqlsrv-5.12.0 && docker-php-ext-enable sqlsrv pdo_sqlsrv

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/sfconnecting

# Composer primero para aprovechar caché de capas
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader

# Código fuente y assets compilados
COPY . .
COPY --from=assets /app/public/build ./public/build

RUN chown -R www-data:www-data /var/www/sfconnecting \
    && chmod -R 755 storage bootstrap/cache

COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 9000
ENTRYPOINT ["/entrypoint.sh"]
CMD ["php-fpm"]
