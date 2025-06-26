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

$vendor_id = $_POST['vendor_id'] ?? null;
$role = $_POST['role'] ?? null;

if (!$vendor_id || $role !== 'vendor') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$db = new Db();
$conn = $db->getConnection();

$sql = "
    SELECT DISTINCT o.id, o.address, o.payment_method, c.phone
    FROM orders o
    JOIN order_items oi ON oi.order_id = o.id
    JOIN products p ON oi.product_id = p.id
    JOIN customers c ON o.customer_id = c.id
    WHERE p.vendor_id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $vendor_id);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];

while ($row = $result->fetch_assoc()) {
    $order_id = $row['id'];

    $item_sql = "
        SELECT p.name AS product_name, p.price, oi.quantity, oi.status
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ? AND p.vendor_id = ?
    ";
    $item_stmt = $conn->prepare($item_sql);
    $item_stmt->bind_param("ii", $order_id, $vendor_id);
    $item_stmt->execute();
    $items_result = $item_stmt->get_result();

    $items = [];
    $total_price = 0; 

    while ($item = $items_result->fetch_assoc()) {
        $item_total = floatval($item['price']) * intval($item['quantity']);
        $total_price += $item_total;

        
        $item['item_total_price'] = $item_total;

        $items[] = $item;
    }

    $item_stmt->close();

    $orders[] = [
        'order_id' => $order_id,
        'address' => $row['address'],
        'total_price' => number_format($total_price, 2, '.', ''), 
        'payment_method' => $row['payment_method'],
        'customer_phone' => $row['phone'],
        'items' => $items
    ];
}

$stmt->close();
$conn->close();

echo json_encode(['status' => 'success', 'orders' => $orders]);
?>
