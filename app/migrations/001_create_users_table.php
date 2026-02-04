<?php

declare(strict_types=1);

namespace App\Db\Migrations;

use App\Db\Connection;
use App\Db\Migration;

class CreateUsersTable extends Migration
{
    public function getName(): string
    {
        return 'Create users table';
    }

    public function up(Connection $db): void
    {
        $db->execute("
            CREATE TABLE IF NOT EXISTS users (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(191) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                last_login_at DATETIME NULL,
                click_counter INT NOT NULL DEFAULT 0,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(Connection $db): void
    {
        $db->execute('DROP TABLE IF EXISTS users');
    }
}
