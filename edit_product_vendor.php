<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");
include('config.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $vendor_id = $_POST['vendor_id'];
    $product_id = $_POST['product_id']; // This is the product ID from the products table

    // Connect to the database
    $db = new Db();
    $conn = $db->getConnection();

   // Check that the product belongs to this vendor
        $stmt = $conn->prepare("SELECT * FROM products WHERE vendor_id = ? AND id = ?");
        $stmt->bind_param("ii", $vendor_id, $product_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            echo json_encode(['status' => 'error', 'message' => 'You do not have permission to edit this product.']);
            exit;
        }


    // New data
    $new_name = $_POST['name'] ?? null;
    $new_price = $_POST['price'] ?? null;
    $new_description = $_POST['description'] ?? null;

    // Image if provided
    $image_new_name = null;
    if (isset($_FILES['image'])) {
        $image = $_FILES['image'];
        $image_ext = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($image_ext, $allowed_exts)) {
            $image_new_name = uniqid('', true) . "." . $image_ext;
            $target_path = "uploads/" . $image_new_name;
            move_uploaded_file($image['tmp_name'], $target_path);
        }
    }

    // Build the update query dynamically
    $fields = [];
    $params = [];
    $types = "";

    if ($new_name !== null) {
        $fields[] = "name = ?";
        $params[] = $new_name;
        $types .= "s";
    }
    if ($new_price !== null) {
        $fields[] = "price = ?";
        $params[] = $new_price;
        $types .= "s";
    }
    if ($new_description !== null) {
        $fields[] = "description = ?";
        $params[] = $new_description;
        $types .= "s";
    }
    if ($image_new_name !== null) {
        $fields[] = "image = ?";
        $params[] = $image_new_name;
        $types .= "s";
    }

    if (empty($fields)) {
        echo json_encode(['status' => 'error', 'message' => 'No data was provided for update.']);
        exit;
    }

    $params[] = $product_id;
    $types .= "i";

    $sql = "UPDATE products SET " . implode(", ", $fields) . " WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Product updated successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update product.']);
    }

    $stmt->close();
    $db->closeConnection();

} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
}
?>
