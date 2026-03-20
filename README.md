# Gestion de Invitaciones API

API REST en Laravel 13 para gestionar usuarios, organizaciones y flujo de invitaciones por token.

## Stack

- Laravel 13
- PostgreSQL
- Redis
- Sanctum (autenticacion por token)
- Spatie Permission (roles y permisos)
- Docker Compose (app, nginx, postgres, redis, queue, scheduler)

## Puesta en marcha (Docker)

1. Copiar variables de entorno:

```bash
cp .env.docker .env
```

2. Levantar contenedores:

```bash
docker compose up -d --build
```

3. Ejecutar migraciones y seeders:

```bash
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed
```

4. URL base:

- `http://localhost:8000`

## Mailtrap (envio de invitaciones)

Configura en `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=tu_usuario_mailtrap
MAIL_PASSWORD=tu_password_mailtrap
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@gestiondeinvitaciones.com"
MAIL_FROM_NAME="Gestion de Invitaciones"
```

El endpoint de crear invitacion envia un correo al email invitado con el token.

## Contrato de respuesta API

Todas las respuestas usan:

```json
{
  "success": true,
  "message": "Texto de negocio",
  "data": {},
  "errors": null
}
```

En error:

```json
{
  "success": false,
  "message": "Descripcion del error",
  "data": null,
  "errors": {}
}
```

## Codigos HTTP

- `200` OK
- `201` Creado
- `401` No autenticado
- `403` Sin permisos
- `404` No encontrado
- `409` Conflicto de estado (ej. invitacion ya procesada)
- `422` Error de validacion (FormRequest)

## Roles y permisos

Roles:

- `admin`
- `manager`
- `member`

Permisos definidos:

- `users.create`, `users.show`, `users.update`, `users.delete`
- `organizations.create`, `organizations.show`, `organizations.update`, `organizations.delete`
- `invitations.create`, `invitations.show`, `invitations.update`, `invitations.delete`

Matriz:

- `admin`: todos los permisos
- `manager`: lectura de usuarios, gestion operativa de organizaciones/invitaciones
- `member`: lectura limitada

Seeder admin:

- Email: `admin@gestiondeinvitaciones.com`
- Password: `Admin12345*`

## Endpoints

### Auth

- `POST /api/auth/register`
  - body: `name`, `email`, `password`, `password_confirmation`
- `POST /api/auth/login`
  - body: `email`, `password`
- `POST /api/auth/logout` (Bearer token)

### Organizaciones

- `GET /api/organizations` (auth + `permission:organizations.show`)
- `POST /api/organizations` (auth + `permission:organizations.create`)
  - body: `name`, `description`

### Invitaciones

- `POST /api/organizations/{organization}/invitations` (auth + `permission:invitations.create`)
  - body: `email`, `role` (`manager|member`)
- `GET /api/invitations/{token}`
- `POST /api/invitations/{token}/accept`
  - caso usuario autenticado: usa su `user_id` internamente
  - caso usuario nuevo: enviar `name`, `password`, `password_confirmation`

## Flujo de invitaciones

1. Un usuario autorizado crea invitacion para una organizacion.
2. Se guarda token unico, rol objetivo, email destino y expiracion.
3. Se envia correo con token.
4. El invitado consulta `GET /api/invitations/{token}`.
5. El invitado acepta con `POST /api/invitations/{token}/accept`.
6. Si no existe usuario, se crea automaticamente.
7. Se vincula usuario a organizacion en `user_organization` con el rol invitado.
8. La invitacion cambia a estado `accepted`.

## Comandos utiles

```bash
docker compose exec app php artisan route:list
docker compose exec app php artisan config:clear
docker compose exec app php artisan optimize:clear
docker compose logs -f app
```

## Capturas Mailtrap

Agregar en entrega:

- captura del inbox mostrando email de invitacion
- captura del contenido del correo (token/flujo de aceptacion)

## Uso de IA (documentacion requerida en entrega)

Para cumplir la prueba tecnica, incluir en la entrega:

- en que partes se uso IA
- por que se uso
- que valor aporto
- que validaciones o ajustes manuales se hicieron sobre el resultado
