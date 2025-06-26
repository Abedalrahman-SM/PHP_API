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

$customer_id = $_POST['customer_id'] ?? null;
$role = $_POST['role'] ?? null;

if (!$customer_id || $role !== 'customer') {
    echo json_encode(['status' => 'error', 'message' => 'Access denied or missing data']);
    exit;
}

$db = new Db();
$conn = $db->getConnection();

// ✅ جلب اسماء المتاجر فقط من المفضلة
$sql = "SELECT v.id, v.store_name
        FROM favorite_vendors fv
        JOIN vendors v ON fv.vendor_id = v.id
        WHERE fv.customer_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();

$favorites = [];
while ($row = $result->fetch_assoc()) {
    $favorites[] = $row;
}

echo json_encode(['status' => 'success', 'favorites' => $favorites]);

$stmt->close();
$conn->close();
?>