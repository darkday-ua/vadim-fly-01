# Migrations

Database migrations for the Fly app.

## Running Migrations

From the project root:

```bash
php scripts/migrate.php
```

This will:
1. Create a `migrations` table if it doesn't exist (tracks which migrations have run)
2. Find all `.php` files in `app/migrations/`
3. Run any migrations that haven't been executed yet (in alphabetical order)

## Creating a New Migration

1. Create a file: `app/migrations/XXX_description.php` (e.g. `003_add_email_to_users.php`)
2. Follow this structure:

```php
<?php

declare(strict_types=1);

namespace App\Db\Migrations;

use App\Db\Connection;
use App\Db\Migration;

class AddEmailToUsers extends Migration
{
    public function getName(): string
    {
        return 'Add email column to users';
    }

    public function up(Connection $db): void
    {
        $db->execute('ALTER TABLE users ADD COLUMN email VARCHAR(255) NULL');
    }

    public function down(Connection $db): void
    {
        $db->execute('ALTER TABLE users DROP COLUMN email');
    }
}
```

3. Run migrations: `php scripts/migrate.php`

## Migration Naming

- File: `001_create_users_table.php` (numeric prefix is optional; used for order)
- Class: `CreateUsersTable` (auto-generated from filename)
- Namespace: `App\Db\Migrations\CreateUsersTable`

The class name is derived from the filename by:
- Stripping a leading numeric prefix (e.g. `001_`)
- Removing `.php`, splitting on `_`, capitalizing each part, joining (e.g. `create_users_table` → `CreateUsersTable`)
