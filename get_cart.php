<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");
include('config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = $_POST['customer_id'];

    if (empty($customer_id)) {
        echo json_encode(['status' => 'error', 'message' => 'the number of customer required']);
        exit;
    }

    $db = new Db();
    $conn = $db->getConnection();

    $stmt = $conn->prepare("
        SELECT 
            c.id AS cart_id,
            c.quantity,
            p.id AS product_id,
            p.name,
            p.price,
            p.image,
            p.vendor_id,
            (p.price * c.quantity) AS total_price
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.customer_id = ?
    ");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $cartItems = [];
    while ($row = $result->fetch_assoc()) {
        $cartItems[] = $row;
    }

    echo json_encode(['status' => 'success', 'data' => $cartItems]);

    $stmt->close();
    $db->closeConnection();
} else {
    echo json_encode(['status' => 'error', 'message' => 'invalid request']);
}
?>
