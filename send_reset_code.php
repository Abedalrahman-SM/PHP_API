<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
include("config.php");

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Load PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit;
}

$email = $_POST['email'] ?? null;
if (!$email) {
    echo json_encode(['status' => 'error', 'message' => 'Email is required']);
    exit;
}

$db = new Db();
$conn = $db->getConnection();

$code = rand(100000, 999999);
$role = null;

// Check vendors
$stmt = $conn->prepare("SELECT id FROM vendors WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $role = 'vendor';
    $update = $conn->prepare("UPDATE vendors SET reset_code = ? WHERE email = ?");
    $update->bind_param("ss", $code, $email);
    $update->execute();
}

// Check customers
$stmt = $conn->prepare("SELECT id FROM customers WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $role = 'customer';
    $update = $conn->prepare("UPDATE customers SET reset_code = ? WHERE email = ?");
    $update->bind_param("ss", $code, $email);
    $update->execute();
}

if (!$role) {
    echo json_encode(['status' => 'error', 'message' => 'Email not found']);
    exit;
}

// Send email using PHPMailer
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'abodysheikh38@gmail.com';          // ✅ Your Gmail address
    $mail->Password = 'buwlpctbwhmxxhqb';       // ✅ Your Gmail App Password
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('your@gmail.com', 'Your App');
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = 'Password Reset Code';
    $mail->Body = "Your password reset code is: <strong>$code</strong>";

    $mail->send();

    echo json_encode([
        'status' => 'success',
        'message' => 'Reset code sent',
        'role' => $role
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Email could not be sent. ' . $mail->ErrorInfo
    ]);
}
