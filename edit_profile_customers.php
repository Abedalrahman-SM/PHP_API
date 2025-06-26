<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");
include('config.php');

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $customer_id      = $_POST['customer_id'] ?? null;
    $role             = $_POST['role'] ?? null;
    $username         = $_POST['username'] ?? null;
    $email            = $_POST['email'] ?? null;
    $phone            = $_POST['phone'] ?? null;
   

    
    if (
        !$customer_id || $role !== 'customer' ||
        !$username || !$email || !$phone 
        
    ) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Unauthorized access or missing required fields.'
        ]);
        exit;
    }

    $db = new Db();
    $conn = $db->getConnection();

    $sql = "UPDATE customers 
            SET username = ?, email = ?, phone = ?
            WHERE id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $username, $email, $phone, $customer_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Profile updated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update profile']);
    }

    $stmt->close();
    $conn->close();

} else {
   
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>