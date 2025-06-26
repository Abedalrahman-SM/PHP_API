<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");
include('config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') 
{
    $cart_id = $_POST['cart_id'];
    $customer_id = $_POST['customer_id'];

    if (empty($cart_id) || empty($customer_id)) 
    {
        echo json_encode([
            'status' => 'error', 
            'message' => 'cart_id and customer_id are required'
        ]);
        exit;
    }

    $db = new Db();
    $conn = $db->getConnection();

    // Delete item with ownership verification
    $stmt = $conn->prepare("
        DELETE FROM cart 
        WHERE id = ? 
        AND customer_id = ?
    ");
    
    $stmt->bind_param("ii", $cart_id, $customer_id);
    
    if ($stmt->execute() && $stmt->affected_rows > 0) 
    {
        echo json_encode([
            'status' => 'success',
            'message' => 'Product removed from cart'
        ]);
    } 
    else 
    {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to remove product or product not found'
        ]);
    }

    $stmt->close();
    $db->closeConnection();
} 
else 
{
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request'
    ]);
}
?>
