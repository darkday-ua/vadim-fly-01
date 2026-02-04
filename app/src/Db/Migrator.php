<?php

declare(strict_types=1);

namespace App\Db;

class Migrator
{
    public function __construct(
        private Connection $db,
        private string $migrationsPath,
    ) {
    }

    public function ensureMigrationsTable(): void
    {
        $this->db->execute("
            CREATE TABLE IF NOT EXISTS migrations (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL UNIQUE,
                executed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function getExecutedMigrations(): array
    {
        $rows = $this->db->fetchAll('SELECT name FROM migrations ORDER BY executed_at');
        return array_column($rows, 'name');
    }

    public function markExecuted(string $name): void
    {
        $this->db->execute('INSERT INTO migrations (name) VALUES (?)', [$name]);
    }

    public function markRolledBack(string $name): void
    {
        $this->db->execute('DELETE FROM migrations WHERE name = ?', [$name]);
    }

    public function run(): void
    {
        $this->ensureMigrationsTable();
        $executed = $this->getExecutedMigrations();
        $files = glob($this->migrationsPath . '/*.php');
        sort($files);

        foreach ($files as $file) {
            $name = basename($file, '.php');
            if (in_array($name, $executed, true)) {
                continue;
            }

            require_once $file;
            $className = $this->getMigrationClassName($name);
            if (!class_exists($className)) {
                throw new \RuntimeException("Migration class not found: $className");
            }

            $migration = new $className();
            echo "Running migration: {$migration->getName()}\n";
            $migration->up($this->db);
            $this->markExecuted($name);
        }
    }

    private function getMigrationClassName(string $name): string
    {
        // Strip numeric prefix (e.g., "001_" from "001_create_users_table")
        $name = preg_replace('/^\d+_/', '', $name);
        
        $parts = explode('_', $name);
        $parts = array_map('ucfirst', $parts);
        return 'App\\Db\\Migrations\\' . implode('', $parts);
    }
}
