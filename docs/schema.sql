-- MySQL schema for Fly app (run once to create DB and tables)

CREATE DATABASE IF NOT EXISTS fly CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE fly;

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(191) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    last_login_at DATETIME NULL,
    click_counter INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default user is created via seed script: php scripts/seed.php
-- This uses DEFAULT_USERNAME and DEFAULT_PASSWORD from .env
-- Example (hardcoded for reference only - use seed.php instead):
-- INSERT INTO users (username, password_hash) VALUES
-- ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
