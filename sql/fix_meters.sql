-- SQL Script to fix meters table

-- Update meter with ID 1
UPDATE meters SET 
    meter_type = 'liters', 
    installation_date = '2025-09-01', 
    status = 'in_stock' 
WHERE id = 1;

-- Update meter with ID 3
UPDATE meters SET 
    meter_type = 'cubic_meters', 
    installation_date = '2025-09-25', 
    status = 'in_stock' 
WHERE id = 3;

-- Fix empty meter_type
UPDATE meters SET 
    meter_type = 'liters' 
WHERE meter_type = '' OR meter_type IS NULL;

-- Fix empty status
UPDATE meters SET 
    status = 'in_stock' 
WHERE status = '' OR status IS NULL;

-- Fix invalid installation dates
UPDATE meters SET 
    installation_date = NULL 
WHERE installation_date = '0000-00-00';