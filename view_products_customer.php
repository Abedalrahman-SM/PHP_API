<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");
include('config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $role = $_POST['role'] ?? '';
    if ($role !== 'customer') {
        echo json_encode(['status' => 'error', 'message' => 'You do not have permission to view products.']);
        exit;
    }

    $search = isset($_POST['search_term']) ? '%' . $_POST['search_term'] . '%' : '%';

    $db = new Db();
    $conn = $db->getConnection();

    $sql = "SELECT 
                p.name, 
                p.price, 
                p.image, 
                v.location, 
                v.store_name 
            FROM products p 
            JOIN vendors v ON p.vendor_id = v.id 
            WHERE (p.name LIKE ? OR v.location LIKE ? OR v.store_name LIKE ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $search, $search, $search);
    $stmt->execute();
    $result = $stmt->get_result();

    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }

    echo json_encode(['status' => 'success', 'products' => $products]);

    $stmt->close();
    $db->closeConnection();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
?>
