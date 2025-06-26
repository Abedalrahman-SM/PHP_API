<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");
include('config.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
   
    $vendor_id = $_POST['vendor_id'];
    $product_id = $_POST['product_id'];
   


    if (empty($vendor_id) || empty($product_id)) {
        echo json_encode(array('status' => 'error', 'message' => 'ID is required.'));
        exit;
    }

    $db = new Db();
    $conn = $db->getConnection();

    // Make sure the product belongs to this vendor
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ? AND vendor_id = ?");
    $stmt->bind_param("ii", $product_id, $vendor_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        $image = $product['image'];


        if (!empty($image) && file_exists("uploads/" . $image)) {
            unlink("uploads/" . $image);
        }

        // Delete the product from the database
        $deleteStmt = $conn->prepare("DELETE FROM products WHERE id = ? AND vendor_id = ?");
        $deleteStmt->bind_param("ii", $product_id, $vendor_id);

        if ($deleteStmt->execute()) {
            echo json_encode(array('status' => 'success', 'message' => 'Product deleted successfully.'));
        } else {
            echo json_encode(array('status' => 'error', 'message' => 'An error occurred while deleting the product.'));
        }

        $deleteStmt->close();
    } else {
        echo json_encode(array('status' => 'error', 'message' => 'Product not found or does not belong to this vendor.'));
    }

    $stmt->close();
    $db->closeConnection();
} else {
    echo json_encode(array('status' => 'error', 'message' => 'Invalid request.'));
}
?>
