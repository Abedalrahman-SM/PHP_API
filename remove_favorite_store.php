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
$vendor_id   = $_POST['vendor_id'] ?? null;
$role        = $_POST['role'] ?? null;

if (!$customer_id || !$vendor_id || $role !== 'customer') {
    echo json_encode(['status' => 'error', 'message' => 'Missing data or unauthorized access']);
    exit;
}

$db = new Db();
$conn = $db->getConnection();

$sql = "DELETE FROM favorite_vendors WHERE customer_id = ? AND vendor_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $customer_id, $vendor_id);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Store removed from favorites']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to remove from favorites']);
}

$stmt->close();
$conn->close();
?>