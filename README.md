# Gestion de Invitaciones - Guia de configuracion y Docker

Proyecto Laravel 13 con PostgreSQL, Redis, Nginx, workers de cola y scheduler ejecutados con Docker Compose.

## Requisitos

- Docker Desktop (con Docker Compose v2)
- Git

## 1) Configuracion inicial del proyecto

Desde la raiz del proyecto:

```bash
cp .env.example .env
```

Edita `.env` para que use los servicios de Docker (valores recomendados):

```env
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=gestiondeinvitaciones
DB_USERNAME=laravel
DB_PASSWORD=secret

REDIS_HOST=redis
REDIS_PORT=6379
REDIS_PASSWORD=null
REDIS_CLIENT=phpredis

QUEUE_CONNECTION=database
```

Notas:

- El contenedor `app` genera `APP_KEY` automaticamente si esta vacia.
- El contenedor `app` ejecuta migraciones al iniciar (`RUN_MIGRATIONS=true`).
- Se crea automaticamente una base de datos de pruebas: `gestiondeinvitaciones_test`.

## 2) Levantar el proyecto con Docker

Construir y arrancar todo:

```bash
docker compose up -d --build
```

Ver contenedores:

```bash
docker compose ps
```

Aplicacion disponible en:

- `http://localhost:8000`

Servicios incluidos:

- `nginx` (puerto `8000` por defecto)
- `app` (PHP-FPM Laravel)
- `postgres` (host `postgres`, puerto interno `5432`, externo `54322`)
- `redis` (puerto `6379`)
- `queue` (worker de colas)
- `scheduler` (tareas programadas)

## 3) Comandos utiles dentro de Docker

Abrir shell en app:

```bash
docker compose exec app sh
```

Ejecutar Artisan:

```bash
docker compose exec app php artisan list
```

Forzar migraciones:

```bash
docker compose exec app php artisan migrate --force
```

Ver logs en vivo:

```bash
docker compose logs -f app nginx postgres redis queue scheduler
```

## 4) Ejecutar pruebas

Este proyecto esta preparado para correr tests contra PostgreSQL en la BD `gestiondeinvitaciones_test`.

```bash
docker compose exec app php artisan test
```

## 5) Detener y limpiar

Detener contenedores:

```bash
docker compose down
```

Detener y eliminar volumenes (reinicio limpio de BD/cache/vendor):

```bash
docker compose down -v
```

## 6) Solucion rapida de problemas

- Si cambia `composer.json` y faltan dependencias:
  - `docker compose exec app composer install --no-interaction --prefer-dist`
- Si hay problemas de cache Laravel:
  - `docker compose exec app php artisan optimize:clear`
- Si no levanta por variables incorrectas:
  - revisa `.env` y confirma `DB_HOST=postgres` y `REDIS_HOST=redis`.
