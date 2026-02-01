<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdminLogin();

header('Content-Type: application/json');

$brand_id = intval($_GET['brand_id'] ?? 0);

if (!$brand_id) {
    echo json_encode([]);
    exit;
}

$db = Database::getInstance()->getConnection();
$stmt = $db->prepare("SELECT id, name FROM product_categories WHERE brand_id = ? AND status = 'active' ORDER BY name ASC");
$stmt->execute([$brand_id]);
$categories = $stmt->fetchAll();

echo json_encode($categories);

