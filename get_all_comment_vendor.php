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

if (!$vendor_id || $role !== 'vendor') {
    echo json_encode(['status' => 'error', 'message' => 'Access denied or missing data']);
    exit;
}

$db = new Db();
$conn = $db->getConnection();


$sql = "SELECT 
            pc.comment,
            pc.created_at,
            c.username AS customer_username,
            p.name AS product_name
        FROM product_comments pc
        JOIN customers c ON pc.customer_id = c.id
        JOIN products p ON pc.product_id = p.id
        WHERE p.vendor_id = ?
        ORDER BY pc.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $vendor_id);
$stmt->execute();
$result = $stmt->get_result();

$comments = [];
while ($row = $result->fetch_assoc()) {
    $comments[] = $row;
}

echo json_encode(['status' => 'success', 'comments' => $comments]);

$stmt->close();
$conn->close();
?>