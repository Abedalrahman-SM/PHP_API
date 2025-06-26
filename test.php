<?php
require_once 'config.php';

$db = new Db();
$conn = $db->getConnection();

if ($conn) {
    echo "✅ Connected successfully to database.";
} else {
    echo "❌ Failed to connect.";
}
?>
