-- Car Raffle System Database Schema
-- Run this SQL to set up the database

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Admins table
CREATE TABLE IF NOT EXISTS `admins` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100),
    `is_active` TINYINT(1) DEFAULT 1,
    `last_login` DATETIME,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Promoters table
CREATE TABLE IF NOT EXISTS `promoters` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(20),
    `is_active` TINYINT(1) DEFAULT 1,
    `last_login` DATETIME,
    `created_by` INT UNSIGNED,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_username` (`username`),
    FOREIGN KEY (`created_by`) REFERENCES `admins`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Malls table
CREATE TABLE IF NOT EXISTS `malls` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `name_en` VARCHAR(100),
    `address` TEXT,
    `latitude` DECIMAL(10, 8) NOT NULL,
    `longitude` DECIMAL(11, 8) NOT NULL,
    `radius` INT UNSIGNED DEFAULT 500,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_location` (`latitude`, `longitude`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Customer entries table
CREATE TABLE IF NOT EXISTS `entries` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `customer_name` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(20) NOT NULL,
    `mall_id` INT UNSIGNED NOT NULL,
    `entry_type` ENUM('customer', 'promoter') NOT NULL DEFAULT 'customer',
    `promoter_id` INT UNSIGNED NULL,
    `invoice_number` INT UNSIGNED DEFAULT 1,
    `total_invoices` INT UNSIGNED DEFAULT 1,
    `ip_address` VARCHAR(45),
    `user_agent` TEXT,
    `latitude` DECIMAL(10, 8),
    `longitude` DECIMAL(11, 8),
    `is_winner` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_phone` (`phone`),
    INDEX `idx_mall` (`mall_id`),
    INDEX `idx_promoter` (`promoter_id`),
    INDEX `idx_entry_type` (`entry_type`),
    INDEX `idx_created` (`created_at`),
    FOREIGN KEY (`mall_id`) REFERENCES `malls`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`promoter_id`) REFERENCES `promoters`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Login attempts for security
CREATE TABLE IF NOT EXISTS `login_attempts` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `user_type` ENUM('admin', 'promoter') NOT NULL,
    `success` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_ip_time` (`ip_address`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Activity logs
CREATE TABLE IF NOT EXISTS `activity_logs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED,
    `user_type` ENUM('admin', 'promoter') NOT NULL,
    `action` VARCHAR(100) NOT NULL,
    `details` TEXT,
    `ip_address` VARCHAR(45),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_user` (`user_id`, `user_type`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- Note: Use setup.php to create admin account with proper password hash
