<?php

declare(strict_types=1);

namespace App\Db\Migrations;

use App\Db\Connection;
use App\Db\Migration;

class AddLastLoginToExistingUsers extends Migration
{
    public function getName(): string
    {
        return 'Add last_login_at column to existing users table';
    }

    public function up(Connection $db): void
    {
        // Check if column already exists
        $columns = $db->fetchAll("
            SELECT COLUMN_NAME 
            FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'users' 
            AND COLUMN_NAME = 'last_login_at'
        ");

        if (empty($columns)) {
            $db->execute("
                ALTER TABLE users 
                ADD COLUMN last_login_at DATETIME NULL AFTER password_hash
            ");
        }
    }

    public function down(Connection $db): void
    {
        $db->execute('ALTER TABLE users DROP COLUMN IF EXISTS last_login_at');
    }
}
