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

Construye la imagen desde el `Dockerfile` incrustado en `docker-compose.yml` (`build.dockerfile_inline`: instala dependencias con Composer en la etapa de build) y levanta el contenedor.

## Levantar

```bash
docker compose up -d
```

La app queda en http://localhost:8000.

## Bajar

```bash
docker compose down
```
