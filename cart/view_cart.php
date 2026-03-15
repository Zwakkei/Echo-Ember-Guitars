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

// Category emoji mapping
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

// Fetch cart items
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
                    <div class="cart-item-card" id="cart-item-<?php echo $item['cart_id']; ?>">
                        <!-- Product Image Container - ADDED BACK -->
                        <div class="cart-image-container">
                            <img src="/echo-ember-guitars/<?php echo $item['image_path']; ?>" 
                                 alt="<?php echo $item['product_name']; ?>"
                                 class="cart-product-image"
                                 onerror="this.style.display='none'; this.parentElement.innerHTML='<div class=\'cart-image-fallback\'>' + '<?php echo $category_emoji[$item['category']] ?? '🎸'; ?>' + '</div>';">
                        </div>
                        
                        <!-- Item Details -->
                        <div class="cart-item-details">
                            <h3><?php echo htmlspecialchars($item['product_name']); ?></h3>
                            <div class="cart-price">₱<?php echo number_format($item['price'], 2); ?></div>
                            
                            <!-- FIXED FORM WITH JAVASCRIPT FALLBACK -->
                            <form action="/echo-ember-guitars/cart/update_cart.php" method="POST" class="cart-update-form" id="form-<?php echo $item['cart_id']; ?>">
                                <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                                <input type="hidden" name="action" id="action-<?php echo $item['cart_id']; ?>" value="">
                                
                                <div class="cart-controls">
                                    <label for="qty_<?php echo $item['cart_id']; ?>">Qty:</label>
                                    <input type="number" 
                                           id="qty_<?php echo $item['cart_id']; ?>" 
                                           name="quantity" 
                                           value="<?php echo $item['quantity']; ?>" 
                                           min="1" 
                                           max="<?php echo $item['stock']; ?>"
                                           class="qty-input">
                                    
                                    <button type="button" class="btn-update" onclick="submitUpdate(<?php echo $item['cart_id']; ?>)">Update</button>
                                    <button type="button" class="btn-remove" onclick="submitRemove(<?php echo $item['cart_id']; ?>)">Remove</button>
                                </div>
                            </form>
                            
                            <div class="cart-subtotal">
                                Subtotal: <strong id="subtotal-<?php echo $item['cart_id']; ?>">₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></strong>
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
                    <span id="cart-total">₱<?php echo number_format($total, 2); ?></span>
                </div>
                <div class="summary-line">
                    <span>Shipping:</span>
                    <span class="free-badge">FREE</span>
                </div>
                <div class="summary-line total-line">
                    <span>Total:</span>
                    <span id="cart-total-final">₱<?php echo number_format($total, 2); ?></span>
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

<script>
function submitUpdate(cartId) {
    const form = document.getElementById('form-' + cartId);
    const actionInput = document.getElementById('action-' + cartId);
    actionInput.name = 'update';
    actionInput.value = '1';
    form.submit();
}

function submitRemove(cartId) {
    if (confirm('Remove this item from cart?')) {
        const form = document.getElementById('form-' + cartId);
        const actionInput = document.getElementById('action-' + cartId);
        actionInput.name = 'remove';
        actionInput.value = '1';
        form.submit();
    }
}
</script>

<style>
.cart-layout {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 30px;
    margin-top: 30px;
}

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

/* Image Container Styles */
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

.cart-item-details {
    flex: 1;
}

.cart-item-details h3 {
    margin: 0 0 5px 0;
    color: #333;
    font-size: 1.2rem;
}

.cart-price {
    font-size: 1.1rem;
    font-weight: 600;
    color: #ff6b6b;
    margin-bottom: 15px;
}

.cart-controls {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    background: #f8f9fa;
    padding: 10px;
    border-radius: 8px;
    margin: 10px 0;
}

.cart-controls label {
    font-weight: 600;
    color: #555;
}

.qty-input {
    width: 60px;
    padding: 8px;
    border: 2px solid #ddd;
    border-radius: 6px;
    text-align: center;
}

.btn-update {
    padding: 8px 16px;
    background: #4CAF50;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s;
}

.btn-update:hover {
    background: #45a049;
    transform: translateY(-2px);
}

.btn-remove {
    padding: 8px 16px;
    background: #ff6b6b;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s;
}

.btn-remove:hover {
    background: #ff5252;
    transform: translateY(-2px);
}

.cart-subtotal {
    font-size: 1rem;
    color: #666;
    margin-top: 10px;
}

.cart-subtotal strong {
    color: #333;
    font-size: 1.1rem;
    margin-left: 5px;
}

.cart-summary {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    position: sticky;
    top: 100px;
    height: fit-content;
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
    transition: all 0.3s;
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
    transition: all 0.3s;
}

.continue-button:hover {
    background: #f0f0f0;
    color: #333;
}

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

/* Dark Mode Support */
body.dark-mode .cart-item-card {
    background: #16213e;
    border-color: #0f3460;
}

body.dark-mode .cart-item-details h3 {
    color: #fff;
}

body.dark-mode .cart-controls {
    background: #0f3460;
}

body.dark-mode .cart-controls label {
    color: #b0b0b0;
}

body.dark-mode .cart-summary {
    background: #16213e;
}

body.dark-mode .cart-summary h3 {
    color: #fff;
}

body.dark-mode .empty-cart-message {
    background: #16213e;
}

body.dark-mode .empty-cart-message h3 {
    color: #fff;
}

body.dark-mode .empty-cart-message p {
    color: #b0b0b0;
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
    
    .cart-controls {
        justify-content: center;
    }
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>