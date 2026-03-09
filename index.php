<?php
// Turn on error reporting (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

// Fetch products
$sql = "SELECT * FROM products WHERE stock > 0 ORDER BY created_at DESC";
$result = $conn->query($sql);

// Store products in array
$products = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}
$product_count = count($products);
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<section class="hero">
    <div class="container">
        <h1>Welcome to Echo & Ember Guitars</h1>
        <p>Discover the perfect guitar for your musical journey</p>
        <a href="#products" class="btn btn-primary">Shop Now</a>
    </div>
</section>

<section id="products" class="products-section">
    <div class="container">
        <h2 class="section-title">Our Guitars</h2>
        
        <?php if ($product_count > 0): ?>
            <div class="products-grid">
                <?php foreach($products as $product): ?>
                    <div class="product-card">
                        <!-- Wishlist Button -->
                        <?php if (isset($_SESSION['user_id']) && (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin')): ?>
                            <div class="wishlist-button-container">
                                <form action="/echo-ember-guitars/wishlist.php" method="POST">
                                    <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                                    <button type="submit" name="add_to_wishlist" class="btn-wishlist-small" title="Add to Wishlist">
                                        ❤️
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>

                        <!-- Product Image -->
                        <a href="/echo-ember-guitars/product.php?id=<?php echo $product['product_id']; ?>" class="product-image-link">
                            <div class="product-image">
                                <?php 
                                if(!empty($product['image_path']) && file_exists(__DIR__ . '/' . $product['image_path'])): 
                                ?>
                                    <img src="/echo-ember-guitars/<?php echo $product['image_path']; ?>" alt="<?php echo $product['product_name']; ?>">
                                <?php else: ?>
                                    <div class="placeholder-image">🎸</div>
                                <?php endif; ?>
                            </div>
                        </a>
                        
                        <div class="product-info">
                            <h3>
                                <a href="/echo-ember-guitars/product.php?id=<?php echo $product['product_id']; ?>">
                                    <?php echo htmlspecialchars($product['product_name']); ?>
                                </a>
                            </h3>
                            <p class="product-category"><?php echo htmlspecialchars($product['category']); ?></p>
                            <p class="product-description"><?php echo htmlspecialchars(substr($product['description'], 0, 100)); ?>...</p>
                            <div class="product-price">₱<?php echo number_format((float)$product['price'], 2); ?></div>
                            <div class="product-stock <?php echo $product['stock'] > 0 ? 'in-stock' : 'out-of-stock'; ?>">
                                Stock: <?php echo (int)$product['stock']; ?>
                            </div>
                            
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <form action="/echo-ember-guitars/cart/add_to_cart.php" method="POST">
                                    <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                                    <input type="number" name="quantity" value="1" min="1" max="<?php echo (int)$product['stock']; ?>" class="quantity-input" <?php echo $product['stock'] == 0 ? 'disabled' : ''; ?>>
                                    <button type="submit" class="btn btn-add-to-cart" <?php echo $product['stock'] == 0 ? 'disabled' : ''; ?>>
                                        Add to Cart
                                    </button>
                                </form>
                            <?php else: ?>
                                <a href="/echo-ember-guitars/user/login.php" class="btn btn-add-to-cart">Login to Buy</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-products">
                <p>No products available at the moment.</p>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <a href="/echo-ember-guitars/admin/add_product.php" class="btn btn-primary">Add Your First Product</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<style>
/* Wishlist button styles */
.wishlist-button-container {
    position: absolute;
    top: 10px;
    right: 10px;
    z-index: 10;
}

.btn-wishlist-small {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: white;
    border: 2px solid #ff6b6b;
    color: #ff6b6b;
    font-size: 1.2rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.btn-wishlist-small:hover {
    background: #ff6b6b;
    color: white;
    transform: scale(1.1);
}

.product-card {
    position: relative;
}

.product-image-link {
    display: block;
    text-decoration: none;
}

.product-info h3 a {
    color: #333;
    text-decoration: none;
    transition: color 0.3s;
}

.product-info h3 a:hover {
    color: #667eea;
}

.in-stock {
    color: #28a745;
}

.out-of-stock {
    color: #dc3545;
}

.no-products {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 10px;
}

.no-products p {
    font-size: 1.2rem;
    color: #666;
    margin-bottom: 20px;
}

/* Dark mode support */
body.dark-mode .product-info h3 a {
    color: #fff;
}

body.dark-mode .btn-wishlist-small {
    background: #16213e;
    border-color: #ff6b6b;
    color: #ff6b6b;
}

body.dark-mode .btn-wishlist-small:hover {
    background: #ff6b6b;
    color: white;
}

body.dark-mode .no-products {
    background: #16213e;
}

body.dark-mode .no-products p {
    color: #b0b0b0;
}
</style>

<?php include __DIR__ . '/includes/footer.php'; ?>