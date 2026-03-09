<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please login to view your cart";
    header('Location: /echo-ember-guitars/user/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch cart items with product details
$sql = "SELECT c.*, p.product_name, p.price, p.stock, p.image_path, p.category 
        FROM cart c 
        JOIN products p ON c.product_id = p.product_id 
        WHERE c.user_id = ?";
        
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$items = [];
$total = 0;

while ($row = $result->fetch_assoc()) {
    $items[] = $row;
    $total += $row['price'] * $row['quantity'];
}

// Category emoji fallback
$category_emoji = [
    'Acoustic' => '🎸',
    'Electric' => '⚡',
    'Bass' => '🎸',
    'Classical' => '🎵',
    'Amplifier' => '🔊',
    'Pedal' => '🎛️',
    'Tool Kit' => '🔧',
    'Tuner' => '🎵',
    'Picks' => '⛏️',
    'Strings' => '🎸',
    'Accessory' => '🎒'
];
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="container">
    <h2 class="section-title">🛒 Shopping Cart</h2>
    
    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php 
            echo $_SESSION['success']; 
            unset($_SESSION['success']); 
        ?></div>
    <?php endif; ?>
    
    <?php if (!empty($items)): ?>
        <div class="cart-layout">
            <!-- Cart Items -->
            <div class="cart-items">
                <?php foreach($items as $item): ?>
                    <div class="cart-item-card">
                        <!-- Product Image with Fixed Size -->
                        <div class="cart-image-container">
                            <?php 
                            $image_path = !empty($item['image_path']) && file_exists(__DIR__ . '/../' . $item['image_path']) 
                                        ? '/echo-ember-guitars/' . $item['image_path'] 
                                        : null;
                            ?>
                            <?php if ($image_path): ?>
                                <img src="<?php echo $image_path; ?>" 
                                     alt="<?php echo $item['product_name']; ?>"
                                     class="cart-product-image"
                                     onerror="this.style.display='none'; this.parentElement.innerHTML='<?php echo $category_emoji[$item['category']] ?? '🎸'; ?>';">
                            <?php else: ?>
                                <div class="cart-image-fallback">
                                    <?php echo $category_emoji[$item['category']] ?? '🎸'; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Item Details -->
                        <div class="cart-item-details">
                            <h3><?php echo htmlspecialchars($item['product_name']); ?></h3>
                            <div class="cart-price">₱<?php echo number_format($item['price'], 2); ?></div>
                            
                            <form action="/echo-ember-guitars/cart/update_cart.php" method="POST" class="cart-actions">
                                <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                                
                                <div class="cart-quantity">
                                    <label>Qty:</label>
                                    <input type="number" 
                                           name="quantity" 
                                           value="<?php echo $item['quantity']; ?>" 
                                           min="1" 
                                           max="<?php echo $item['stock']; ?>"
                                           class="qty-field">
                                </div>
                                
                                <div class="cart-buttons">
                                    <button type="submit" name="update" class="btn-update">↻ Update</button>
                                    <button type="submit" name="remove" class="btn-remove">✕ Remove</button>
                                </div>
                            </form>
                            
                            <div class="cart-subtotal">
                                Subtotal: <strong>₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></strong>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Order Summary -->
            <div class="cart-summary">
                <h3>📋 Order Summary</h3>
                
                <div class="summary-line">
                    <span>Subtotal:</span>
                    <span>₱<?php echo number_format($total, 2); ?></span>
                </div>
                
                <div class="summary-line">
                    <span>Shipping:</span>
                    <span class="free-badge">FREE</span>
                </div>
                
                <div class="summary-line total-line">
                    <span>Total:</span>
                    <span>₱<?php echo number_format($total, 2); ?></span>
                </div>
                
                <a href="/echo-ember-guitars/cart/checkout.php" class="checkout-button">Proceed to Checkout →</a>
                <a href="/echo-ember-guitars/index.php#products" class="continue-button">← Continue Shopping</a>
            </div>
        </div>
    <?php else: ?>
        <div class="empty-cart-message">
            <div class="empty-cart-icon">🛒</div>
            <h3>Your cart is empty</h3>
            <p>Ready to find your next guitar?</p>
            <a href="/echo-ember-guitars/index.php#products" class="btn btn-primary">Shop Now →</a>
        </div>
    <?php endif; ?>
</div>

<style>
/* Cart Layout */
.cart-layout {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 30px;
    margin-top: 30px;
}

/* Cart Items */
.cart-items {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.cart-item-card {
    display: flex;
    background: white;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    gap: 20px;
    transition: transform 0.2s;
}

.cart-item-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

/* Fixed Size Image Container */
.cart-image-container {
    width: 100px;
    height: 100px;
    flex-shrink: 0;
    background: #f8f9fa;
    border-radius: 8px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #f0f0f0;
}

.cart-product-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.cart-image-fallback {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

/* Item Details */
.cart-item-details {
    flex: 1;
}

.cart-item-details h3 {
    font-size: 1.1rem;
    margin: 0 0 5px 0;
    color: #333;
}

.cart-price {
    font-size: 1rem;
    font-weight: 600;
    color: #ff6b6b;
    margin-bottom: 10px;
}

/* Cart Actions */
.cart-actions {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 10px;
    flex-wrap: wrap;
}

.cart-quantity {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #f8f9fa;
    padding: 5px 10px;
    border-radius: 6px;
}

.cart-quantity label {
    color: #666;
    font-size: 0.9rem;
}

.qty-field {
    width: 50px;
    padding: 4px;
    border: 1px solid #ddd;
    border-radius: 4px;
    text-align: center;
}

.cart-buttons {
    display: flex;
    gap: 8px;
}

.btn-update {
    padding: 5px 12px;
    background: #4CAF50;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.9rem;
}

.btn-remove {
    padding: 5px 12px;
    background: #ff6b6b;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.9rem;
}

.cart-subtotal {
    font-size: 0.95rem;
    color: #666;
}

.cart-subtotal strong {
    color: #333;
    margin-left: 5px;
}

/* Order Summary */
.cart-summary {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    position: sticky;
    top: 100px;
    height: fit-content;
}

.cart-summary h3 {
    margin: 0 0 15px 0;
    padding-bottom: 10px;
    border-bottom: 2px solid #f0f0f0;
    color: #333;
}

.summary-line {
    display: flex;
    justify-content: space-between;
    margin: 12px 0;
    color: #666;
}

.free-badge {
    color: #4CAF50;
    font-weight: 600;
}

.total-line {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 2px solid #f0f0f0;
    font-size: 1.2rem;
    font-weight: 600;
    color: #333;
}

.total-line span:last-child {
    color: #ff6b6b;
}

.checkout-button {
    display: block;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    text-align: center;
    padding: 14px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    margin: 20px 0 10px;
    transition: transform 0.2s;
}

.checkout-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
}

.continue-button {
    display: block;
    background: #f8f9fa;
    color: #666;
    text-align: center;
    padding: 12px;
    border-radius: 8px;
    text-decoration: none;
    transition: background 0.2s;
}

.continue-button:hover {
    background: #f0f0f0;
    color: #333;
}

/* Empty Cart */
.empty-cart-message {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.empty-cart-icon {
    font-size: 4rem;
    margin-bottom: 20px;
    opacity: 0.7;
}

.empty-cart-message h3 {
    color: #333;
    margin-bottom: 10px;
    font-size: 1.5rem;
}

.empty-cart-message p {
    color: #666;
    margin-bottom: 25px;
}

/* Responsive */
@media (max-width: 768px) {
    .cart-layout {
        grid-template-columns: 1fr;
    }
    
    .cart-summary {
        position: static;
        margin-top: 20px;
    }
    
    .cart-item-card {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    
    .cart-image-container {
        width: 120px;
        height: 120px;
        margin-bottom: 10px;
    }
    
    .cart-actions {
        justify-content: center;
    }
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>