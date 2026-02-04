<?php

declare(strict_types=1);

namespace App\Db;

abstract class Migration
{
    abstract public function up(Connection $db): void;
    abstract public function down(Connection $db): void;
    abstract public function getName(): string;
}
