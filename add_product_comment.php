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
$product_id  = $_POST['product_id'] ?? null;
$comment     = $_POST['comment'] ?? null;
$role        = $_POST['role'] ?? null;

if (!$customer_id || !$product_id || !$comment || $role !== 'customer') {
    echo json_encode(['status' => 'error', 'message' => 'Missing data or unauthorized']);
    exit;
}

$db = new Db();
$conn = $db->getConnection();

$sql = "INSERT INTO product_comments (customer_id, product_id, comment) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iis", $customer_id, $product_id, $comment);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Comment added successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to add comment']);
}

$stmt->close();
$conn->close();
?>