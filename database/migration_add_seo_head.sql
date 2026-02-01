-- Migration: Add seo_head column to seo_data table
-- Run this if you already have the database set up

ALTER TABLE seo_data 
ADD COLUMN seo_head TEXT NULL AFTER h2_text;

