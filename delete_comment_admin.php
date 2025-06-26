<?php
include('config.php');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

$role = $_POST['role'] ?? null;
$comment_id = $_POST['comment_id'] ?? null;

if ($role !== 'admin' || !$comment_id) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized or missing data']);
    exit;
}

$db = new Db();
$conn = $db->getConnection();

$sql = "DELETE FROM product_comments WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $comment_id);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Comment deleted successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to delete comment']);
}

$stmt->close();
$conn->close();
?>