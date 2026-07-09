-- Schema for VKU Pet Shop
-- Import this file into MySQL/phpMyAdmin

CREATE DATABASE IF NOT EXISTS vku_pet_shop
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE vku_pet_shop;

-- Users table for login/registration
CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(191) NOT NULL,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Products table for shop items
CREATE TABLE IF NOT EXISTS products (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(191) NOT NULL,
  brand VARCHAR(100) DEFAULT NULL,
  description TEXT DEFAULT NULL,
  image VARCHAR(255) DEFAULT NULL,
  price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  old_price DECIMAL(10,2) DEFAULT NULL,
  category VARCHAR(100) DEFAULT NULL,
  stock INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Cart table for logged-in users
CREATE TABLE IF NOT EXISTS carts (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_carts_user_id (user_id),
  CONSTRAINT fk_carts_user_id
    FOREIGN KEY (user_id) REFERENCES users (id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Cart items table
CREATE TABLE IF NOT EXISTS cart_items (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  cart_id INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NOT NULL,
  quantity INT NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_cart_product (cart_id, product_id),
  CONSTRAINT fk_cart_items_cart_id
    FOREIGN KEY (cart_id) REFERENCES carts (id)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT fk_cart_items_product_id
    FOREIGN KEY (product_id) REFERENCES products (id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;