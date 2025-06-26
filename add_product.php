<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header('Content-Type: application/json');
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $vendor_id = $_POST['vendor_id'] ?? '';
    $name = $_POST['name'] ?? '';
    $price = $_POST['price'] ?? '';
    $description = $_POST['description'] ?? '';
    $image_url = $_POST['image_url'] ?? null;
    $image_name = null;

    // Basic validation
    if (empty($vendor_id) || empty($name) || empty($price) || empty($description)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
        exit;
    }

    // Connect to DB
    $db = new Db();
    $conn = $db->getConnection();

    // If image file uploaded
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image = $_FILES['image'];
        $image_name = $image['name'];
        $image_tmp_name = $image['tmp_name'];
        $image_ext = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));

        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($image_ext, $allowed_exts)) {
            echo json_encode(['status' => 'error', 'message' => 'Unsupported image type.']);
            exit;
        }

        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        $image_new_name = uniqid('prod_', true) . "." . $image_ext;
        $target_file = $target_dir . $image_new_name;

        if (move_uploaded_file($image_tmp_name, $target_file)) {
            $image_url = $target_file;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'An error occurred while uploading the image.']);
            exit;
        }
    }

    // Ensure at least image_url or uploaded file exists
    if ($image_url) {
        $stmt = $conn->prepare("INSERT INTO products (vendor_id, name, price, description, image) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $vendor_id, $name, $price, $description, $image_url);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Product added successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'An error occurred while adding the product.']);
        }
        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No valid image or image URL provided.']);
    }

    $db->closeConnection();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>
