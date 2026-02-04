# Local MySQL setup for Fly

The app expects these credentials (set in `.env`):

| Variable     | Example   | Description        |
|-------------|-----------|--------------------|
| `DB_HOST`   | 127.0.0.1 | MySQL host         |
| `DB_PORT`   | 3306      | MySQL port (default 3306) |
| `DB_NAME`   | fly       | Database name      |
| `DB_USER`   | fly       | MySQL username     |
| `DB_PASSWORD` | secret   | MySQL password     |

## 1. Create database and user (as MySQL root)

Connect as root (or another admin user):

```bash
mysql -u root -p
```

Then run:

```sql
-- Create database
CREATE DATABASE IF NOT EXISTS fly CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user and grant access (use your chosen password)
CREATE USER IF NOT EXISTS 'fly'@'localhost' IDENTIFIED BY 'secret';
GRANT ALL PRIVILEGES ON fly.* TO 'fly'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

To use a different password, change `'secret'` in the `CREATE USER` line and set the same value in your `.env` as `DB_PASSWORD`.

## 2. Run migrations

```bash
php scripts/migrate.php
```

This creates the `users` table and a `migrations` tracking table.

## 3. Seed default user (optional)

Create the default user using credentials from `.env`:

```bash
php scripts/seed.php
```

This uses `DEFAULT_USERNAME` and `DEFAULT_PASSWORD` from your `.env` file (defaults to `admin` / `admin`).

## 4. Check your `.env`

Ensure `.env` matches your MySQL setup, for example:

```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=fly
DB_USER=fly
DB_PASSWORD=secret

# Default user credentials (used by seed script)
DEFAULT_USERNAME=admin
DEFAULT_PASSWORD=admin
```

If MySQL runs on a non-default port (e.g. Docker on 13306), set `DB_PORT=13306`.

## 5. Test the connection

From the project root:

```bash
php -r "
require 'vendor/autoload.php';
\$config = require 'app/config/config.php';
\$pdo = App\Db\Connection::fromConfig(\$config['db']);
echo 'OK: connected to ' . \$config['db']['name'] . PHP_EOL;
"
```

If you see `OK: connected to fly`, the app can use this MySQL setup.
