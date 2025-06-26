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

if ($role !== 'admin') {
  echo json_encode(['status' => 'error', 'message' => 'Access denied']);
  exit;
}

$db = new Db();
$conn = $db->getConnection();

$sql = "SELECT COUNT(*) AS total_comments FROM product_comments";
$result = $conn->query($sql);

if ($result && $row = $result->fetch_assoc()) {
  echo json_encode([
    'status' => 'success',
    'total_comments' => (int)$row['total_comments']
  ]);
} else {
  echo json_encode([
    'status' => 'error',
    'message' => 'Failed to fetch comments count'
  ]);
}

$conn->close();
?>
