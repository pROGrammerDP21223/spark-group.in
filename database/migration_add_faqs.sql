-- Migration: Add FAQs table
-- Date: 2024

CREATE TABLE IF NOT EXISTS faqs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question VARCHAR(500) NOT NULL,
    answer TEXT NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_sort (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert FAQ data
INSERT INTO faqs (question, answer, status, sort_order) VALUES
('How can I place an order for industrial tools and abrasives?', 'You can place an order through our website, email, or by contacting our sales team. We provide guidance to help you select the right tools and products for your industry needs.', 'active', 1),
('Which brands do you distribute?', 'We are authorized partners of BOSCH, Tyrolit, and ICFS, offering genuine, high-quality power tools, abrasives, construction chemicals, and fastening solutions.', 'active', 2),
('Do you provide technical support for your products?', 'Yes, we offer expert technical support to help you choose, operate, and maintain tools and fastening systems for optimal performance.', 'active', 3),
('Can I get customized solutions for my manufacturing or construction needs?', 'Absolutely! We work closely with our clients to provide tailored solutions, including specialized tools, abrasive products, and fastening systems for your unique operational requirements.', 'active', 4),
('Do you offer bulk orders for industrial clients?', 'Yes, we cater to small and large-scale orders, ensuring timely delivery and competitive pricing for industrial and commercial customers.', 'active', 5),
('How do I know if a product is suitable for my industry application?', 'Our sales and technical team can recommend the right products based on your industry, machinery, and operational needs, ensuring efficiency and safety.', 'active', 6),
('Where are your products delivered?', 'We serve manufacturing units, fabrication shops, construction companies, and industrial clients across Pune and other industrial regions in India.', 'active', 7),
('Do you provide after-sales service for tools and machinery accessories?', 'Yes, we provide guidance, maintenance tips, and replacement support to ensure your equipment operates at peak performance.', 'active', 8);

