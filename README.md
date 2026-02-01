# Professional Dealer Website

A comprehensive, SEO-first dealer website built with Core PHP, MySQL, and Bootstrap 5. This system is designed for showcasing multiple brands and their products with city-wise dynamic page generation for maximum SEO impact.

## Features

### Core Features
- **Multi-Brand Support**: Manage multiple brands with their own product categories
- **City-Wise Page Generation**: Automatic generation of city-specific pages for all brands, categories, and products
- **SEO-First Architecture**: Complete SEO control with dynamic meta tags, H1/H2, canonical URLs, and Open Graph tags
- **Dynamic Product Specifications**: Add/remove product specifications with a clean 2-column table
- **Professional Admin Dashboard**: Full-featured CMS for managing all content
- **Responsive Design**: Bootstrap 5 responsive design with professional dealer/industrial styling

### SEO Features
- Dynamic meta titles, descriptions, and keywords
- City-specific SEO optimization
- Auto-generated sitemap.xml
- Canonical URLs
- Open Graph and Twitter Card support
- SEO-friendly URL structure

### Admin Features
- Complete CRUD for Brands, Categories, Products, Cities
- Dynamic product specifications management
- SEO management for all pages (base and city-wise)
- Certifications and Testimonials management
- Enquiry management system
- Static pages management
- Contact details management
- Sitemap generator

## Installation

### Requirements
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache with mod_rewrite enabled
- XAMPP/WAMP/LAMP or similar

### Setup Steps

1. **Database Setup**
   ```sql
   -- Import the database schema
   mysql -u root -p < database/schema.sql
   ```
   Or import `database/schema.sql` through phpMyAdmin

2. **Configuration**
   - Edit `config/database.php` with your database credentials
   - Edit `config/config.php` and update `SITE_URL` to match your domain

3. **File Permissions**
   ```bash
   chmod -R 755 uploads/
   ```

4. **Access Admin Panel**
   - URL: `http://localhost/sparks/admin/`
   - Default credentials:
     - Username: `admin`
     - Password: `admin123`

## Project Structure

```
sparks/
├── admin/                 # Admin dashboard
│   ├── includes/         # Admin header, footer, auth
│   ├── ajax/             # AJAX endpoints
│   └── *.php            # Admin CRUD pages
├── config/               # Configuration files
│   ├── config.php       # Main config
│   └── database.php     # Database connection
├── database/             # Database schema
│   └── schema.sql       # Complete database schema
├── includes/             # Shared functions
│   └── functions.php    # Utility functions
├── public/               # Public website
│   ├── includes/        # Header, footer
│   └── *.php           # Public pages
├── uploads/             # Uploaded images
│   ├── brands/
│   ├── categories/
│   ├── products/
│   └── ...
├── .htaccess            # URL routing
└── README.md
```

## URL Structure

### Public URLs
- Home: `/`
- About: `/about-us`
- Contact: `/contact-us`
- Brand (base): `/bosch`
- Brand (city): `/bosch-pune`
- Category (base): `/bosch/power-tools`
- Category (city): `/bosch/power-tools-pune`
- Product (base): `/bosch/power-tools/drill-machine`
- Product (city): `/bosch/power-tools/drill-machine-pune`
- Sitemap: `/sitemap.xml`

### Admin URLs
- Admin Login: `/admin/login.php`
- Admin Dashboard: `/admin/`
- All admin pages: `/admin/{page}.php`

## City-Wise Page Generation

The system automatically generates city-specific pages for:
- **Brands**: `/brand-slug` (base) and `/brand-slug-city-slug` (city)
- **Categories**: `/brand/category` (base) and `/brand/category-city` (city)
- **Products**: `/brand/category/product` (base) and `/brand/category/product-city` (city)

Each city-specific page has:
- Dynamic H1 text (e.g., "Bosch Pune")
- City-specific meta tags
- Separate SEO data management
- Automatic canonical URLs

## SEO Management

### For Each Entity (Brand/Category/Product)
1. Go to the entity's admin page
2. Click "SEO" button
3. Select city (or "Base Page" for no city)
4. Fill in:
   - Meta Title
   - Meta Description
   - Meta Keywords
   - Canonical URL
   - OG Tags (Title, Description, Image)
   - H1 Text
   - H2 Text

### Sitemap
- Auto-generated at `/sitemap.xml`
- Includes all pages (base and city-wise)
- Updates automatically when content changes

## Product Specifications

Each product can have multiple specifications:
1. Go to Products → Edit → Click "Specs"
2. Add specifications with Name and Value
3. Specifications display in a clean 2-column table on product pages

## Admin Panel Features

### Dashboard
- Statistics overview
- Recent enquiries
- Quick actions

### Brands Management
- Add/Edit/Delete brands
- Upload brand image and logo
- SEO management (base + city-wise)

### Categories Management
- Add/Edit/Delete categories
- Link to brands
- SEO management

### Products Management
- Add/Edit/Delete products
- Link to brand and category
- Image and gallery upload
- Dynamic specifications management
- SEO management

### Cities Management
- Add/Edit/Delete cities
- City slug used for URL generation

### Other Features
- Certifications management
- Testimonials management
- Static pages (About, Contact, etc.)
- Contact details management
- Enquiry viewer and status management

## Security Features

- Prepared statements (PDO) to prevent SQL injection
- Input sanitization
- File upload validation
- Admin authentication
- Session management
- XSS protection

## Best Practices

1. **SEO**: Always fill in SEO data for base pages and important city pages
2. **Images**: Use optimized images (recommended: WebP format)
3. **Content**: Write unique, descriptive content for each page
4. **Sitemap**: Submit sitemap to Google Search Console
5. **Performance**: Enable gzip compression (already in .htaccess)

## Troubleshooting

### Images not uploading
- Check `uploads/` folder permissions (755)
- Check PHP upload settings in `php.ini`

### URLs not working
- Ensure mod_rewrite is enabled
- Check `.htaccess` file is present
- Verify Apache AllowOverride is set to All

### Database connection error
- Check credentials in `config/database.php`
- Ensure MySQL is running
- Verify database exists

## Support

For issues or questions, please refer to the code comments or create an issue in the repository.

## License

This project is built for professional use. Customize as needed for your requirements.

---

**Built with**: Core PHP, MySQL, Bootstrap 5  
**Designed for**: SEO-focused dealer websites with multi-brand and city-wise page generation

