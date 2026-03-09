<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /echo-ember-guitars/user/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $cart_id = (int)($_POST['cart_id'] ?? 0);
    
    // Verify cart item belongs to user
    $check_stmt = $conn->prepare("SELECT * FROM cart WHERE cart_id = ? AND user_id = ?");
    $check_stmt->bind_param("ii", $cart_id, $user_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows === 0) {
        $_SESSION['error'] = "Invalid cart item!";
        header('Location: /echo-ember-guitars/cart/view_cart.php');
        exit();
    }
    
    if (isset($_POST['update'])) {
        // Update quantity
        $quantity = (int)($_POST['quantity'] ?? 1);
        
        // Check stock
        $stock_stmt = $conn->prepare("
            SELECT p.stock 
            FROM cart c 
            JOIN products p ON c.product_id = p.product_id 
            WHERE c.cart_id = ?
        ");
        $stock_stmt->bind_param("i", $cart_id);
        $stock_stmt->execute();
        $stock_result = $stock_stmt->get_result();
        $stock_data = $stock_result->fetch_assoc();
        
        if ($quantity <= $stock_data['stock']) {
            $update_stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ?");
            $update_stmt->bind_param("ii", $quantity, $cart_id);
            $update_stmt->execute();
            $_SESSION['success'] = "Cart updated successfully!";
        } else {
            $_SESSION['error'] = "Not enough stock! Only {$stock_data['stock']} available.";
        }
        
    } elseif (isset($_POST['remove'])) {
        // Remove item
        $delete_stmt = $conn->prepare("DELETE FROM cart WHERE cart_id = ?");
        $delete_stmt->bind_param("i", $cart_id);
        $delete_stmt->execute();
        $_SESSION['success'] = "Item removed from cart!";
    }
    
    // Update cart count in session
    $count_stmt = $conn->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
    $count_stmt->bind_param("i", $user_id);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $count_data = $count_result->fetch_assoc();
    $_SESSION['cart_count'] = $count_data['total'] ?? 0;
}

header('Location: /echo-ember-guitars/cart/view_cart.php');
exit();
?>