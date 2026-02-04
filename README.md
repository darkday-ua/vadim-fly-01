# Fly — Plain PHP monorepo

Framework-free PHP app: views, session-based auth, MySQL.

## Setup

```bash
composer install
cp .env.example .env
# Edit .env with your DB credentials
mysql -u root -p < docs/schema.sql
```

Document root: **`app/public/`**. Point your server (Apache/Nginx/PHP built-in) at it.

### PHP built-in server

```bash
php -S localhost:8080 -t app/public
```

Then open http://localhost:8080

### Default login (from schema)

- **Username:** `admin`
- **Password:** `admin`  
Change or remove this user in production.

## Layout

- `app/public/index.php` — entry point
- `app/bootstrap.php` — config, DB, session, router
- `app/routes.php` — route definitions
- `app/src/` — Http, Db, Auth, View
- `app/config/` — env and config
- `docs/` — architecture, schema

See [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md) for the bird’s-eye view.
