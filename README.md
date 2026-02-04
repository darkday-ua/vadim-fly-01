# Fly — Plain PHP monorepo

Framework-free PHP app: session-based auth, MySQL, views. **App starts at the login page** (no separate home page).

## Quick start

| How to run | See |
|------------|-----|
| **Locally without Docker** (PHP + MySQL on host) | [docs/RUN-AND-DEPLOY.md#1-run-locally-without-docker](docs/RUN-AND-DEPLOY.md#1-run-locally-without-docker) |
| **Locally with Docker** (containers) | [docs/RUN-AND-DEPLOY.md#2-run-locally-with-docker](docs/RUN-AND-DEPLOY.md#2-run-locally-with-docker) |
| **Build Docker image** (for push/deploy) | [docs/RUN-AND-DEPLOY.md#3-build-the-docker-image](docs/RUN-AND-DEPLOY.md#3-build-the-docker-image) |
| **Deploy on remote server** | [docs/RUN-AND-DEPLOY.md#4-deploy-on-a-remote-server](docs/RUN-AND-DEPLOY.md#4-deploy-on-a-remote-server) |

**TL;DR — local with Docker:**

```bash
cp .env.example .env   # edit: DB_HOST=mysql, DB_*, DEFAULT_*
docker compose up -d
./scripts/docker-migrate.sh
# Open http://localhost:8080
```

**TL;DR — local without Docker:**

```bash
composer install && cp .env.example .env   # edit DB_*, DEFAULT_*
php scripts/migrate.php && php scripts/seed.php
php -S localhost:8080 -t app/public
```

Default user is created by `php scripts/seed.php` from `DEFAULT_USERNAME` / `DEFAULT_PASSWORD` in `.env`. Re-run seed to force-update password.

## Layout

- `app/public/index.php` — entry point
- `app/bootstrap.php` — config, DB, session, router
- `app/routes.php` — route definitions
- `app/src/` — Http, Db, Auth, View
- `app/config/` — env and config
- `app/migrations/` — database migrations
- `scripts/` — migrate.php, seed.php, docker-migrate.sh, docker-setup.sh
- `docs/` — run/deploy, architecture, auth, schema

See [docs/RUN-AND-DEPLOY.md](docs/RUN-AND-DEPLOY.md) for run and deploy details, [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md) for the bird’s-eye view, and [docs/AUTH.md](docs/AUTH.md) for auth and migrations.
