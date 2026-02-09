# How to run and deploy Fly

This guide covers: running locally without Docker, running locally with Docker, building the Docker image, and deploying to a remote server.

---

## 1. Run locally (without Docker)

Use this when you have PHP 8.1+ and MySQL on your machine.

### Prerequisites

- PHP 8.1+ with extensions: `pdo_mysql`, `mbstring`, `json`
- Composer
- MySQL 8 (or MariaDB) with a database and root (or dedicated) user

### Steps

```bash
# Clone or enter the project
cd /path/to/fly

# Install dependencies
composer install

# Environment
cp .env.example .env
# Edit .env: set DB_HOST=127.0.0.1, DB_PORT=3306, DB_NAME=fly,
#            DB_USER=root, DB_PASSWORD=<your_mysql_password>,
#            DEFAULT_USERNAME, DEFAULT_PASSWORD

# Create database (if not exists)
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS fly CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Run migrations
php scripts/migrate.php

# Create default user
php scripts/seed.php

# Start the built-in PHP server (document root = app/public)
php -S localhost:8080 -t app/public
```

Open **http://localhost:8080**. You should see the login page.

### Using Apache or Nginx locally

- **Document root** must be `app/public/`.
- Point the vhost to that directory; ensure `index.php` is the directory index and that requests are rewritten to `index.php` for non-file paths (e.g. `FallbackResource /index.php` for Apache, or try_files + fastcgi to `index.php` for Nginx).

---

## 2. Run locally with Docker

Use this when you want MySQL and the app in containers; no need to install PHP/MySQL on the host.

### Prerequisites

- Docker
- Docker Compose (v2: `docker compose` or v1: `docker-compose`)

### Steps

```bash
cd /path/to/fly

# Environment (required)
cp .env.example .env
# Edit .env and set at least:
#   DB_HOST=mysql
#   DB_NAME=fly
#   DB_USER=root
#   DB_PASSWORD=<password>
#   DB_ROOT_PASSWORD=<same or root password>
#   DEFAULT_USERNAME=admin
#   DEFAULT_PASSWORD=<password>
#   APP_PORT=8080  (optional; default often 8080 or 18080)

# Start stack (builds app image if needed, starts MySQL + app + nginx)
docker compose up -d

# Run migrations and seed (uses .env; requires docker compose)
./scripts/docker-migrate.sh
# Or manually:
# docker compose exec app php scripts/migrate.php
# docker compose exec app php scripts/seed.php
```

Open **http://localhost:8080** (or the port set in `APP_PORT`).

### Useful commands

```bash
# Logs
docker compose logs -f app
docker compose logs -f mysql

# Stop
docker compose down

# Rebuild app image after code changes
docker compose build app
docker compose up -d
```

### Development mode (live code changes)

If you have `docker-compose.dev.yml` that mounts the project into the container:

```bash
docker compose -f docker-compose.yml -f docker-compose.dev.yml up -d
```

---

## 3. Build the Docker image

Use this to build the app image for local runs or for pushing to a registry (e.g. for deployment).

### Build from project root

```bash
cd /path/to/fly

# Build (default: latest tag)
docker build -t fly:latest .

# Or tag for a registry
docker build -t your-registry.com/youruser/fly:latest .
```

The image includes:

- PHP 8.2-FPM and required extensions (pdo_mysql, mbstring, etc.)
- Composer and installed dependencies (`composer install --no-dev`)
- Full app tree under `/var/www/html` (including `app/`, `scripts/`, etc.)

### Push to Docker Hub (or another registry)

```bash
docker login
docker tag fly:latest your-dockerhub-username/fly:latest
docker push your-dockerhub-username/fly:latest
```

Use the same image name and tag in your server `.env` as `APP_IMAGE` (see below).

---

## 4. Deploy on a remote server

Deployment uses the **pre-built image** (from Docker Hub or your registry) and a compose file that does **not** build the app, only runs the image with MySQL and nginx.

### 4.1 One-time server setup

On the server (e.g. Ubuntu):

- Install Docker and Docker Compose (v2 preferred).
- Optionally run the project’s **server setup script** from the repo (if present), which installs Docker and creates a directory (e.g. `/opt/fly`) with a compose file and nginx config.

If you do it manually:

- Create a directory, e.g. `/opt/fly`.
- Put there:
  - A `docker-compose.yml` that uses `image: ${APP_IMAGE}` for the app (no `build:`).
  - An nginx config that proxies to the app container (e.g. `app:9000`) and sets `SCRIPT_FILENAME` to `/var/www/html/app/public/$fastcgi_script_name`.
  - A `.env` file (see below).

### 4.2 Compose file on the server

The server compose file should:

- Use **MySQL** with `MYSQL_DATABASE`, `MYSQL_ROOT_PASSWORD` (and optionally `MYSQL_USER`/`MYSQL_PASSWORD` if you use a non-root DB user).
- Use **app** service with `image: ${APP_IMAGE}` (no build, no volume over `/var/www/html/app` so the image’s code and `app/config/` are used).
- Pass environment variables into the app: `DB_HOST=mysql`, `DB_PORT=3306`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`, `DEFAULT_USERNAME`, `DEFAULT_PASSWORD`.
- Use **nginx** to listen on port 80 (or desired port) and proxy PHP to the app container.

Example app service (conceptual):

```yaml
app:
  image: ${APP_IMAGE}
  environment:
    - DB_HOST=mysql
    - DB_PORT=3306
    - DB_NAME=${DB_NAME}
    - DB_USER=${DB_USER}
    - DB_PASSWORD=${DB_PASSWORD}
    - DEFAULT_USERNAME=${DEFAULT_USERNAME}
    - DEFAULT_PASSWORD=${DEFAULT_PASSWORD}
  depends_on:
    mysql:
      condition: service_healthy
```

### 4.3 Environment on the server

Create `.env` in the deployment directory (e.g. `/opt/fly/.env`) with **at least**:

```env
# Image from Docker Hub (or your registry)
APP_IMAGE=your-dockerhub-username/fly:latest

# Database (MySQL container creates DB; app uses root by default)
DB_NAME=fly
DB_USER=root
DB_PASSWORD=<secure_password>
DB_ROOT_PASSWORD=<secure_password>

# Default admin (for seed script)
DEFAULT_USERNAME=admin
DEFAULT_PASSWORD=<secure_password>

# Port nginx listens on (host)
APP_PORT=80
```

Do **not** mount over `/var/www/html/app` so that `app/config/config.php` and the rest of the app stay from the image.

### 4.4 Deploy and run

```bash
# On the server, in the deployment directory (e.g. /opt/fly)
docker compose pull   # optional; use if you updated the image tag
docker compose up -d

# One-time: migrations and default user
docker compose exec app php scripts/migrate.php
docker compose exec app php scripts/seed.php
```

Open **http://your-server-ip** (or your domain). You should see the login page.

### 4.5 Updating the app on the server

After pushing a new image:

```bash
docker compose pull
docker compose up -d
# If schema changed:
docker compose exec app php scripts/migrate.php
```

---

## 5. HTTPS and Let's Encrypt

The stack (from repo `docker-compose.yml`) runs nginx on **port 80 and 443**. Port 80 redirects to HTTPS; port 443 serves the app with TLS. Certificates come from Let's Encrypt via the **certbot** service.

### 5.1 Prerequisites

- DNS for your domain points to the server (A record).
- In the server `.env`: set `DOMAIN=your-domain.com` and `CERTBOT_EMAIL=your@email.com`.

### 5.2 First-time certificate

1. Start the stack so nginx is up (it will use a self-signed cert until you obtain a real one):

   ```bash
   docker compose up -d
   ```

2. Obtain the certificate (certbot writes into the shared `letsencrypt` volume). From the deploy directory, ensure `DOMAIN` and `CERTBOT_EMAIL` are in `.env`, then run:

   ```bash
   set -a && source .env && set +a
   docker compose run --rm certbot certonly --webroot -w /var/www/certbot -d "$DOMAIN" -m "$CERTBOT_EMAIL" --agree-tos --non-interactive
   ```

3. Reload nginx to use the new certificate:

   ```bash
   docker compose exec nginx nginx -s reload
   ```

After that, **https://your-domain.com** will serve the app with a valid Let's Encrypt certificate.

### 5.3 Renewal

Certificates expire after 90 days. Renew and reload nginx:

```bash
docker compose run --rm certbot renew
docker compose exec nginx nginx -s reload
```

To automate renewal, add a cron job on the server (e.g. daily):

```cron
0 3 * * * cd /opt/fly && docker compose run --rm certbot renew && docker compose exec nginx nginx -s reload
```

### 5.4 Files involved

- **docker/nginx.conf.template** – Nginx config with 80 (ACME challenge + redirect) and 443 (TLS + app). `DOMAIN` is substituted at runtime.
- **docker/nginx-entrypoint.sh** – Creates a self-signed cert if none exists, then starts nginx. Must be executable on the host (`chmod +x docker/nginx-entrypoint.sh`).
- **certbot** service – Uses the `certbot` profile; run manually with `docker compose run --rm certbot ...`.

---

## 6. GitHub Actions CI/CD (deploy on push)

The repo includes a workflow that builds the Docker image, pushes it to Docker Hub, and deploys to your Hetzner VM on every push to `main` (or on manual run).

### 6.1 What the workflow does

1. **Build and push**: Builds the app image, tags it with the git short SHA (e.g. `abc1234`) and `latest`, pushes to Docker Hub (`darkday443/vadim-fly`).
2. **Deploy**: SSHs to your server, runs in the deploy directory:
   - `export APP_IMAGE=darkday443/vadim-fly:<sha>`
   - `docker compose pull && docker compose up -d`
   - `docker compose exec -T app php scripts/migrate.php`

The server’s compose file must use `image: ${APP_IMAGE}` for the app service so the deployed tag is picked up.

### 6.2 Required GitHub secrets

In the repo: **Settings → Secrets and variables → Actions**, add:

| Secret | Description |
|--------|-------------|
| `DOCKERHUB_USERNAME` | Docker Hub username (e.g. `darkday443`) |
| `DOCKERHUB_TOKEN` | Docker Hub access token (Settings → Security → Access tokens) |
| `SSH_HOST` | Hetzner VM hostname or IP |
| `SSH_USER` | SSH user (e.g. `root` or `deploy`) |
| `SSH_PRIVATE_KEY` | Full contents of the private key used to SSH to the VM |

Optional:

| Secret | Description |
|--------|-------------|
| `DEPLOY_PATH` | Directory on the server where compose runs (default: `/opt/fly`) |

### 6.3 Trigger

- **Automatic**: Push to the `main` branch.
- **Manual**: Actions → Build and Deploy → Run workflow.

To deploy from another branch, edit `.github/workflows/deploy.yml` and change the `branches` list under `on.push`.

---

## Quick reference

| Goal                    | Command / doc section        |
|-------------------------|-----------------------------|
| Run locally, no Docker  | Section 1 (PHP + MySQL)     |
| Run locally with Docker | Section 2 (`docker compose`)|
| Build Docker image      | Section 3 (`docker build`)  |
| Deploy on server        | Section 4 (image + compose) |
| HTTPS (Let's Encrypt)   | Section 5 (DOMAIN, certbot, renewal) |
| CI/CD (GitHub → Hetzner)| Section 6 (secrets + push to main)   |

For auth, migrations, and schema details see [AUTH.md](AUTH.md) and [schema.sql](schema.sql).
