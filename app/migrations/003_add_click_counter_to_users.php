<?php

declare(strict_types=1);

namespace App\Db\Migrations;

use App\Db\Connection;
use App\Db\Migration;

class AddClickCounterToUsers extends Migration
{
    public function getName(): string
    {
        return 'Add click_counter field to users table';
    }

    public function up(Connection $db): void
    {
        // Check if column already exists
        $columns = $db->fetchAll("
            SELECT COLUMN_NAME 
            FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'users' 
            AND COLUMN_NAME = 'click_counter'
        ");

        if (empty($columns)) {
            $db->execute("
                ALTER TABLE users 
                ADD COLUMN click_counter INT NOT NULL DEFAULT 0 AFTER last_login_at
            ");
        }
    }

    public function down(Connection $db): void
    {
        $db->execute('ALTER TABLE users DROP COLUMN IF EXISTS click_counter');
    }
}
