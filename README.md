# Basic Backend

Backend PHP básico con Composer y Docker.

## Requisitos

- Git
- Docker Desktop

## Clonar e instalar

```bash
git clone <url-del-repo> basic-backend
cd basic-backend
cp .env.example .env
```

## Construir

```bash
docker compose up -d --build
```

Construye la imagen desde el `Dockerfile` incrustado en `docker-compose.yml` (`build.dockerfile_inline`: instala dependencias con Composer y el driver `pdo_pgsql` en la etapa de build) y levanta el contenedor.

## Levantar

```bash
docker compose up -d
```

La app queda en http://localhost:8000.

## Base de datos

El servicio `db` levanta PostgreSQL 17 con un volumen (`db_data`) para que los datos persistan. La app arranca solo cuando la DB está sana (`depends_on` + healthcheck).

Conexión de prueba: http://localhost:8000/api/db.php

La conexión se configura con la variable `DATABASE_URL` del `.env`:

```
DATABASE_URL=postgres://usuario:password@host:puerto/base
```

Valor por defecto: `postgres://basic_backend:secret@db:5432/basic_backend` (el host `db` es el nombre del servicio dentro de Docker).

Si tienes un cliente de PostgreSQL local, puedes conectarte a `localhost:5432` con `basic_backend`/`secret`.

## Endpoints

| Método    | Ruta             | Acción                                                                                         |
| --------- | ---------------- | ---------------------------------------------------------------------------------------------- |
| GET       | `/users`         | Listar usuarios                                                                                |
| GET       | `/users/{id}`    | Obtener un usuario                                                                             |
| POST      | `/users`         | Crear usuario (`{name, email}`)                                                                |
| PUT/PATCH | `/users/{id}`    | Actualizar usuario (envía solo los campos a cambiar: `{name}`, `{email}` o ambos)              |
| DELETE    | `/users/{id}`    | Eliminar usuario                                                                               |
| GET       | `/products`      | Listar productos                                                                               |
| GET       | `/products/{id}` | Obtener un producto                                                                            |
| POST      | `/products`      | Crear producto (`{name, price, stock}`)                                                        |
| PUT/PATCH | `/products/{id}` | Actualizar producto (envía solo los campos a cambiar: `{name}`, `{price}`, `{stock}` o varios) |
| DELETE    | `/products/{id}` | Eliminar producto                                                                              |

`GET /users` y `GET /products` aceptan paginación por query params: `?page=2&per_page=10` (por defecto `page=1`, `per_page=10`, máx. 100).

## Seguridad

Todos los requests pasan por el front controller (`src/index.php` como router del servidor PHP embebido). Solo se responden las rutas definidas; cualquier intento de acceder a archivos internos (`/Logger.php`, `/db/Database.php`, `/logs/app.log`, traversal con `..`, extensiones como `.php`/`.log`) recibe `404`.

## Logs

Cada request queda registrado en `src/logs/app.log` con nivel (`SUCCESS`, `WARN`, `ERROR`), método, ruta, código de estado y mensaje. Los intentos de acceso bloqueados se registran como `Ruta bloqueada`; los errores no capturados se registran con su stack trace completo. El archivo de logs es privado: no se sirve vía HTTP.

## Bajar

```bash
docker compose down
```

Los datos de la DB se conservan en el volumen; para borrarlos: `docker compose down -v`.
