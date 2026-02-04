-- ------------------------------------------------------------
-- Web Shop with Buying & Bidding - Database Schema (MySQL/MariaDB)
-- ------------------------------------------------------------

SET NAMES utf8mb4;
SET time_zone = '+00:00';

-- Drop in dependency order (safe for re-run during development)
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS bids;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS users;

-- ------------------------------------------------------------
-- Users (Customer / Admin)
-- ------------------------------------------------------------
CREATE TABLE users (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name            VARCHAR(100) NOT NULL,
  email           VARCHAR(190) NOT NULL,
  password_hash   VARCHAR(255) NOT NULL,
  role            ENUM('customer','admin') NOT NULL DEFAULT 'customer',
  created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Products (Buy-now or Auction)
-- ------------------------------------------------------------
CREATE TABLE products (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  title           VARCHAR(160) NOT NULL,
  description     TEXT NULL,
  image_url       VARCHAR(255) NULL,

  type            ENUM('buy_now','auction') NOT NULL,

  -- For buy-now products
  price           DECIMAL(10,2) NULL,

  -- For auction products
  starting_bid    DECIMAL(10,2) NULL,
  auction_end_at  DATETIME NULL,

  -- Generic inventory flag (optional, but useful)
  is_active       TINYINT(1) NOT NULL DEFAULT 1,

  created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (id),

  -- Helpful indexes
  KEY idx_products_type (type),
  KEY idx_products_active (is_active),
  KEY idx_products_auction_end (auction_end_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Note:
-- Enforcing "price required when type=buy_now" and
-- "starting_bid + auction_end_at required when type=auction"
-- is best done in API validation for MySQL/MariaDB compatibility.

-- ------------------------------------------------------------
-- Bids (only valid for auction products - enforced by API logic)
-- ------------------------------------------------------------
CREATE TABLE bids (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  product_id      INT UNSIGNED NOT NULL,
  user_id         INT UNSIGNED NOT NULL,
  amount          DECIMAL(10,2) NOT NULL,
  created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (id),

  CONSTRAINT fk_bids_product
    FOREIGN KEY (product_id) REFERENCES products(id)
    ON DELETE CASCADE,

  CONSTRAINT fk_bids_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE,

  KEY idx_bids_product_created (product_id, created_at),
  KEY idx_bids_product_amount  (product_id, amount)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Orders (created from cart)
-- ------------------------------------------------------------
CREATE TABLE orders (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id         INT UNSIGNED NOT NULL,

  status          ENUM('pending','paid','cancelled','completed') NOT NULL DEFAULT 'pending',
  total_amount    DECIMAL(10,2) NOT NULL DEFAULT 0.00,

  created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (id),

  CONSTRAINT fk_orders_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE,

  KEY idx_orders_user_created (user_id, created_at),
  KEY idx_orders_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Order Items (snapshot price at time of order)
-- ------------------------------------------------------------
CREATE TABLE order_items (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  order_id        INT UNSIGNED NOT NULL,
  product_id      INT UNSIGNED NOT NULL,

  quantity        INT UNSIGNED NOT NULL DEFAULT 1,
  unit_price      DECIMAL(10,2) NOT NULL, -- store snapshot price at checkout
  line_total      DECIMAL(10,2) NOT NULL,

  PRIMARY KEY (id),

  CONSTRAINT fk_order_items_order
    FOREIGN KEY (order_id) REFERENCES orders(id)
    ON DELETE CASCADE,

  CONSTRAINT fk_order_items_product
    FOREIGN KEY (product_id) REFERENCES products(id)
    ON DELETE RESTRICT,

  KEY idx_order_items_order (order_id),
  KEY idx_order_items_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
