# Fly — Plain PHP monorepo

Framework-free PHP app: views, session-based auth, MySQL.

## Setup

### Option 1: Docker (Recommended)

```bash
cp .env.example .env
# Edit .env: set DB_HOST=mysql for Docker

docker-compose up -d
docker-compose exec app php scripts/migrate.php
docker-compose exec app php scripts/seed.php
```

Open http://localhost:8080

See [docs/DOCKER.md](docs/DOCKER.md) for detailed Docker instructions.

### Option 2: Local PHP

```bash
composer install
cp .env.example .env
# Edit .env with your DB credentials and default user (DEFAULT_USERNAME, DEFAULT_PASSWORD)

# Run migrations
php scripts/migrate.php

# Seed default user (optional, uses DEFAULT_USERNAME/DEFAULT_PASSWORD from .env)
php scripts/seed.php
```

Document root: **`app/public/`**. Point your server (Apache/Nginx/PHP built-in) at it.

#### PHP built-in server

```bash
php -S localhost:8080 -t app/public
```

Then open http://localhost:8080

### Default login

The default user is created via `php scripts/seed.php` using:
- **Username:** From `DEFAULT_USERNAME` in `.env` (default: `admin`)
- **Password:** From `DEFAULT_PASSWORD` in `.env` (default: `admin`)

⚠️ Change these credentials in `.env` for production!

## Layout

- `app/public/index.php` — entry point
- `app/bootstrap.php` — config, DB, session, router
- `app/routes.php` — route definitions
- `app/src/` — Http, Db, Auth, View
- `app/config/` — env and config
- `docs/` — architecture, schema

See [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md) for the bird’s-eye view.
