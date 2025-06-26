<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header('Content-Type: application/json');
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // استلام الإيميل والباسورد
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // الاتصال بقاعدة البيانات
    $db = new Db();
    $conn = $db->getConnection();

    // التحقق من وجود الإيميل في أي جدول
    $stmt = $conn->prepare("SELECT id, username, password, 'vendor' AS role FROM vendors WHERE email = ?
                        UNION 
                        SELECT id, username, password, 'customer' AS role FROM customers WHERE email = ?
                        UNION 
                        SELECT id, username, password, 'admin' AS role FROM admins WHERE username = ?");
$stmt->bind_param("sss", $email, $email, $email); // still passing same value for all three

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // في حال كان Vendor، نتحقق من الحالة (pending / approved)
        if ($row['role'] == 'vendor') {
            $vendor_id = $row['id'];

            $stmt_check_status = $conn->prepare("SELECT status FROM vendors WHERE id = ?");
            $stmt_check_status->bind_param("i", $vendor_id);
            $stmt_check_status->execute();
            $status_result = $stmt_check_status->get_result();
            $status_row = $status_result->fetch_assoc();

            if ($status_row['status'] == 'pending') {
                echo json_encode(['status' => 'error', 'message' => 'Your account is pending approval from the admin.']);
                exit;
            }
        }

        // التحقق من الباسورد
        if (password_verify($password, $row['password'])) {
            $role = $row['role'];
            $userId = $row['id'];
            $userName = $row['username'];

            $response = [
                'status' => 'success',
                'message' => 'Login successful.',
                'role' => $role,
                'user_id' => $userId,
                'username' => $userName,
                
            ];

            // إذا كان Vendor، أضف vendor_id
            if ($role == 'vendor') {
               $stmt_vendor = $conn->prepare("SELECT id, store_name , textdescription,phone FROM vendors WHERE email = ?");
                 $stmt_vendor->bind_param("s", $email);
                $stmt_vendor->execute();
                $vendor_result = $stmt_vendor->get_result();
                if ($vendor_result && $vendor_result->num_rows > 0) {
                    $vendor_row = $vendor_result->fetch_assoc();
                    $response['vendor_id'] = $vendor_row['id'];
                    $response['store_name'] = $vendor_row['store_name'];
                    $response['textdescription'] = $vendor_row['textdescription'];
                    $response['phone'] = $vendor_row['phone'];

                }
            }

            echo json_encode($response);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Incorrect password.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Email not found.']);
    }

    $stmt->close();
    $db->closeConnection();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
