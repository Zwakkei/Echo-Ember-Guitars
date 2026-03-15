<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

// Check if admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    // Get image path to delete file
    $stmt = $conn->prepare("SELECT image_path FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    
    // Delete image file if exists
    if ($product && $product['image_path']) {
        $image_path = '../' . $product['image_path'];
        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }
    
    // Delete product from database
    $delete = $conn->prepare("DELETE FROM products WHERE product_id = ?");
    $delete->bind_param("i", $id);
    $delete->execute();
}

header('Location: products.php');
exit();
?>