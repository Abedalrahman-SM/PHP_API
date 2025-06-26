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
$status = $_POST['status'] ?? null;
$role = $_POST['role'] ?? null;

if (!$vendor_id || !$status || $role !== 'admin') {
echo json_encode(['status' => 'error', 'message' => 'Missing data or unauthorized']);
 exit;
}


$valid_statuses = ['pending', 'approved', 'rejected'];
if (!in_array($status, $valid_statuses)) {
 echo json_encode(['status' => 'error', 'message' => 'Invalid status']);
 exit;
}

$db = new Db();
$conn = $db->getConnection();
error_log("vendor_id: $vendor_id, status: $status, role: $role");

$sql = "UPDATE vendors SET status = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $status, $vendor_id);

if ($stmt->execute()) {
 echo json_encode(['status' => 'success', 'message' => 'Vendor status updated']);
} else {
 echo json_encode(['status' => 'error', 'message' => 'Failed to update status']);
}

$stmt->close();
$conn->close();
?>