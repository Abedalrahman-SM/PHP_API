<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

include('config.php');

$vendor_id = isset($_POST['vendor_id']) ? $_POST['vendor_id'] : (isset($_GET['vendor_id']) ? $_GET['vendor_id'] : null);

if (!$vendor_id) {
    echo json_encode(['status' => 'error', 'message' => 'vendor_id is required']);
    exit;
}

$db = new Db();
$conn = $db->getConnection();

$stmt = $conn->prepare("SELECT * FROM products WHERE vendor_id = ?");
$stmt->bind_param("i", $vendor_id);
$stmt->execute();
$result = $stmt->get_result();

$products = [];
while ($row = $result->fetch_assoc()) {
    $row['image_url'] = !empty($row['image']) ? "http://192.168.1.5/Senior/" . $row['image'] : null;
    $products[] = $row;
}

if (count($products) > 0) {
    echo json_encode(['status' => 'success', 'products' => $products]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'No products found.']);
}

$stmt->close();
$db->closeConnection();
?>
