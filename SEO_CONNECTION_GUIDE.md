# SEO Data Connection Guide

## How SEO Data is Connected to Pages

### Overview

The system uses a unified `seo_data` table to manage SEO for all page types. Each page type uses:
- **entity_type**: The type of entity (brand, category, product, page)
- **entity_id**: The ID of the specific entity
- **city_id**: NULL for base pages, or city ID for city-specific pages

---

## 1. Product Pages

### Connection Method
```php
// In public/product_detail.php
$seoData = getSEOData($db, 'product', $product['id'], $cityId);
```

### How It Works
- **entity_type**: `'product'`
- **entity_id**: Product ID from `products` table
- **city_id**: NULL (base) or city ID (city-specific)

### Example
- Product ID: 5
- Base page: `getSEOData($db, 'product', 5, null)`
- Pune page: `getSEOData($db, 'product', 5, 1)` (where 1 is Pune's city_id)

### Admin Management
- Go to: **Admin → Products → Edit → SEO Button**
- Select city (or base page)
- Manage SEO data

---

## 2. Product Category Pages

### Connection Method
```php
// In public/category_detail.php
$seoData = getSEOData($db, 'category', $category['id'], $cityId);
```

### How It Works
- **entity_type**: `'category'`
- **entity_id**: Category ID from `product_categories` table
- **city_id**: NULL (base) or city ID (city-specific)

### Example
- Category ID: 3
- Base page: `getSEOData($db, 'category', 3, null)`
- Mumbai page: `getSEOData($db, 'category', 3, 2)` (where 2 is Mumbai's city_id)

### Admin Management
- Go to: **Admin → Categories → Edit → SEO Button**
- Select city (or base page)
- Manage SEO data

---

## 3. Brand Pages

### Connection Method
```php
// In public/brand_detail.php
$seoData = getSEOData($db, 'brand', $brand['id'], $cityId);
```

### How It Works
- **entity_type**: `'brand'`
- **entity_id**: Brand ID from `brands` table
- **city_id**: NULL (base) or city ID (city-specific)

### Example
- Brand ID: 2
- Base page: `getSEOData($db, 'brand', 2, null)`
- Delhi page: `getSEOData($db, 'brand', 2, 3)` (where 3 is Delhi's city_id)

### Admin Management
- Go to: **Admin → Brands → Edit → SEO Button**
- Select city (or base page)
- Manage SEO data

---

## 4. Static Pages (Home, About, Contact, etc.)

### Connection Method
```php
// Each static page uses a fixed entity_id:
// Home: entity_id = 0
// About: entity_id = 1
// Contact: entity_id = 2
// Enquiry: entity_id = 3
// Testimonials: entity_id = 4
// Certifications: entity_id = 5

$pageSEO = getSEOData($db, 'page', $entityId, null);
```

### How It Works
- **entity_type**: `'page'`
- **entity_id**: Fixed ID for each page type (see below)
- **city_id**: Always NULL (static pages don't have city variations)

### Entity ID Mapping

| Page | Entity ID | URL | Admin Location |
|------|-----------|-----|----------------|
| Home | 0 | `/` | Admin → Page SEO → Home Page |
| About Us | 1 | `/about-us` | Admin → Page SEO → About Us |
| Contact Us | 2 | `/contact-us` | Admin → Page SEO → Contact Us |
| Enquiry | 3 | `/enquiry` | Admin → Page SEO → Enquiry Form |
| Testimonials | 4 | `/testimonials` | Admin → Page SEO → Testimonials |
| Certifications | 5 | `/certifications` | Admin → Page SEO → Certifications |

### Example
```php
// Home page
$pageSEO = getSEOData($db, 'page', 0, null);

// About page
$pageSEO = getSEOData($db, 'page', 1, null);

// Contact page
$pageSEO = getSEOData($db, 'page', 2, null);
```

### Admin Management
- Go to: **Admin → Page SEO**
- Select page from dropdown
- Manage SEO data for that page

---

## Database Structure

### seo_data Table
```sql
CREATE TABLE seo_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    entity_type ENUM('brand', 'category', 'product', 'page') NOT NULL,
    entity_id INT NOT NULL,
    city_id INT NULL,
    meta_title VARCHAR(255),
    meta_description TEXT,
    meta_keywords VARCHAR(500),
    canonical_url VARCHAR(500),
    og_title VARCHAR(255),
    og_description TEXT,
    og_image VARCHAR(500),
    h1_text VARCHAR(255),
    h2_text VARCHAR(255),
    seo_head TEXT,
    ...
    UNIQUE KEY unique_seo (entity_type, entity_id, city_id)
);
```

### Key Points
- **Unique Constraint**: `(entity_type, entity_id, city_id)` ensures one SEO record per combination
- **city_id = NULL**: Base pages (no city)
- **city_id = INT**: City-specific pages

---

## How to Add SEO for a New Page

### For Dynamic Pages (Brand/Category/Product)
1. The SEO system is already connected
2. Just go to Admin → [Entity] → Edit → SEO
3. Fill in the SEO fields

### For Static Pages
1. Add page to `$staticPages` array in `admin/page_seo.php`
2. Assign a unique `entity_id`
3. Update the public page file to fetch SEO:
   ```php
   $pageSEO = getSEOData($db, 'page', $entityId, null);
   ```
4. Add fallback defaults if needed

---

## SEO Data Flow

```
Admin Panel
    ↓
Save SEO Data (saveSEOData function)
    ↓
Database (seo_data table)
    ↓
Public Page (getSEOData function)
    ↓
Header Template (displays meta tags)
```

---

## Examples

### Product Page SEO
```php
// Product ID 10, Base page
$seo = getSEOData($db, 'product', 10, null);
// Returns SEO data where:
// entity_type = 'product'
// entity_id = 10
// city_id = NULL

// Product ID 10, Pune page
$seo = getSEOData($db, 'product', 10, 1);
// Returns SEO data where:
// entity_type = 'product'
// entity_id = 10
// city_id = 1
```

### Static Page SEO
```php
// Home page
$seo = getSEOData($db, 'page', 0, null);
// Returns SEO data where:
// entity_type = 'page'
// entity_id = 0
// city_id = NULL
```

---

## Important Notes

1. **Fallback Values**: If no SEO data exists, pages use default values
2. **City-Specific**: Only Brand/Category/Product pages support city variations
3. **Static Pages**: Always use `city_id = NULL`
4. **SEO Head Code**: Custom code (Google Analytics, etc.) is stored in `seo_head` column
5. **Canonical URLs**: Should be set for each page to avoid duplicate content issues

---

## Troubleshooting

### SEO not showing on page
1. Check if SEO data exists in database
2. Verify entity_type and entity_id match
3. Check if city_id is correct (NULL for base pages)
4. Verify page is calling `getSEOData()` correctly

### Duplicate SEO entries
- The unique constraint prevents duplicates
- If you see duplicates, check the database directly

### City-specific SEO not working
- Ensure city_id is passed correctly
- Verify city exists in cities table
- Check URL routing for city parameter

