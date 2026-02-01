# Upgrade Notes - New Features Added

## New Features

### 1. Multiple Product Images (Gallery)
- Products already support multiple images via gallery field
- Gallery images are stored as JSON array
- Admin can upload multiple images when adding/editing products
- Gallery displays on product detail pages

### 2. SEO Head Column
- New `seo_head` column added to `seo_data` table
- Allows adding custom HTML/JavaScript code in `<head>` section
- Perfect for:
  - Google Analytics
  - Google Tag Manager
  - Schema markup
  - Custom meta tags
  - Any other head-level code

**How to use:**
1. Go to any entity's SEO page (Brand/Category/Product)
2. Scroll to "Custom SEO Head Code" field
3. Paste your code (e.g., Google Analytics script)
4. Save

### 3. Rich Text Editor
- TinyMCE editor integrated for:
  - Product descriptions
  - Static page content
- Features:
  - Bold, italic, underline
  - Lists (ordered/unordered)
  - Links
  - Images
  - Text alignment
  - Formatting options
  - Code view

**How to use:**
- When editing products or static pages, you'll see a rich text editor toolbar
- Format your content using the toolbar
- HTML is automatically generated and saved
- Content displays properly formatted on public pages

## Database Migration

If you already have the database set up, run this migration:

```sql
ALTER TABLE seo_data 
ADD COLUMN seo_head TEXT NULL AFTER h2_text;
```

Or import: `database/migration_add_seo_head.sql`

## Files Modified

1. `database/schema.sql` - Added `seo_head` column
2. `includes/functions.php` - Updated SEO functions
3. `admin/brands.php` - Added SEO head field and rich text editor
4. `admin/categories.php` - Added SEO head field
5. `admin/products.php` - Added rich text editor for description, SEO head field
6. `admin/pages.php` - Added rich text editor for content
7. `public/includes/header.php` - Added SEO head code output, styling for rich content
8. `public/product_detail.php` - Output HTML from rich editor
9. `public/about.php` - Output HTML from rich editor
10. `public/brand_detail.php` - Pass SEO head code
11. `public/category_detail.php` - Pass SEO head code
12. `public/product_detail.php` - Pass SEO head code

## Notes

- Rich text content is stored as HTML in database
- Content is output directly (not escaped) to preserve formatting
- SEO head code is output directly in `<head>` section
- Be careful with SEO head code - validate it before saving
- TinyMCE uses CDN (no API key needed for basic features)

