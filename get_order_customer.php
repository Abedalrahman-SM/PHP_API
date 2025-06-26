<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

include("config.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

$customer_id = $_POST['customer_id'] ?? null;
$role = $_POST['role'] ?? null;
$order_id = $_POST['order_id'] ?? null;

if (!$customer_id || $role !== 'customer') {
    echo json_encode(['status' => 'error', 'message' => 'Missing or invalid data']);
    exit;
}

$db = new Db();
$conn = $db->getConnection();


if ($order_id) {
    $stmt = $conn->prepare("UPDATE orders SET is_hidden_for_customer = 1 WHERE id = ? AND customer_id = ?");
    $stmt->bind_param("ii", $order_id, $customer_id);
    $stmt->execute();
    $stmt->close();
}


$ordersStmt = $conn->prepare("SELECT * FROM orders WHERE customer_id = ? AND is_hidden_for_customer = 0");
$ordersStmt->bind_param("i", $customer_id);
$ordersStmt->execute();
$ordersResult = $ordersStmt->get_result();


$orders = [];

while ($order = $ordersResult->fetch_assoc()) {
    $order_id = $order['id'];

    $itemsStmt = $conn->prepare("
        SELECT
           o.total_price,
            p.name AS product_name,
            oi.price,
            oi.quantity,
            oi.status,
            v.store_name AS vendor_name
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        JOIN vendors v ON p.vendor_id = v.id
        JOIN orders o ON oi.order_id = o.id
        WHERE oi.order_id = ?
    ");
    $itemsStmt->bind_param("i", $order_id);
    $itemsStmt->execute();
    $itemsResult = $itemsStmt->get_result();

    $items = [];
    $order_status = null;
    $total_price = 0;
    $total = 0;

    while ($item = $itemsResult->fetch_assoc()) {
        $item['total'] = $item['price'] * $item['quantity'];
        $total += $item['total'];
         $items[] = $item;
        if (!$order_status) {
            $order_status = $item['status'];
        }
    }

    $order['status'] = $order_status ?? 'pending';
    $item['total'] = $total;
    $item['total_price'] = $total_price;
    $order['items'] = $items;

    $orders[] = $order;

    $itemsStmt->close();
}

echo json_encode([
    'status' => 'success',
    'orders' => $orders
]);

$ordersStmt->close();
$conn->close();
?>
