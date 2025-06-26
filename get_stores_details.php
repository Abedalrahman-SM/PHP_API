<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");
include('config.php');


if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

$vendor_id = $_POST['vendor_id'] ?? null;
$role = $_POST['role'] ?? null;

if (!$vendor_id || $role !== 'customer') {
    echo json_encode(['status' => 'error', 'message' => 'Access denied or missing data']);
    exit;
}

$db = new Db();
$conn = $db->getConnection();

// Get vendor/store info
$vendor_stmt = $conn->prepare("SELECT store_name, location, textdescription,phone FROM vendors WHERE id = ?");
$vendor_stmt->bind_param("i", $vendor_id);
$vendor_stmt->execute();
$vendor_result = $vendor_stmt->get_result();

if ($vendor_result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Store not found']);
    exit;
}

$store_info = $vendor_result->fetch_assoc();

// Get products of this vendor
$products_stmt = $conn->prepare("SELECT id, name, price, image FROM products WHERE vendor_id = ?");
$products_stmt->bind_param("i", $vendor_id);
$products_stmt->execute();
$products_result = $products_stmt->get_result();

$products = [];
while ($row = $products_result->fetch_assoc()) {
    $products[] = $row;
}

echo json_encode([
    'status' => 'success',
    'store' => $store_info,
    'products' => $products
]);

$vendor_stmt->close();
$products_stmt->close();
$conn->close();
?>