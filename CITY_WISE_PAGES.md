# City-Wise Page Generation System

## Overview

This system automatically generates city-specific pages for all brands, categories, and products. This is a critical SEO feature that allows you to create location-based landing pages without manual work.

## How It Works

### URL Structure

For every brand, category, and product, the system generates:

1. **Base Page** (no city):
   - `/bosch`
   - `/bosch/power-tools`
   - `/bosch/power-tools/drill-machine`

2. **City Pages** (auto-generated for each active city):
   - `/bosch-pune`
   - `/bosch-mumbai`
   - `/bosch/power-tools-pune`
   - `/bosch/power-tools-mumbai`
   - `/bosch/power-tools/drill-machine-pune`
   - `/bosch/power-tools/drill-machine-mumbai`

### Automatic Generation

- **No Manual Creation**: When you add a city, all existing brands/categories/products automatically get city-specific pages
- **Dynamic Content**: Each city page can have unique SEO data (meta tags, H1, H2)
- **Same Content, Different SEO**: Product content remains the same, but SEO elements can be customized per city

## Implementation Details

### Routing Logic

The `.htaccess` file handles URL routing:

```apache
# Brand with city: /bosch-pune
RewriteRule ^([a-z0-9-]+)-([a-z0-9-]+)$ public/index.php?type=brand&slug=$1&city=$2

# Category with city: /bosch/power-tools-pune
RewriteRule ^([a-z0-9-]+)/([a-z0-9-]+)-([a-z0-9-]+)$ public/index.php?type=category&brand=$1&slug=$2&city=$3

# Product with city: /bosch/power-tools/drill-machine-pune
RewriteRule ^([a-z0-9-]+)/([a-z0-9-]+)/([a-z0-9-]+)-([a-z0-9-]+)$ public/index.php?type=product&brand=$1&category=$2&slug=$3&city=$4
```

### Database Structure

The `seo_data` table stores SEO information for each entity and city combination:

```sql
CREATE TABLE seo_data (
    entity_type ENUM('brand', 'category', 'product', 'page'),
    entity_id INT,
    city_id INT NULL,  -- NULL for base pages, INT for city pages
    meta_title VARCHAR(255),
    meta_description TEXT,
    h1_text VARCHAR(255),
    ...
    UNIQUE KEY unique_seo (entity_type, entity_id, city_id)
);
```

### Page Rendering

Each page type (brand, category, product) checks for city parameter:

```php
// Get city data if city slug provided
$cityData = null;
$cityId = null;
if (!empty($city)) {
    $stmt = $db->prepare("SELECT * FROM cities WHERE slug = ? AND status = 'active'");
    $stmt->execute([$city]);
    $cityData = $stmt->fetch();
    if ($cityData) {
        $cityId = $cityData['id'];
    }
}

// Get SEO data (base or city-specific)
$seoData = getSEOData($db, 'brand', $brand['id'], $cityId);

// Build dynamic H1
if (empty($seoData['h1_text'])) {
    $seoData['h1_text'] = $brand['name'];
    if ($cityData) {
        $seoData['h1_text'] .= ' ' . $cityData['name'];
    }
}
```

## SEO Management

### Setting Up City-Specific SEO

1. **Go to Admin Panel** → Select entity (Brand/Category/Product)
2. **Click "SEO" button**
3. **Select City** from dropdown (or "Base Page" for no city)
4. **Fill in SEO fields**:
   - Meta Title (e.g., "Bosch Power Tools in Pune")
   - Meta Description
   - H1 Text (e.g., "Bosch Pune")
   - H2 Text
   - Canonical URL
   - OG Tags

### SEO Best Practices

1. **Base Pages First**: Always set up base page SEO before city pages
2. **Unique Content**: Write unique meta descriptions for each city
3. **Location Keywords**: Include city name in meta titles naturally
4. **Canonical URLs**: Set proper canonical URLs to avoid duplicate content issues
5. **H1 Variations**: Use city name in H1 (e.g., "Bosch Pune" vs "Bosch")

## Example Workflow

### Adding a New City

1. Admin → Cities → Add New City
   - Name: "Delhi"
   - Slug: "delhi"
   - Status: Active

2. **Automatic Result**: All existing brands, categories, and products now have:
   - `/brand-delhi`
   - `/brand/category-delhi`
   - `/brand/category/product-delhi`

3. **Configure SEO**: Go to each entity's SEO page and set city-specific SEO for Delhi

### Adding a New Brand

1. Admin → Brands → Add New Brand
   - Name: "Makita"
   - Slug: "makita"

2. **Automatic Result**: For each active city (Pune, Mumbai, Delhi), you get:
   - `/makita` (base)
   - `/makita-pune`
   - `/makita-mumbai`
   - `/makita-delhi`

3. **Configure SEO**: Set base SEO and city-specific SEO as needed

## Sitemap Generation

The sitemap automatically includes all city pages:

```xml
<!-- Base brand page -->
<url>
    <loc>https://yoursite.com/bosch</loc>
    <priority>0.9</priority>
</url>

<!-- City-specific brand pages -->
<url>
    <loc>https://yoursite.com/bosch-pune</loc>
    <priority>0.9</priority>
</url>
<url>
    <loc>https://yoursite.com/bosch-mumbai</loc>
    <priority>0.9</priority>
</url>
```

## Benefits

1. **SEO Scalability**: Create hundreds of location pages automatically
2. **Local SEO**: Target specific cities with unique content
3. **No Manual Work**: Add a city once, get pages for all entities
4. **Flexible**: Customize SEO per city without duplicating content
5. **Search Engine Friendly**: Proper canonical URLs and unique meta tags

## Technical Notes

- City pages share the same content as base pages
- Only SEO elements (meta tags, H1, H2) can differ
- Product specifications and descriptions remain the same
- Images and galleries are shared across all city variations
- The system uses city slugs in URLs for SEO-friendly structure

## Limitations

- City pages cannot have different product content (only SEO)
- All city pages must use the same product images
- City-specific content would require additional database fields

## Future Enhancements

Possible improvements:
- City-specific product descriptions
- City-specific pricing
- City-specific availability
- Location-based product filtering

