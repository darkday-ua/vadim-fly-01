<?php

declare(strict_types=1);

namespace App\Db\Migrations;

use App\Db\Connection;
use App\Db\Migration;

class AddUserLockAndMute extends Migration
{
    public function getName(): string
    {
        return 'Add is_locked and is_muted to users';
    }

    public function up(Connection $db): void
    {
        foreach (['is_locked', 'is_muted'] as $col) {
            $rows = $db->fetchAll("
                SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = ?
            ", [$col]);
            if (empty($rows)) {
                $db->execute("ALTER TABLE users ADD COLUMN {$col} TINYINT(1) NOT NULL DEFAULT 0 AFTER click_counter");
            }
        }
    }

    public function down(Connection $db): void
    {
        $db->execute('ALTER TABLE users DROP COLUMN IF EXISTS is_locked');
        $db->execute('ALTER TABLE users DROP COLUMN IF EXISTS is_muted');
    }
}
