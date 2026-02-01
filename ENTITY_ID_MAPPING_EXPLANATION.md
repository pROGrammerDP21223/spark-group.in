# Entity ID Mapping Explanation

## Where is the Mapping Stored?

**Answer: The mapping is NOT stored in the database. It's hardcoded in PHP code.**

## Location in Code

### 1. Admin Panel Definition
**File:** `admin/page_seo.php` (Lines 9-16)

```php
// Define static pages
$staticPages = [
    'home' => ['name' => 'Home Page', 'entity_id' => 0],
    'about' => ['name' => 'About Us', 'entity_id' => 1],
    'contact' => ['name' => 'Contact Us', 'entity_id' => 2],
    'enquiry' => ['name' => 'Enquiry Form', 'entity_id' => 3],
    'testimonials' => ['name' => 'Testimonials', 'entity_id' => 4],
    'certifications' => ['name' => 'Certifications', 'entity_id' => 5]
];
```

### 2. Public Pages Usage
Each public page file uses the hardcoded entity_id:

- **Home:** `public/home.php` → `getSEOData($db, 'page', 0, null)`
- **About:** `public/about.php` → `getSEOData($db, 'page', 1, null)`
- **Contact:** `public/contact.php` → `getSEOData($db, 'page', 2, null)`
- **Enquiry:** `public/enquiry.php` → `getSEOData($db, 'page', 3, null)`
- **Testimonials:** `public/testimonials.php` → `getSEOData($db, 'page', 4, null)`
- **Certifications:** `public/certifications.php` → `getSEOData($db, 'page', 5, null)`

## How It Works in Database

The `seo_data` table stores the SEO content, but uses the entity_id as a reference:

```sql
-- Example: SEO data for Home page
INSERT INTO seo_data (entity_type, entity_id, city_id, meta_title, ...) 
VALUES ('page', 0, NULL, 'Home Page Title', ...);

-- Example: SEO data for About page
INSERT INTO seo_data (entity_type, entity_id, city_id, meta_title, ...) 
VALUES ('page', 1, NULL, 'About Us Title', ...);
```

### Database Structure
```sql
CREATE TABLE seo_data (
    entity_type ENUM('brand', 'category', 'product', 'page'),
    entity_id INT NOT NULL,  -- This is where 0, 1, 2, 3, 4, 5 are stored
    city_id INT NULL,
    meta_title VARCHAR(255),
    ...
    UNIQUE KEY unique_seo (entity_type, entity_id, city_id)
);
```

## Why Not in Database?

The entity IDs are **fixed identifiers** (like constants), not dynamic data. They're similar to:
- HTTP status codes (200, 404, 500)
- ENUM values in database
- Configuration constants

## How to View/Query in Database

To see SEO data for static pages in the database:

```sql
-- View all static page SEO data
SELECT * FROM seo_data 
WHERE entity_type = 'page' 
ORDER BY entity_id;

-- View Home page SEO (entity_id = 0)
SELECT * FROM seo_data 
WHERE entity_type = 'page' AND entity_id = 0;

-- View About page SEO (entity_id = 1)
SELECT * FROM seo_data 
WHERE entity_type = 'page' AND entity_id = 1;
```

## Summary

| Location | What's Stored | Purpose |
|----------|--------------|---------|
| **PHP Code** (`admin/page_seo.php`) | Mapping definition (which page = which entity_id) | Defines the relationship |
| **PHP Code** (public pages) | Hardcoded entity_id values | Used to fetch SEO data |
| **Database** (`seo_data` table) | Actual SEO content (meta tags, etc.) | Stores the SEO data |

**The mapping itself is in code, but the SEO data it references is in the database.**

