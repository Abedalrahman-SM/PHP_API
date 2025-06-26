<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header('Content-Type: application/json');
require_once 'config.php';

// ✅ Create the DB instance and get the connection
$db = new Db();
$conn = $db-> getConnection();

// Get POST data
$username = $_POST['username'] ?? '';
$email    = $_POST['email'] ?? '';
$phone    = $_POST['phone'] ?? '';
$password = $_POST['password'] ?? '';



// Check for empty fields
if (empty($username) || empty($password) || empty($email) || empty($phone)) {
    echo json_encode(['status' => 'error', 'message' => 'All fields required.']);
    exit;
}

// Check if email exists
$check = mysqli_query($conn, "SELECT id FROM customers WHERE email='$email'");
if (mysqli_num_rows($check) > 0) {
    echo json_encode(['status' => 'error', 'message' => 'Email already exists.']);
    exit;
}

// Hash password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Insert user
$query = "INSERT INTO customers (username, email,  phone,password) VALUES ('$username', '$email', '$phone', '$hashedPassword')";
if (mysqli_query($conn, $query)) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Registration failed.']);
}
?>
