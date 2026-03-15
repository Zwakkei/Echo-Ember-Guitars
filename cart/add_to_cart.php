<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please login to add items to cart";
    header('Location: /echo-ember-guitars/user/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $product_id = (int)($_POST['product_id'] ?? 0);
    $quantity = (int)($_POST['quantity'] ?? 1);
    
    // Check if product exists and has stock
    $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ? AND stock >= ?");
    $stmt->bind_param("ii", $product_id, $quantity);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Check if already in cart
        $check = $conn->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
        $check->bind_param("ii", $user_id, $product_id);
        $check->execute();
        $cart_result = $check->get_result();
        
        if ($cart_result->num_rows > 0) {
            // Update quantity
            $cart_item = $cart_result->fetch_assoc();
            $new_qty = $cart_item['quantity'] + $quantity;
            $update = $conn->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ?");
            $update->bind_param("ii", $new_qty, $cart_item['cart_id']);
            $update->execute();
        } else {
            // Insert new
            $insert = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $insert->bind_param("iii", $user_id, $product_id, $quantity);
            $insert->execute();
        }
        
        // Update cart count in session
        $count = $conn->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
        $count->bind_param("i", $user_id);
        $count->execute();
        $count_result = $count->get_result();
        $count_data = $count_result->fetch_assoc();
        $_SESSION['cart_count'] = $count_data['total'] ?? 0;
        
        $_SESSION['success'] = "Product added to cart!";
        
        // Check if we came from wishlist
        if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'wishlist.php') !== false) {
            header('Location: /echo-ember-guitars/wishlist.php');
        } else {
            header('Location: /echo-ember-guitars/cart/view_cart.php');
        }
        exit();
    } else {
        $_SESSION['error'] = "Product not available or out of stock!";
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/echo-ember-guitars/index.php'));
        exit();
    }
} else {
    header('Location: /echo-ember-guitars/index.php');
    exit();
}
?>