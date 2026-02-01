-- Migration: Fix h2_text column size
-- Change h2_text from VARCHAR(255) to TEXT to allow longer content

ALTER TABLE seo_data 
MODIFY COLUMN h2_text TEXT;

