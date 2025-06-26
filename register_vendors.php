<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header('Content-Type: application/json');
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Receive data from the form
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];  // Password
    $location = trim($_POST['location']);
    $textdescription = trim($_POST['textdescription']); // Vendor's description text
    $store_name = trim($_POST['store_name']); // New store name

    // Validate required fields
    if (empty($email) || empty($username) || empty($phone) || empty($password) || empty($location) || empty($textdescription) || empty($store_name)) {
        echo json_encode(array('status' => 'error', 'message' => 'All fields are required.'));
        exit;
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Connect to the database
    $db = new Db();
    $conn = $db->getConnection();

    // Check email
    $stmt = $conn->prepare("SELECT email FROM vendors WHERE email = ? UNION SELECT email FROM customers WHERE email = ?");
    $stmt->bind_param("ss", $email, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        echo json_encode(array('status' => 'error', 'message' => 'Email is already in use.'));
        exit;
    }
    $stmt->close();

    // Check username
    $stmt = $conn->prepare("SELECT username FROM vendors WHERE username = ? UNION SELECT username FROM customers WHERE username = ?");
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        echo json_encode(array('status' => 'error', 'message' => 'Username is already in use.'));
        exit;
    }
    $stmt->close();

    // Check phone number
    $stmt = $conn->prepare("SELECT phone FROM vendors WHERE phone = ? UNION SELECT phone FROM customers WHERE phone = ?");
    $stmt->bind_param("ss", $phone, $phone);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        echo json_encode(array('status' => 'error', 'message' => 'Phone number is already in use.'));
        exit;
    }
    $stmt->close();

    // Add vendor data
    $stmt = $conn->prepare("INSERT INTO vendors (email, username, phone, password, location, textdescription, store_name, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
    $stmt->bind_param("sssssss", $email, $username, $phone, $hashed_password, $location, $textdescription, $store_name);
    if ($stmt->execute()) {
        echo json_encode(array('status' => 'success', 'message' => 'Registration successful. The vendor is awaiting approval from the admin.'));
    } else {
        echo json_encode(array('status' => 'error', 'message' => 'An error occurred during registration.'));
    }

    $stmt->close();
    $db->closeConnection();
} else {
    echo json_encode(array('status' => 'error', 'message' => 'Invalid request'));
}
?>
