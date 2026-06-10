# Dockerización SFconnecting — Guía de Despliegue

## Arquitectura final

```
172.20.1.7/          → demgest (sin cambios)
172.20.1.7/crm       → SFconnecting (nuevo)
172.20.1.7/crm/login → login SFconnecting
```

Un solo nginx en el servidor maneja todo el puerto 80. El contenedor PHP-FPM de SFconnecting corre en el mismo `docker-compose.yml` de demgest.

---

## Paso 1 — Crear archivos Docker en SFconnecting (local)

### `Dockerfile`

```dockerfile
FROM php:8.2-fpm

# Dependencias del sistema
RUN apt-get update && apt-get install -y \
    curl unzip git libpng-dev libonig-dev libxml2-dev zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Microsoft ODBC Driver 18 (para SQL Server Contiflex)
RUN curl -sSL https://packages.microsoft.com/keys/microsoft.asc | apt-key add - \
    && curl -sSL https://packages.microsoft.com/config/debian/11/prod.list \
       > /etc/apt/sources.list.d/mssql-release.list \
    && apt-get update \
    && ACCEPT_EULA=Y apt-get install -y msodbcsql18 unixodbc-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Extensiones PHP
RUN docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd
RUN pecl install sqlsrv pdo_sqlsrv && docker-php-ext-enable sqlsrv pdo_sqlsrv

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/sfconnecting

COPY . .

RUN composer install --no-dev --optimize-autoloader \
    && chown -R www-data:www-data /var/www/sfconnecting \
    && chmod -R 755 storage bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]
```

### `.env.docker` (template — copiar a `.env` en el servidor)

```env
APP_NAME=SFconnecting
APP_ENV=production
APP_KEY=                        # generar con: php artisan key:generate
APP_DEBUG=false
APP_URL=http://172.20.1.7/crm

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=mysql                   # nombre del servicio en docker-compose
DB_PORT=3306
DB_DATABASE=sfconnecting
DB_USERNAME=root
DB_PASSWORD=                    # misma del mysql de demgest

ERP_CONTIFLEX_HOST=172.20.3.1
ERP_CONTIFLEX_PORT=1433
ERP_CONTIFLEX_DATABASE=Contiflex
ERP_CONTIFLEX_USERNAME=sa
ERP_CONTIFLEX_PASSWORD=          # credencial del ERP — nunca commitear

SESSION_DRIVER=file
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
```

---

## Paso 2 — Modificar docker-compose.yml de demgest en el servidor

Agregar el servicio `sfconnecting` al `docker-compose.yml` existente:

```yaml
  sfconnecting:
    build:
      context: /srv/sfconnecting        # directorio donde subirás el proyecto
    container_name: sfconnecting-app
    restart: unless-stopped
    working_dir: /var/www/sfconnecting
    volumes:
      - /srv/sfconnecting:/var/www/sfconnecting
      - sfconnecting_storage:/var/www/sfconnecting/storage
    networks:
      - app-network                     # misma red del nginx y mysql de demgest
    depends_on:
      - mysql
```

Y al final del archivo, en `volumes:`:

```yaml
volumes:
  sfconnecting_storage:
```

---

## Paso 3 — Agregar location /crm al nginx de demgest

Dentro del bloque `server` que ya existe en el nginx de demgest, agregar:

```nginx
# SFconnecting
location /crm {
    alias /var/www/sfconnecting/public;
    try_files $uri $uri/ @sfconnecting;

    location ~ \.php$ {
        fastcgi_pass sfconnecting-app:9000;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME /var/www/sfconnecting/public$fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_path_info;
    }
}

location @sfconnecting {
    fastcgi_pass sfconnecting-app:9000;
    fastcgi_index index.php;
    include fastcgi_params;
    fastcgi_param SCRIPT_FILENAME /var/www/sfconnecting/public/index.php;
    fastcgi_param PATH_INFO $uri;
}
```

---

## Paso 4 — Subir el proyecto al servidor

```bash
# En el servidor (slv-aplicaciones)
mkdir -p /srv/sfconnecting

# Desde tu máquina local — copiar archivos (excluye node_modules, vendor, .env)
scp -r . usuario@172.20.1.7:/srv/sfconnecting

# O clonar desde git si está en un repositorio
git clone <repo-url> /srv/sfconnecting
```

---

## Paso 5 — Configurar y levantar

```bash
# En el servidor, dentro de /srv/sfconnecting
cp .env.docker .env
# Editar .env: poner DB_PASSWORD y APP_KEY

# Generar APP_KEY
docker run --rm -v /srv/sfconnecting:/app -w /app php:8.2-cli php artisan key:generate

# Levantar el nuevo contenedor (sin bajar demgest)
cd /ruta/docker-compose-demgest
docker compose up -d sfconnecting

# Recargar nginx (sin reiniciar)
docker exec <nombre-contenedor-nginx> nginx -s reload

# Crear base de datos y correr migraciones
docker exec -it sfconnecting-app php artisan migrate --force
docker exec -it sfconnecting-app php artisan db:seed --class=RolesPermisosSeeder
```

---

## Verificación

```bash
# Ver logs del contenedor
docker logs sfconnecting-app -f

# Probar que responde
curl -I http://172.20.1.7/crm/login
# Esperado: HTTP/1.1 200 OK
```

---

## Resumen de lo que se toca

| Qué | Acción | Riesgo para demgest |
|-----|--------|---------------------|
| `docker-compose.yml` de demgest | Agregar servicio `sfconnecting` | Ninguno |
| Nginx de demgest | Agregar `location /crm` | Ninguno — demgest sigue en `/` |
| MySQL de demgest | Crear nueva DB `sfconnecting` | Ninguno |
| `/srv/sfconnecting` | Directorio nuevo | Ninguno |
