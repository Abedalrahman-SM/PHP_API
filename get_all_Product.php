<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
include('config.php');

$db = new Db();
$conn = $db->getConnection();


$sql = "
    SELECT 
        p.id,
        p.name,
        p.price,
        p.image,
        p.vendor_id, 
        v.store_name
    FROM products p
    JOIN vendors v ON p.vendor_id = v.id
";

$result = $conn->query($sql);

$products = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }

    echo json_encode([
        'status' => 'success',
        'products' => $products
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'No products found or query failed'
    ]);
}

$conn->close();
?>
