-- Hinlo Airsoft Zone — Database Schema (MySQL / MariaDB)
-- Compatible with XAMPP's bundled MariaDB out of the box.
--
-- Import via phpMyAdmin: open http://localhost/phpmyadmin,
-- click "Import", choose this file, click "Go".
--
-- Or via command line (XAMPP's mysql binary):
--   Windows: C:\xampp\mysql\bin\mysql.exe -u root -p < schema.sql
--   macOS:   /Applications/XAMPP/xamppfiles/bin/mysql -u root -p < schema.sql
--   Linux:   /opt/lampp/bin/mysql -u root -p < schema.sql
-- (XAMPP's default root user has no password — just press Enter.)

CREATE DATABASE IF NOT EXISTS hinlo_airsoft
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE hinlo_airsoft;

-- ------------------------------------------------------------
-- Community registrations (Join Us form)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS registrations (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name   VARCHAR(100) NOT NULL,
    email       VARCHAR(150) NOT NULL,
    phone       VARCHAR(30)  DEFAULT NULL,
    status      ENUM('pending', 'confirmed') NOT NULL DEFAULT 'pending',
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_registrations_email (email)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Contact messages (Contacts form)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS contact_messages (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    email       VARCHAR(150) NOT NULL,
    message     TEXT NOT NULL,
    is_read     TINYINT(1) NOT NULL DEFAULT 0,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Reviews (home page review form + featured reviews)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS reviews (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reviewer_name  VARCHAR(100) NOT NULL,
    rating         TINYINT UNSIGNED NOT NULL DEFAULT 5,
    review_text    TEXT NOT NULL,
    is_published   TINYINT(1) NOT NULL DEFAULT 0,
    created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT chk_rating CHECK (rating BETWEEN 1 AND 5)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Admin users (for the /admin CRUD panel)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS admin_users (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username       VARCHAR(50) NOT NULL,
    password_hash  VARCHAR(255) NOT NULL,
    created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_admin_username (username)
) ENGINE=InnoDB;

-- No admin user is seeded here on purpose (a hardcoded password hash
-- in source control is a bad habit). After importing this schema,
-- visit /admin/setup.php once in your browser to create the first
-- admin account — that script locks itself once an account exists.
