<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

include('config.php'); 

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

$order_id = $_POST['order_id'] ?? null;
$vendor_id = $_POST['vendor_id'] ?? null;
$new_status = $_POST['status'] ?? null;
$role = $_POST['role'] ?? null; 

if (!$order_id || !$vendor_id || !$new_status || $role !== 'vendor') {
    echo json_encode(['status' => 'error', 'message' => 'Missing required data or unauthorized']);
    exit;
}

$db = new Db();
$conn = $db->getConnection();

$sql = "UPDATE order_items oi
        JOIN products p ON oi.product_id = p.id
        SET oi.status = ?
        WHERE oi.order_id = ? AND p.vendor_id = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => 'Prepare failed: ' . $conn->error]);
    exit;
}

$stmt->bind_param("sii", $new_status, $order_id, $vendor_id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Status updated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No matching records found']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Execute failed: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
