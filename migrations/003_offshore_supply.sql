-- Gordon Food Service Offshore Supply - Contractor Code + Supply Requests

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS=0;

CREATE TABLE IF NOT EXISTS contractors (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(255) NOT NULL,
  company_name VARCHAR(255) NOT NULL,
  contractor_code VARCHAR(32) NOT NULL UNIQUE,
  discount_percent DECIMAL(5,2) DEFAULT 35.00,
  discount_eligible TINYINT(1) DEFAULT 1,
  active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_active_code (active, contractor_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS supply_pricing_config (
  id INT AUTO_INCREMENT PRIMARY KEY,
  config_json JSON NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS supply_requests (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  request_number VARCHAR(64) NOT NULL UNIQUE,
  contractor_id INT NOT NULL,
  duration_days INT NOT NULL,
  crew_size INT NOT NULL,
  supply_types JSON NOT NULL,
  delivery_location VARCHAR(50) NOT NULL,
  delivery_speed VARCHAR(50) NOT NULL,
  storage_life_months INT NULL,
  calculated_price DECIMAL(14,2) NOT NULL,
  currency VARCHAR(3) DEFAULT 'USD',
  status VARCHAR(50) DEFAULT 'submitted',
  effective_date DATE NULL,
  notes TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_supply_requests_contractor FOREIGN KEY (contractor_id) REFERENCES contractors(id),
  INDEX idx_contractor_date (contractor_id, created_at),
  INDEX idx_effective_date (effective_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO supply_pricing_config (config_json)
SELECT JSON_OBJECT(
  'base_rate_per_person_day', 22.5,
  'type_multipliers', JSON_OBJECT('water', 0.9, 'dry_food', 1.0, 'canned_food', 1.05, 'mixed_supplies', 1.1),
  'location_multipliers', JSON_OBJECT('pickup', 0.85, 'local', 0.95, 'onshore', 1.0, 'nearshore', 1.15, 'offshore_rig', 1.35),
  'speed_multipliers', JSON_OBJECT('standard', 1.0, 'priority', 1.2, 'emergency', 1.45)
)
WHERE NOT EXISTS (SELECT 1 FROM supply_pricing_config);

SET FOREIGN_KEY_CHECKS=1;
