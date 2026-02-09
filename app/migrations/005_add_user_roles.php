<?php

declare(strict_types=1);

namespace App\Db\Migrations;

use App\Db\Connection;
use App\Db\Migration;

class AddUserRoles extends Migration
{
    public function getName(): string
    {
        return 'Add role column (admin, user)';
    }

    public function up(Connection $db): void
    {
        $rows = $db->fetchAll("
            SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'role'
        ");
        if (empty($rows)) {
            $db->execute("ALTER TABLE users ADD COLUMN role VARCHAR(50) NOT NULL DEFAULT 'user' AFTER username");
            $db->execute("UPDATE users SET role = 'admin' WHERE username = 'admin' LIMIT 1");
        }
    }

    public function down(Connection $db): void
    {
        $db->execute('ALTER TABLE users DROP COLUMN IF EXISTS role');
    }
}
