-- Initial database schema for Family Money Track
-- MySQL 8+ / MariaDB 10.6+

CREATE DATABASE IF NOT EXISTS family_money_track
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE family_money_track;

CREATE TABLE users (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  email VARCHAR(255) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  display_name VARCHAR(120) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE households (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(120) NOT NULL,
  currency_code CHAR(3) NOT NULL DEFAULT 'HUF',
  owner_user_id BIGINT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (owner_user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE household_members (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  household_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NOT NULL,
  role ENUM('owner','editor','viewer') NOT NULL DEFAULT 'editor',
  joined_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_household_user (household_id, user_id),
  FOREIGN KEY (household_id) REFERENCES households(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE household_invites (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  household_id BIGINT UNSIGNED NOT NULL,
  invite_code CHAR(32) NOT NULL,
  role ENUM('editor','viewer') NOT NULL DEFAULT 'editor',
  expires_at TIMESTAMP NULL,
  created_by_user_id BIGINT UNSIGNED NOT NULL,
  used_by_user_id BIGINT UNSIGNED NULL,
  used_at TIMESTAMP NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_invite_code (invite_code),
  FOREIGN KEY (household_id) REFERENCES households(id) ON DELETE CASCADE,
  FOREIGN KEY (created_by_user_id) REFERENCES users(id),
  FOREIGN KEY (used_by_user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE categories (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  household_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(80) NOT NULL,
  color CHAR(7) NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_archived TINYINT(1) NOT NULL DEFAULT 0,
  UNIQUE KEY uq_household_category (household_id, name),
  FOREIGN KEY (household_id) REFERENCES households(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE receipts (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  household_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NOT NULL,
  image_path VARCHAR(255) NOT NULL,
  image_sha256 CHAR(64) NULL,
  raw_text MEDIUMTEXT NULL,
  parsed_json JSON NULL,
  ocr_provider VARCHAR(40) NULL,
  ocr_confidence DECIMAL(5,2) NULL,
  merchant_name VARCHAR(120) NULL,
  receipt_date DATE NULL,
  total_amount DECIMAL(12,2) NULL,
  status ENUM('pending','processed','failed') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_receipt_hash (household_id, image_sha256),
  KEY idx_receipt_household_date (household_id, receipt_date),
  FOREIGN KEY (household_id) REFERENCES households(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE receipt_items (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  receipt_id BIGINT UNSIGNED NOT NULL,
  line_no INT NOT NULL,
  item_name VARCHAR(160) NOT NULL,
  quantity DECIMAL(10,2) NULL,
  unit_price DECIMAL(12,2) NULL,
  total_price DECIMAL(12,2) NOT NULL,
  suggested_category_id BIGINT UNSIGNED NULL,
  confidence DECIMAL(5,2) NULL,
  KEY idx_receipt_line (receipt_id, line_no),
  FOREIGN KEY (receipt_id) REFERENCES receipts(id) ON DELETE CASCADE,
  FOREIGN KEY (suggested_category_id) REFERENCES categories(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE expenses (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  household_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NOT NULL,
  category_id BIGINT UNSIGNED NOT NULL,
  receipt_id BIGINT UNSIGNED NULL,
  amount DECIMAL(12,2) NOT NULL,
  spent_at DATE NOT NULL,
  description VARCHAR(255) NULL,
  source ENUM('manual','receipt') NOT NULL DEFAULT 'manual',
  client_uuid CHAR(36) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  deleted_at TIMESTAMP NULL,
  UNIQUE KEY uq_household_client (household_id, client_uuid),
  KEY idx_expense_household_date (household_id, spent_at),
  KEY idx_expense_household_category (household_id, category_id),
  FOREIGN KEY (household_id) REFERENCES households(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (category_id) REFERENCES categories(id),
  FOREIGN KEY (receipt_id) REFERENCES receipts(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
