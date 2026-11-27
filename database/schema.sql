CREATE DATABASE IF NOT EXISTS loyalty_rewards
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE loyalty_rewards;

-- Customers and the admin share one table; "role" separates them.
CREATE TABLE users (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(100) NOT NULL,
  email         VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role          ENUM('customer','admin') NOT NULL DEFAULT 'customer',
  status        ENUM('active','blocked') NOT NULL DEFAULT 'active',
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE products (
  id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name           VARCHAR(150) NOT NULL,
  coins_per_unit INT UNSIGNED NOT NULL,
  is_active      TINYINT(1) NOT NULL DEFAULT 1,
  created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- One production run of codes for one product.
CREATE TABLE batches (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  product_id  INT UNSIGNED NOT NULL,
  quantity    INT UNSIGNED NOT NULL,
  created_by  INT UNSIGNED NOT NULL,
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(id),
  FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB;

-- One row per physical box. We store only an HMAC hash of the code.
CREATE TABLE product_codes (
  id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  batch_id   INT UNSIGNED NOT NULL,
  code_hash  CHAR(64) NOT NULL UNIQUE,
  status     ENUM('unused','claimed','blocked') NOT NULL DEFAULT 'unused',
  claimed_by INT UNSIGNED NULL,
  claimed_at TIMESTAMP NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (batch_id)   REFERENCES batches(id),
  FOREIGN KEY (claimed_by) REFERENCES users(id),
  INDEX idx_status (status)
) ENGINE=InnoDB;

-- Every scan attempt, successful or not.
CREATE TABLE claim_attempts (
  id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id         INT UNSIGNED NULL,
  product_code_id BIGINT UNSIGNED NULL,
  result          ENUM('success','invalid','already_claimed','blocked','rate_limited') NOT NULL,
  ip_address      VARCHAR(45) NOT NULL,
  created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id)         REFERENCES users(id),
  FOREIGN KEY (product_code_id) REFERENCES product_codes(id),
  INDEX idx_user_time (user_id, created_at),
  INDEX idx_ip_time (ip_address, created_at)
) ENGINE=InnoDB;

CREATE TABLE redemption_requests (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id     INT UNSIGNED NOT NULL,
  coins       INT UNSIGNED NOT NULL,
  rupees      DECIMAL(10,2) NOT NULL,
  status      ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  reviewed_by INT UNSIGNED NULL,
  reviewed_at TIMESTAMP NULL,
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id)     REFERENCES users(id),
  FOREIGN KEY (reviewed_by) REFERENCES users(id)
) ENGINE=InnoDB;

-- The ledger: balance = SUM(points) for a user. Rows are never edited.
CREATE TABLE point_ledger (
  id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id         INT UNSIGNED NOT NULL,
  entry_type      ENUM('earn','redeem_hold','redeem_refund') NOT NULL,
  points          INT NOT NULL,               -- positive = added, negative = removed
  product_code_id BIGINT UNSIGNED NULL UNIQUE, -- a code can earn points only once
  redemption_id   INT UNSIGNED NULL,
  created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id)         REFERENCES users(id),
  FOREIGN KEY (product_code_id) REFERENCES product_codes(id),
  FOREIGN KEY (redemption_id)   REFERENCES redemption_requests(id),
  INDEX idx_user (user_id)
) ENGINE=InnoDB;

CREATE TABLE settings (
  setting_key   VARCHAR(50) PRIMARY KEY,
  setting_value VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

INSERT INTO settings (setting_key, setting_value) VALUES
  ('rupees_per_coin', '1.00'),
  ('min_redeem_coins', '100');


CREATE TABLE login_attempts (
  id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email      VARCHAR(190) NOT NULL,
  ip_address VARCHAR(45) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_email_time (email, created_at)
) ENGINE=InnoDB;