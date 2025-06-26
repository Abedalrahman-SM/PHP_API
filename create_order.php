<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");
include('config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = $_POST['customer_id'];
    $address = $_POST['address'];
    $payment_method = $_POST['payment_method'];

    if (empty($customer_id) || empty($address) || empty($payment_method)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
        exit;
    }

    $db = new Db();
    $conn = $db->getConnection();

    // 1. Fetch products from the cart with prices
    $stmt = $conn->prepare("
        SELECT c.product_id, c.quantity, p.price
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.customer_id = ?
    ");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $order_items = [];
    $total_price = 0;
    while ($row = $result->fetch_assoc()) {
        $order_items[] = $row;
        $total_price += $row['quantity'] * $row['price'];
    }
    $stmt->close();

    if (empty($order_items)) {
        echo json_encode(['status' => 'error', 'message' => 'Cart is empty.']);
        exit;
    }

    // 2. Create the order in the orders table with total_price
    $stmt = $conn->prepare("INSERT INTO orders (customer_id, address, payment_method, total_price,  created_at) VALUES (?, ?, ?, ?,  NOW())");
    $stmt->bind_param("issd", $customer_id, $address, $payment_method, $total_price);
    if (!$stmt->execute()) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to create order.']);
        exit;
    }

    $order_id = $stmt->insert_id;
    $stmt->close();

    // 3. Insert products into order_items
    $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
    foreach ($order_items as $item) {
        $stmt->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
        $stmt->execute();
    }
    $stmt->close();

    // 4. Clear the cart
    $stmt = $conn->prepare("DELETE FROM cart WHERE customer_id = ?");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $stmt->close();

    $db->closeConnection();

    echo json_encode(['status' => 'success', 'message' => 'Order created successfully.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
?>
