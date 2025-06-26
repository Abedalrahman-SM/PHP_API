<?php
include('config.php');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");
$customer_id = $_POST['customer_id'] ?? null;
$role = $_POST['role']?? null;
if (!$customer_id|| $role !=='customer') {
    echo json_encode(['status' => 'error', 'message' => 'you no have permission']);
    exit;
}

$db = new Db();
$conn = $db->getConnection();

$sql = "SELECT  username, email, phone
        FROM customers WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $customer = $result->fetch_assoc();
    echo json_encode(['status' => 'success', 'customer' => $customer]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'customer not found']);
}

$stmt->close();
$conn->close();
?>