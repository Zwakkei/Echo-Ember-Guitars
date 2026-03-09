<?php
require_once '../config/database.php';
require_once __DIR__ . '/../includes/auth.php'; 

if (!isLoggedIn()) {
    redirect('../user/login.php');
}

$user_id = $_SESSION['user_id'];

// Fetch cart items
$sql = "SELECT c.*, p.product_name, p.price, p.stock 
        FROM cart c 
        JOIN products p ON c.product_id = p.product_id 
        WHERE c.user_id = ?";
        
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart_items = $stmt->get_result();

if ($cart_items->num_rows === 0) {
    redirect('view_cart.php');
}

// Calculate total
$total = 0;
$items = [];
while($item = $cart_items->fetch_assoc()) {
    $items[] = $item;
    $total += $item['price'] * $item['quantity'];
}

// Fetch user details
$user_stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shipping_address = sanitize($_POST['shipping_address']);
    $payment_method = sanitize($_POST['payment_method']);
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // Create order
        $order_stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, shipping_address, payment_method) VALUES (?, ?, ?, ?)");
        $order_stmt->bind_param("idss", $user_id, $total, $shipping_address, $payment_method);
        $order_stmt->execute();
        $order_id = $conn->insert_id;
        
        // Add order details and update stock
        foreach ($items as $item) {
            // Add to order_details
            $detail_stmt = $conn->prepare("INSERT INTO order_details (order_id, product_id, quantity, price_at_time) VALUES (?, ?, ?, ?)");
            $detail_stmt->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
            $detail_stmt->execute();
            
            // Update product stock
            $new_stock = $item['stock'] - $item['quantity'];
            $stock_stmt = $conn->prepare("UPDATE products SET stock = ? WHERE product_id = ?");
            $stock_stmt->bind_param("ii", $new_stock, $item['product_id']);
            $stock_stmt->execute();
        }
        
        // Clear cart
        $clear_stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
        $clear_stmt->bind_param("i", $user_id);
        $clear_stmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        // Update session cart count
        $_SESSION['cart_count'] = 0;
        $_SESSION['success'] = "Order placed successfully! Order ID: #" . $order_id;
        
        redirect('../user/orders.php');
        
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Error processing order. Please try again.";
        redirect('checkout.php');
    }
}
?>

<?php include '../includes/header.php'; ?>

<div class="container">
    <h2 class="section-title">Checkout</h2>
    
    <div class="checkout-container">
        <div class="checkout-form">
            <h3>Shipping Information</h3>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" value="<?php echo $user['full_name']; ?>" readonly>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" value="<?php echo $user['email']; ?>" readonly>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="text" id="phone" value="<?php echo $user['phone']; ?>" readonly>
                </div>
                
                <div class="form-group">
                    <label for="shipping_address">Shipping Address *</label>
                    <textarea id="shipping_address" name="shipping_address" rows="3" required><?php echo $user['address']; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="payment_method">Payment Method *</label>
                    <select id="payment_method" name="payment_method" required>
                        <option value="Cash on Delivery">Cash on Delivery</option>
                        <option value="Bank Transfer">Bank Transfer</option>
                        <option value="GCash">GCash</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Place Order</button>
                <a href="view_cart.php" class="btn btn-secondary btn-block">Back to Cart</a>
            </form>
        </div>
        
        <div class="order-summary">
            <h3>Order Summary</h3>
            <div class="summary-items">
                <?php foreach($items as $item): ?>
                    <div class="summary-item">
                        <span class="item-name"><?php echo $item['product_name']; ?> x<?php echo $item['quantity']; ?></span>
                        <span class="item-price">₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="summary-total">
                <p>Total: <span>₱<?php echo number_format($total, 2); ?></span></p>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>