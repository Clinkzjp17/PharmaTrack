-- setup.sql
-- Run this once in phpMyAdmin or via MySQL CLI:
--   mysql -u root -p < setup.sql

CREATE DATABASE IF NOT EXISTS pharmatrack;
USE pharmatrack;

CREATE TABLE IF NOT EXISTS users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(50)  NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    role       ENUM('admin','user') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Optional: seed a default admin so you can log in immediately
-- Password is: admin123
INSERT IGNORE INTO users (username, password, role)
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
