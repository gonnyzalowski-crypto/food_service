-- Streicher B2B Shop - Order Flow Enhancement
-- Adds payment uploads, detailed order statuses, and tracking events

-- Modify orders table to support full workflow
ALTER TABLE orders 
  MODIFY COLUMN status ENUM(
    'pending',
    'awaiting_payment',
    'payment_uploaded',
    'payment_confirmed',
    'processing',
    'shipped',
    'in_transit',
    'out_for_delivery',
    'delivered',
    'cancelled',
    'returned'
  ) DEFAULT 'awaiting_payment';

-- Add additional order fields
ALTER TABLE orders
  ADD COLUMN po_number VARCHAR(100) NULL AFTER order_number,
  ADD COLUMN notes TEXT NULL AFTER shipping_address,
  ADD COLUMN admin_notes TEXT NULL AFTER notes,
  ADD COLUMN payment_method VARCHAR(50) DEFAULT 'bank_transfer' AFTER currency,
  ADD COLUMN payment_confirmed_at TIMESTAMP NULL AFTER payment_method,
  ADD COLUMN payment_confirmed_by INT NULL AFTER payment_confirmed_at,
  ADD COLUMN shipped_at TIMESTAMP NULL AFTER payment_confirmed_by,
  ADD COLUMN delivered_at TIMESTAMP NULL AFTER shipped_at;

-- Payment uploads table
CREATE TABLE IF NOT EXISTS payment_uploads (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id BIGINT NOT NULL,
  filename VARCHAR(255) NOT NULL,
  original_filename VARCHAR(255) NOT NULL,
  file_path VARCHAR(500) NOT NULL,
  file_size INT NOT NULL,
  mime_type VARCHAR(100) NOT NULL,
  uploaded_by INT NULL,
  notes TEXT NULL,
  status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
  reviewed_by INT NULL,
  reviewed_at TIMESTAMP NULL,
  review_notes TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_payment_uploads_order FOREIGN KEY (order_id) REFERENCES orders(id),
  CONSTRAINT fk_payment_uploads_user FOREIGN KEY (uploaded_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Enhanced shipments table for detailed tracking
ALTER TABLE shipments
  ADD COLUMN shipped_at TIMESTAMP NULL AFTER status,
  ADD COLUMN estimated_delivery DATE NULL AFTER shipped_at,
  ADD COLUMN actual_delivery TIMESTAMP NULL AFTER estimated_delivery,
  ADD COLUMN origin_city VARCHAR(100) NULL AFTER actual_delivery,
  ADD COLUMN origin_country CHAR(2) DEFAULT 'DE' AFTER origin_city,
  ADD COLUMN destination_city VARCHAR(100) NULL AFTER origin_country,
  ADD COLUMN destination_country CHAR(2) NULL AFTER destination_city,
  ADD COLUMN weight_kg DECIMAL(10,2) NULL AFTER destination_country,
  ADD COLUMN dimensions VARCHAR(100) NULL AFTER weight_kg,
  ADD COLUMN service_type VARCHAR(100) NULL AFTER dimensions;

-- Tracking events table (normalized from JSON)
CREATE TABLE IF NOT EXISTS tracking_events (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  shipment_id BIGINT NOT NULL,
  event_code VARCHAR(50) NOT NULL,
  event_status VARCHAR(100) NOT NULL,
  event_description TEXT NULL,
  location_city VARCHAR(100) NULL,
  location_state VARCHAR(100) NULL,
  location_country CHAR(2) NULL,
  location_facility VARCHAR(255) NULL,
  event_timestamp TIMESTAMP NOT NULL,
  is_exception BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_tracking_events_shipment FOREIGN KEY (shipment_id) REFERENCES shipments(id),
  INDEX idx_shipment_timestamp (shipment_id, event_timestamp DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Product categories
CREATE TABLE IF NOT EXISTS categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  slug VARCHAR(255) UNIQUE NOT NULL,
  description TEXT NULL,
  parent_id INT NULL,
  sort_order INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_categories_parent FOREIGN KEY (parent_id) REFERENCES categories(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add category to products
ALTER TABLE products
  ADD COLUMN category_id INT NULL AFTER sku,
  ADD COLUMN long_description LONGTEXT NULL AFTER description,
  ADD COLUMN features JSON NULL AFTER long_description,
  ADD COLUMN specifications JSON NULL AFTER features,
  ADD COLUMN weight_kg DECIMAL(10,2) NULL AFTER specifications,
  ADD COLUMN dimensions VARCHAR(100) NULL AFTER weight_kg,
  ADD COLUMN warranty_months INT DEFAULT 12 AFTER dimensions,
  ADD COLUMN is_featured BOOLEAN DEFAULT FALSE AFTER warranty_months,
  ADD COLUMN is_active BOOLEAN DEFAULT TRUE AFTER is_featured,
  ADD CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(id);

-- Cart table (persistent carts)
CREATE TABLE IF NOT EXISTS carts (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  session_id VARCHAR(255) NULL,
  user_id INT NULL,
  company_id INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_carts_user FOREIGN KEY (user_id) REFERENCES users(id),
  CONSTRAINT fk_carts_company FOREIGN KEY (company_id) REFERENCES companies(id),
  INDEX idx_session (session_id),
  INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cart_items (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  cart_id BIGINT NOT NULL,
  product_id INT NOT NULL,
  quantity INT NOT NULL DEFAULT 1,
  unit_price DECIMAL(12,2) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_cart_items_cart FOREIGN KEY (cart_id) REFERENCES carts(id) ON DELETE CASCADE,
  CONSTRAINT fk_cart_items_product FOREIGN KEY (product_id) REFERENCES products(id),
  UNIQUE KEY unique_cart_product (cart_id, product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Order activity log
CREATE TABLE IF NOT EXISTS order_activity (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  order_id BIGINT NOT NULL,
  user_id INT NULL,
  action VARCHAR(100) NOT NULL,
  description TEXT NULL,
  old_value TEXT NULL,
  new_value TEXT NULL,
  ip_address VARCHAR(45) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_order_activity_order FOREIGN KEY (order_id) REFERENCES orders(id),
  CONSTRAINT fk_order_activity_user FOREIGN KEY (user_id) REFERENCES users(id),
  INDEX idx_order_created (order_id, created_at DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default categories
INSERT INTO categories (name, slug, description, sort_order) VALUES
('Hydraulic Systems', 'hydraulic-systems', 'Industrial hydraulic pumps, valves, cylinders, and complete systems', 1),
('Drilling Equipment', 'drilling-equipment', 'Rotary drilling rigs, mud pumps, and drilling accessories', 2),
('Pipeline Components', 'pipeline-components', 'Valves, fittings, flanges, and pipeline accessories', 3),
('Compressors', 'compressors', 'Industrial gas and air compressors for oil & gas applications', 4),
('Pumping Systems', 'pumping-systems', 'Centrifugal, reciprocating, and specialty pumps', 5),
('Safety Equipment', 'safety-equipment', 'Blowout preventers, safety valves, and emergency systems', 6),
('Instrumentation', 'instrumentation', 'Pressure gauges, flow meters, and control systems', 7),
('Spare Parts', 'spare-parts', 'Replacement parts, seals, gaskets, and maintenance kits', 8);
