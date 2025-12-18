-- Fix meters table by adding and populating meter_type and status columns
USE water_billing_db;

-- Check if meter_type column exists, if not add it
ALTER TABLE meters ADD COLUMN IF NOT EXISTS meter_type VARCHAR(50) DEFAULT 'residential' AFTER serial_number;

-- Check if status column exists, if not add it
ALTER TABLE meters ADD COLUMN IF NOT EXISTS status VARCHAR(50) DEFAULT 'functional' AFTER initial_reading;

-- Update all meters with empty meter_type
UPDATE meters SET meter_type = 'residential' WHERE meter_type IS NULL OR meter_type = '';

-- Update all meters with empty status
UPDATE meters SET status = 'functional' WHERE status IS NULL OR status = '';

-- Show the updated table
SELECT id, serial_number, meter_type, initial_reading, status, client_id, photo_url FROM meters;