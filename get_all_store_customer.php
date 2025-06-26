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

$role = $_POST['role'] ?? null;

if ($role !== 'customer') {
    echo json_encode(['status' => 'error', 'message' => 'Access denied']);
    exit;
}

$db = new Db();
$conn = $db->getConnection();

$sql = "SELECT id, store_name, location, textdescription,phone  FROM vendors WHERE vendors.status = 'approved'";
$result = $conn->query($sql);

$stores = [];
while ($row = $result->fetch_assoc()) {
    $stores[] = $row;
}

echo json_encode(['status' => 'success', 'stores' => $stores]);

$conn->close();
?>