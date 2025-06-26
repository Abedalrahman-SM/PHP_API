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

// Check if already in favorites
$check = $conn->prepare("SELECT id FROM favorite_vendors WHERE customer_id = ? AND vendor_id = ?");
$check->bind_param("ii", $customer_id, $vendor_id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['status' => 'error', 'message' => 'Store already in favorites']);
    exit;
}

// Insert into favorites
$sql = "INSERT INTO favorite_vendors (customer_id, vendor_id) VALUES (?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $customer_id, $vendor_id);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Store added to favorites']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to add to favorites']);
}

$stmt->close();
$conn->close();
?>