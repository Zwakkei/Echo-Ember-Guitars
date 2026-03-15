<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

// Create detailed debug log
$debug_file = __DIR__ . '/debug_detailed.txt';
file_put_contents($debug_file, "\n\n=== " . date('Y-m-d H:i:s') . " ===\n", FILE_APPEND);
file_put_contents($debug_file, "POST data: " . print_r($_POST, true) . "\n", FILE_APPEND);
file_put_contents($debug_file, "SESSION user_id: " . ($_SESSION['user_id'] ?? 'not set') . "\n", FILE_APPEND);

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    file_put_contents($debug_file, "ERROR: Not logged in\n", FILE_APPEND);
    header('Location: /echo-ember-guitars/user/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $cart_id = (int)($_POST['cart_id'] ?? 0);
    
    file_put_contents($debug_file, "Processing: cart_id=$cart_id, user_id=$user_id\n", FILE_APPEND);
    
    // Verify cart item belongs to user
    $check_stmt = $conn->prepare("SELECT * FROM cart WHERE cart_id = ? AND user_id = ?");
    $check_stmt->bind_param("ii", $cart_id, $user_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows === 0) {
        file_put_contents($debug_file, "ERROR: Cart item not found for this user\n", FILE_APPEND);
        $_SESSION['error'] = "Invalid cart item!";
        header('Location: /echo-ember-guitars/cart/view_cart.php');
        exit();
    }
    
    $cart_item = $result->fetch_assoc();
    file_put_contents($debug_file, "Cart item found: " . print_r($cart_item, true) . "\n", FILE_APPEND);
    
    // Handle update
    if (isset($_POST['update'])) {
        $quantity = (int)($_POST['quantity'] ?? 1);
        file_put_contents($debug_file, "UPDATE action with quantity: $quantity\n", FILE_APPEND);
        
        // Check stock
        $stock_stmt = $conn->prepare("SELECT stock FROM products WHERE product_id = ?");
        $stock_stmt->bind_param("i", $cart_item['product_id']);
        $stock_stmt->execute();
        $stock_result = $stock_stmt->get_result();
        $product = $stock_result->fetch_assoc();
        
        file_put_contents($debug_file, "Product stock: " . $product['stock'] . "\n", FILE_APPEND);
        
        if ($quantity > 0 && $quantity <= $product['stock']) {
            $update_stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ?");
            $update_stmt->bind_param("ii", $quantity, $cart_id);
            
            if ($update_stmt->execute()) {
                file_put_contents($debug_file, "SUCCESS: Updated quantity to $quantity\n", FILE_APPEND);
                $_SESSION['success'] = "Cart updated successfully!";
            } else {
                file_put_contents($debug_file, "ERROR: Update failed - " . $conn->error . "\n", FILE_APPEND);
                $_SESSION['error'] = "Failed to update cart!";
            }
        } else {
            file_put_contents($debug_file, "ERROR: Invalid quantity $quantity (max: {$product['stock']})\n", FILE_APPEND);
            $_SESSION['error'] = "Invalid quantity! Max stock: " . $product['stock'];
        }
    }
    
    // Handle remove
    if (isset($_POST['remove'])) {
        file_put_contents($debug_file, "REMOVE action detected\n", FILE_APPEND);
        $delete_stmt = $conn->prepare("DELETE FROM cart WHERE cart_id = ?");
        $delete_stmt->bind_param("i", $cart_id);
        
        if ($delete_stmt->execute()) {
            file_put_contents($debug_file, "SUCCESS: Item removed\n", FILE_APPEND);
            $_SESSION['success'] = "Item removed from cart!";
        } else {
            file_put_contents($debug_file, "ERROR: Remove failed - " . $conn->error . "\n", FILE_APPEND);
            $_SESSION['error'] = "Failed to remove item!";
        }
    }
    
    // Update cart count in session
    $count_stmt = $conn->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
    $count_stmt->bind_param("i", $user_id);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $count_data = $count_result->fetch_assoc();
    $_SESSION['cart_count'] = $count_data['total'] ?? 0;
    file_put_contents($debug_file, "New cart count: " . $_SESSION['cart_count'] . "\n", FILE_APPEND);
    
} else {
    file_put_contents($debug_file, "ERROR: Not a POST request\n", FILE_APPEND);
}

file_put_contents($debug_file, "Redirecting to cart page\n", FILE_APPEND);
header('Location: /echo-ember-guitars/cart/view_cart.php');
exit();
?>