<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: user/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle add to wishlist
if (isset($_POST['add_to_wishlist'])) {
    $product_id = (int)$_POST['product_id'];
    
    $check = $conn->prepare("SELECT * FROM wishlist WHERE user_id = ? AND product_id = ?");
    $check->bind_param("ii", $user_id, $product_id);
    $check->execute();
    
    if ($check->get_result()->num_rows === 0) {
        $insert = $conn->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
        $insert->bind_param("ii", $user_id, $product_id);
        $insert->execute();
        $_SESSION['success'] = "Added to wishlist!";
    } else {
        $_SESSION['error'] = "Already in wishlist!";
    }
    
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit();
}

// Handle remove from wishlist
if (isset($_GET['remove'])) {
    $wishlist_id = (int)$_GET['remove'];
    $conn->query("DELETE FROM wishlist WHERE wishlist_id = $wishlist_id AND user_id = $user_id");
    $_SESSION['success'] = "Removed from wishlist";
    header('Location: wishlist.php');
    exit();
}

// Get wishlist items
$wishlist = $conn->query("
    SELECT w.*, p.product_name, p.price, p.image_path, p.category
    FROM wishlist w
    JOIN products p ON w.product_id = p.product_id
    WHERE w.user_id = $user_id
");
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<div class="wishlist-container">
    <h1 class="wishlist-title">❤️ My Wishlist</h1>
    
    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php 
            echo $_SESSION['success']; 
            unset($_SESSION['success']); 
        ?></div>
    <?php endif; ?>
    
    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-error"><?php 
            echo $_SESSION['error']; 
            unset($_SESSION['error']); 
        ?></div>
    <?php endif; ?>
    
    <?php if ($wishlist->num_rows > 0): ?>
        <div class="wishlist-grid">
            <?php while($item = $wishlist->fetch_assoc()): ?>
                <div class="wishlist-item">
                    <div class="wishlist-item-image">
                        <a href="product.php?id=<?php echo $item['product_id']; ?>">
                            <?php if(!empty($item['image_path']) && file_exists(__DIR__ . '/' . $item['image_path'])): ?>
                                <img src="/echo-ember-guitars/<?php echo $item['image_path']; ?>" alt="<?php echo $item['product_name']; ?>">
                            <?php else: ?>
                                <div class="wishlist-placeholder">🎸</div>
                            <?php endif; ?>
                        </a>
                    </div>
                    <div class="wishlist-item-details">
                        <h3>
                            <a href="product.php?id=<?php echo $item['product_id']; ?>">
                                <?php echo $item['product_name']; ?>
                            </a>
                        </h3>
                        <div class="wishlist-item-price">₱<?php echo number_format($item['price'], 2); ?></div>
                       <div class="wishlist-item-actions">
    <form action="/echo-ember-guitars/cart/add_to_cart.php" method="POST" style="flex: 2;">
        <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
        <input type="hidden" name="quantity" value="1">
        <button type="submit" class="btn-add-to-cart" style="width: 100%; border: none; cursor: pointer; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 10px; border-radius: 6px; font-size: 0.95rem; font-weight: 500;">
            🛒 Add to Cart
        </button>
    </form>
    <a href="wishlist.php?remove=<?php echo $item['wishlist_id']; ?>" class="btn-remove" onclick="return confirm('Remove from wishlist?')">✕ Remove</a>
</div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="empty-wishlist">
            <div class="empty-icon">❤️</div>
            <h2>Your wishlist is empty</h2>
            <p>Start adding items you love!</p>
            <a href="shop.php" class="btn-shop">Browse Products</a>
        </div>
    <?php endif; ?>
</div>

<style>
/* ===== WISHLIST PAGE STYLES ===== */
.wishlist-container {
    max-width: 1200px;
    margin: 40px auto;
    padding: 0 20px;
}

.wishlist-title {
    font-size: 2rem;
    color: #333;
    margin-bottom: 30px;
    text-align: center;
    position: relative;
}

.wishlist-title:after {
    content: '';
    display: block;
    width: 60px;
    height: 3px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    margin: 10px auto 0;
    border-radius: 3px;
}

/* Alert Messages */
.alert {
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 25px;
    text-align: center;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

/* Wishlist Grid */
.wishlist-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 25px;
}

/* Wishlist Item Card */
.wishlist-item {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    transition: transform 0.3s, box-shadow 0.3s;
    border: 1px solid #f0f0f0;
}

.wishlist-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}

.wishlist-item-image {
    height: 200px;
    overflow: hidden;
    background: #f8f9fa;
}

.wishlist-item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s;
}

.wishlist-item:hover .wishlist-item-image img {
    transform: scale(1.05);
}

.wishlist-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.wishlist-item-details {
    padding: 20px;
}

.wishlist-item-details h3 {
    margin: 0 0 10px 0;
    font-size: 1.1rem;
    line-height: 1.4;
    height: 2.8em;
    overflow: hidden;
}

.wishlist-item-details h3 a {
    color: #333;
    text-decoration: none;
    transition: color 0.3s;
}

.wishlist-item-details h3 a:hover {
    color: #667eea;
}

.wishlist-item-price {
    font-size: 1.2rem;
    font-weight: 700;
    color: #ff6b6b;
    margin-bottom: 15px;
}

.wishlist-item-actions {
    display: flex;
    gap: 10px;
}

.btn-add-to-cart {
    flex: 2;
    padding: 10px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    text-align: center;
    text-decoration: none;
    border-radius: 6px;
    font-size: 0.95rem;
    font-weight: 500;
    transition: all 0.3s;
}

.btn-add-to-cart:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
}

.btn-remove {
    flex: 1;
    padding: 10px;
    background: #ff6b6b;
    color: white;
    text-align: center;
    text-decoration: none;
    border-radius: 6px;
    font-size: 0.95rem;
    font-weight: 500;
    transition: all 0.3s;
}

.btn-remove:hover {
    background: #ff5252;
    transform: translateY(-2px);
}

/* Empty Wishlist */
.empty-wishlist {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
}

.empty-icon {
    font-size: 4rem;
    margin-bottom: 20px;
    opacity: 0.5;
}

.empty-wishlist h2 {
    color: #333;
    margin-bottom: 10px;
    font-size: 1.8rem;
}

.empty-wishlist p {
    color: #666;
    margin-bottom: 25px;
    font-size: 1.1rem;
}

.btn-shop {
    display: inline-block;
    padding: 12px 30px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s;
}

.btn-shop:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
}

/* Dark Mode Support */
body.dark-mode .wishlist-title {
    color: #fff;
}

body.dark-mode .wishlist-item {
    background: #16213e;
    border-color: #0f3460;
}

body.dark-mode .wishlist-item-details h3 a {
    color: #fff;
}

body.dark-mode .empty-wishlist {
    background: #16213e;
}

body.dark-mode .empty-wishlist h2 {
    color: #fff;
}

body.dark-mode .empty-wishlist p {
    color: #b0b0b0;
}

/* Responsive */
@media (max-width: 768px) {
    .wishlist-grid {
        grid-template-columns: 1fr;
    }
    
    .wishlist-item-actions {
        flex-direction: column;
    }
    
    .btn-add-to-cart,
    .btn-remove {
        width: 100%;
    }
}

@media (max-width: 480px) {
    .wishlist-title {
        font-size: 1.5rem;
    }
    
    .wishlist-item-details {
        padding: 15px;
    }
}
</style>

<?php include __DIR__ . '/includes/footer.php'; ?>