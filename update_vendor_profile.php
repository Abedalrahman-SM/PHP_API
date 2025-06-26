<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header('Content-Type: application/json');
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $vendor_id = $_POST['vendor_id'] ?? '';
    $username = $_POST['username'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $description = $_POST['textdescription'] ?? '';

    // Validate required fields
    if (!$vendor_id || !$username || !$phone || !$description) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required including vendor id']);
        exit;
    }

    // Connect to the database
    $db = new Db();
    $conn = $db->getConnection();

    // Update vendor profile
    $stmt = $conn->prepare("UPDATE vendors SET username = ?, phone = ?, textdescription = ? WHERE id = ?");
    $stmt->bind_param("sssi", $username, $phone, $description, $vendor_id);

    if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Profile updated successfully.']);
    } else {
        // Check if vendor exists
        $check_stmt = $conn->prepare("SELECT id FROM vendors WHERE id = ?");
        $check_stmt->bind_param("i", $vendor_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        if ($result->num_rows > 0) {
            echo json_encode(['status' => 'error', 'message' => 'No changes made. Data is identical to existing data.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Vendor ID does not exist.']);
        }
        $check_stmt->close();
    }
}


    $stmt->close();
    $db->closeConnection();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
