-- Remove tracking functionality - replaced with supply portal
-- This migration removes all tracking-related tables and columns

SET FOREIGN_KEY_CHECKS=0;

-- Drop tracking communications table
DROP TABLE IF EXISTS tracking_communications;

-- Drop tracking history table  
DROP TABLE IF EXISTS tracking_history;

-- Drop tracking events table
DROP TABLE IF EXISTS tracking_events;

-- Drop shipments table
DROP TABLE IF EXISTS shipments;

-- Remove any tracking-related columns from orders table (if they exist)
ALTER TABLE orders 
DROP COLUMN IF EXISTS tracking_number,
DROP COLUMN IF EXISTS shipped_at,
DROP COLUMN IF EXISTS delivered_at;

SET FOREIGN_KEY_CHECKS=1;
