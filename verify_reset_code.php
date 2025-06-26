<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
include("config.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit;
}

$email = $_POST['email'] ?? '';
$code = $_POST['code'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$role = $_POST['role'] ?? '';

if (!$email || !$code || !$new_password || !in_array($role, ['vendor', 'customer'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing or invalid data']);
    exit;
}

$db = new Db();
$conn = $db->getConnection();

$table = $role === 'vendor' ? 'vendors' : 'customers';

$stmt = $conn->prepare("SELECT id FROM $table WHERE email = ? AND reset_code = ?");
$stmt->bind_param("ss", $email, $code);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid code or email']);
    exit;
}

$hashed = password_hash($new_password, PASSWORD_DEFAULT);
$update = $conn->prepare("UPDATE $table SET password = ?, reset_code = NULL WHERE email = ?");
$update->bind_param("ss", $hashed, $email);
$update->execute();

echo json_encode(['status' => 'success', 'message' => 'Password reset successfully']);
$conn->close();
