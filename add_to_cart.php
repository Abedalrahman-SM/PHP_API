<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");
include('config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = $_POST['customer_id'];
    $product_id = $_POST['product_id'];
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

    if (empty($customer_id) || empty($product_id)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
        exit;
    }

    $db = new Db();
    $conn = $db->getConnection();

    // Check if the product already exists in the cart
    $stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE customer_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $customer_id, $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // Product exists, just increase the quantity
        $new_quantity = $row['quantity'] + $quantity;
        $update_stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        $update_stmt->bind_param("ii", $new_quantity, $row['id']);
        $update_stmt->execute();
        $update_stmt->close();
    } else {
        // New product, add it to the cart
        $insert_stmt = $conn->prepare("INSERT INTO cart (customer_id, product_id, quantity) VALUES (?, ?, ?)");
        $insert_stmt->bind_param("iii", $customer_id, $product_id, $quantity);
        $insert_stmt->execute();
        $insert_stmt->close();
    }

    echo json_encode(['status' => 'success', 'message' => 'Product added to cart']);
    $stmt->close();
    $db->closeConnection();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
?>
