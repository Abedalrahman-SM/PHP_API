<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");
include('config.php');
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

$role = $_POST['role'] ?? null;

if ($role !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Access denied']);
    exit;
}

$db = new Db();
$conn = $db->getConnection();

$sql = "SELECT 
            pc.id AS comment_id,
            pc.comment,
            pc.created_at,
            c.username AS customer_username,
            p.name AS product_name,
            v.store_name AS vendor_name
        FROM product_comments pc
        JOIN customers c ON pc.customer_id = c.id
        JOIN products p ON pc.product_id = p.id
        JOIN vendors v ON p.vendor_id = v.id
        ORDER BY pc.created_at DESC";

$result = $conn->query($sql);

$comments = [];
while ($row = $result->fetch_assoc()) {
    $comments[] = $row;
}

echo json_encode(['status' => 'success', 'comments' => $comments]);

$conn->close();
?>