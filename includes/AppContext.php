<?php
/**
 * AppContext
 *  - Central place to load/shared common data (brands, categories, contact)
 *  - Avoids duplicating the same queries in header, footer, and pages
 */

class AppContext
{
    /** @var PDO */
    public PDO $db;

    /** @var array<int,array> */
    public array $brands = [];

    /** @var array<int,array<int,array>> brand_id => categories[] */
    public array $categoriesByBrand = [];

    /** @var array<string,array<int,array>> type => records[] */
    public array $contactMap = [];

    /** @var array<int,array> All brands (shortcut for footer, etc.) */
    public array $allBrands = [];

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->loadBrands();
        $this->loadCategoriesByBrand();
        $this->loadContactDetails();
    }

    protected function loadBrands(): void
    {
        $stmt = $this->db->query(
            "SELECT * FROM brands
             WHERE status = 'active'
             ORDER BY sort_order ASC, name ASC"
        );
        $this->brands = $stmt->fetchAll();
        $this->allBrands = $this->brands;
    }

    protected function loadCategoriesByBrand(): void
    {
        $this->categoriesByBrand = [];

        foreach ($this->brands as $brand) {
            $stmt = $this->db->prepare(
                "SELECT * FROM product_categories
                 WHERE brand_id = ? AND status = 'active'
                 ORDER BY sort_order ASC, name ASC"
            );
            $stmt->execute([$brand['id']]);
            $this->categoriesByBrand[$brand['id']] = $stmt->fetchAll();
        }
    }

    protected function loadContactDetails(): void
    {
        $stmt = $this->db->query(
            "SELECT * FROM contact_details
             WHERE status = 'active'
             ORDER BY sort_order ASC"
        );
        $contactDetails = $stmt->fetchAll();

        $this->contactMap = [];
        foreach ($contactDetails as $contact) {
            $this->contactMap[$contact['type']][] = $contact;
        }
    }
}


