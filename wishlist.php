<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: user/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle add to wishlist
if (isset($_POST['add_to_wishlist'])) {
    $product_id = $_POST['product_id'];
    
    $check = $conn->prepare("SELECT * FROM wishlist WHERE user_id = ? AND product_id = ?");
    $check->bind_param("ii", $user_id, $product_id);
    $check->execute();
    
    if ($check->get_result()->num_rows === 0) {
        $insert = $conn->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
        $insert->bind_param("ii", $user_id, $product_id);
        $insert->execute();
        $_SESSION['success'] = "Added to wishlist!";
    }
}

// Handle remove from wishlist
if (isset($_GET['remove'])) {
    $wishlist_id = $_GET['remove'];
    $conn->query("DELETE FROM wishlist WHERE wishlist_id = $wishlist_id AND user_id = $user_id");
    $_SESSION['success'] = "Removed from wishlist";
}

// Get wishlist items
$wishlist = $conn->query("
    SELECT w.*, p.product_name, p.price, p.image_path 
    FROM wishlist w
    JOIN products p ON w.product_id = p.product_id
    WHERE w.user_id = $user_id
");
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<div class="wishlist-page">
    <h1 class="wishlist-title">❤️ My Wishlist</h1>
    
    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php 
            echo $_SESSION['success']; 
            unset($_SESSION['success']); 
        ?></div>
    <?php endif; ?>
    
    <?php if ($wishlist->num_rows > 0): ?>
        <div class="wishlist-grid">
            <?php while($item = $wishlist->fetch_assoc()): ?>
                <div class="wishlist-card">
                    <div class="wishlist-image">
                        <a href="product.php?id=<?php echo $item['product_id']; ?>">
                            <?php if(!empty($item['image_path']) && file_exists(__DIR__ . '/' . $item['image_path'])): ?>
                                <img src="/echo-ember-guitars/<?php echo $item['image_path']; ?>" alt="<?php echo $item['product_name']; ?>">
                            <?php else: ?>
                                <div class="placeholder-image-wishlist">🎸</div>
                            <?php endif; ?>
                        </a>
                    </div>
                    
                    <div class="wishlist-details">
                        <h3><a href="product.php?id=<?php echo $item['product_id']; ?>"><?php echo $item['product_name']; ?></a></h3>
                        <div class="wishlist-price">₱<?php echo number_format($item['price'], 2); ?></div>
                        
                        <div class="wishlist-actions">
                            <a href="cart/add_to_cart.php?product_id=<?php echo $item['product_id']; ?>" class="btn-wishlist-cart">
                                🛒 Add to Cart
                            </a>
                            <a href="wishlist.php?remove=<?php echo $item['wishlist_id']; ?>" class="btn-wishlist-remove" onclick="return confirm('Remove from wishlist?')">
                                ✕
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="empty-wishlist">
            <div class="empty-emoji">❤️</div>
            <h2>Your wishlist is empty</h2>
            <p>Save items you love here!</p>
            <a href="index.php#products" class="btn btn-primary">Browse Products</a>
        </div>
    <?php endif; ?>
</div>

<style>
.wishlist-page {
    max-width: 1200px;
    margin: 40px auto;
    padding: 0 20px;
}

.wishlist-title {
    font-size: 2rem;
    color: #333;
    margin-bottom: 30px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.wishlist-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 25px;
}

.wishlist-card {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    transition: all 0.3s;
    border: 1px solid #f0f0f0;
}

.wishlist-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.wishlist-image {
    height: 200px;
    overflow: hidden;
    background: #f8f9fa;
}

.wishlist-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.placeholder-image-wishlist {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    background: linear-gradient(135deg, #ff6b6b 0%, #ff8e8e 100%);
    color: white;
}

.wishlist-details {
    padding: 20px;
}

.wishlist-details h3 {
    margin-bottom: 10px;
}

.wishlist-details h3 a {
    color: #333;
    text-decoration: none;
}

.wishlist-details h3 a:hover {
    color: #667eea;
}

.wishlist-price {
    font-size: 1.2rem;
    font-weight: 700;
    color: #ff6b6b;
    margin-bottom: 15px;
}

.wishlist-actions {
    display: flex;
    gap: 10px;
}

.btn-wishlist-cart {
    flex: 1;
    padding: 10px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    text-decoration: none;
    text-align: center;
    border-radius: 6px;
    transition: all 0.3s;
}

.btn-wishlist-cart:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
}

.btn-wishlist-remove {
    width: 40px;
    height: 40px;
    background: #ff6b6b;
    color: white;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    transition: all 0.3s;
}

.btn-wishlist-remove:hover {
    background: #ff5252;
    transform: scale(1.1);
}

.empty-wishlist {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 15px;
}

.empty-emoji {
    font-size: 4rem;
    margin-bottom: 20px;
    opacity: 0.7;
}

.empty-wishlist h2 {
    color: #333;
    margin-bottom: 10px;
}

.empty-wishlist p {
    color: #666;
    margin-bottom: 25px;
}

/* Dark mode support */
body.dark-mode .wishlist-card {
    background: #16213e;
    border-color: #0f3460;
}

body.dark-mode .wishlist-details h3 a {
    color: #fff;
}

body.dark-mode .empty-wishlist {
    background: #16213e;
}

body.dark-mode .empty-wishlist h2,
body.dark-mode .empty-wishlist p {
    color: #fff;
}

@media (max-width: 768px) {
    .wishlist-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include __DIR__ . '/includes/footer.php'; ?>