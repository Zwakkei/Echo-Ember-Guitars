<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

// Get product ID from URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id === 0) {
    header('Location: index.php');
    exit();
}

// Fetch product details
$stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: index.php');
    exit();
}

$product = $result->fetch_assoc();

// Get related products (same category, exclude current product)
$related_sql = "SELECT * FROM products WHERE category = ? AND product_id != ? LIMIT 4";
$related_stmt = $conn->prepare($related_sql);
$related_stmt->bind_param("si", $product['category'], $product_id);
$related_stmt->execute();
$related_products = $related_stmt->get_result();

// Check if product is in wishlist
$in_wishlist = false;
if (isset($_SESSION['user_id'])) {
    $wish_check = $conn->prepare("SELECT * FROM wishlist WHERE user_id = ? AND product_id = ?");
    $wish_check->bind_param("ii", $_SESSION['user_id'], $product_id);
    $wish_check->execute();
    $in_wishlist = $wish_check->get_result()->num_rows > 0;
}
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<div class="product-page">
    <div class="container">
        <!-- Breadcrumb navigation -->
        <div class="breadcrumb">
            <a href="index.php">Home</a> > 
            <a href="index.php#products">Products</a> > 
            <span><?php echo $product['product_name']; ?></span>
        </div>

        <div class="product-details-container">
            <!-- LEFT COLUMN: Image Gallery -->
            <div class="product-gallery">
                <div class="main-image-container">
                    <?php if(!empty($product['image_path']) && file_exists(__DIR__ . '/' . $product['image_path'])): ?>
                        <img src="/echo-ember-guitars/<?php echo $product['image_path']; ?>" 
                             alt="<?php echo $product['product_name']; ?>" 
                             id="mainProductImage"
                             class="main-product-image">
                    <?php else: ?>
                        <div class="main-placeholder">🎸</div>
                    <?php endif; ?>
                </div>
                
                <!-- Thumbnail Gallery (you can add more images to database later) -->
                <div class="thumbnail-gallery">
                    <div class="thumbnail active" onclick="changeImage(this, '<?php echo $product['image_path']; ?>')">
                        <?php if(!empty($product['image_path'])): ?>
                            <img src="/echo-ember-guitars/<?php echo $product['image_path']; ?>" alt="Thumbnail">
                        <?php else: ?>
                            <div class="thumbnail-placeholder">🎸</div>
                        <?php endif; ?>
                    </div>
                    <!-- Add more thumbnails here if you have multiple images -->
                </div>
            </div>

            <!-- RIGHT COLUMN: Product Info -->
            <div class="product-info-column">
                <h1 class="product-title"><?php echo $product['product_name']; ?></h1>
                
                <div class="product-meta">
                    <span class="product-category-badge"><?php echo $product['category']; ?></span>
                    <div class="product-rating">
                        <span class="stars">★★★★★</span>
                        <span class="review-count">(12 reviews)</span>
                    </div>
                </div>

                <div class="product-price-large">₱<?php echo number_format($product['price'], 2); ?></div>
                
                <div class="product-availability">
                    <?php if($product['stock'] > 0): ?>
                        <div class="in-stock">
                            <span class="stock-icon">✓</span>
                            In Stock (<?php echo $product['stock']; ?> available)
                        </div>
                    <?php else: ?>
                        <div class="out-of-stock">
                            <span class="stock-icon">✗</span>
                            Out of Stock
                        </div>
                    <?php endif; ?>
                </div>

                <div class="product-description-full">
                    <h3>Product Description</h3>
                    <p><?php echo nl2br($product['description']); ?></p>
                </div>

                <!-- Action Buttons -->
                <div class="product-actions">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <!-- Add to Cart Form -->
                        <form action="/echo-ember-guitars/cart/add_to_cart.php" method="POST" class="add-to-cart-large-form" id="addToCartForm">
                            <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                            
                            <div class="quantity-selector-large">
                                <label for="quantity">Quantity:</label>
                                <div class="quantity-controls">
                                    <button type="button" class="qty-btn" onclick="decrementQuantity()">−</button>
                                    <input type="number" 
                                           id="quantity" 
                                           name="quantity" 
                                           value="1" 
                                           min="1" 
                                           max="<?php echo $product['stock']; ?>"
                                           class="quantity-input-large"
                                           readonly>
                                    <button type="button" class="qty-btn" onclick="incrementQuantity(<?php echo $product['stock']; ?>)">+</button>
                                </div>
                            </div>

                            <button type="submit" class="btn-add-to-cart-large" <?php echo $product['stock'] == 0 ? 'disabled' : ''; ?>>
                                🛒 Add to Cart
                            </button>
                        </form>

                        <!-- Wishlist Button -->
                        <?php if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'): ?>
                            <form action="/echo-ember-guitars/wishlist.php" method="POST" class="wishlist-large-form">
                                <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                                <button type="submit" name="add_to_wishlist" class="btn-wishlist-large <?php echo $in_wishlist ? 'in-wishlist' : ''; ?>">
                                    <?php echo $in_wishlist ? '❤️ In Wishlist' : '♡ Add to Wishlist'; ?>
                                </button>
                            </form>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="login-to-purchase">
                            <p>Please <a href="/echo-ember-guitars/user/login.php">login</a> to purchase this item.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Product Highlights -->
                <div class="product-highlights">
                    <h3>Key Features</h3>
                    <ul class="highlights-list">
                        <li>✓ High-quality materials</li>
                        <li>✓ Professional grade</li>
                        <li>✓ 1-year warranty</li>
                        <li>✓ Free shipping</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Related Products Section -->
        <?php if ($related_products->num_rows > 0): ?>
        <div class="related-products-section">
            <h2 class="related-title">You May Also Like</h2>
            <div class="related-products-grid">
                <?php while($related = $related_products->fetch_assoc()): ?>
                    <div class="related-product-card">
                        <a href="product.php?id=<?php echo $related['product_id']; ?>">
                            <div class="related-product-image">
                                <?php if(!empty($related['image_path']) && file_exists(__DIR__ . '/' . $related['image_path'])): ?>
                                    <img src="/echo-ember-guitars/<?php echo $related['image_path']; ?>" alt="<?php echo $related['product_name']; ?>">
                                <?php else: ?>
                                    <div class="related-placeholder">🎸</div>
                                <?php endif; ?>
                            </div>
                            <h4><?php echo $related['product_name']; ?></h4>
                            <div class="related-product-price">₱<?php echo number_format($related['price'], 2); ?></div>
                        </a>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
/* ===== PRODUCT PAGE STYLES ===== */
.product-page {
    padding: 40px 0;
    background: #f8f9fa;
    min-height: 100vh;
}

/* Breadcrumb */
.breadcrumb {
    margin-bottom: 30px;
    color: #666;
    font-size: 0.95rem;
}

.breadcrumb a {
    color: #667eea;
    text-decoration: none;
}

.breadcrumb a:hover {
    text-decoration: underline;
}

.breadcrumb span {
    color: #333;
    font-weight: 500;
}

/* Main Product Container */
.product-details-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 50px;
    background: white;
    padding: 40px;
    border-radius: 20px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    margin-bottom: 50px;
}

/* Gallery Styles */
.product-gallery {
    position: sticky;
    top: 100px;
    height: fit-content;
}

.main-image-container {
    width: 100%;
    height: 400px;
    background: #f8f9fa;
    border-radius: 15px;
    overflow: hidden;
    margin-bottom: 15px;
    border: 2px solid #f0f0f0;
    display: flex;
    align-items: center;
    justify-content: center;
}

.main-product-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.main-placeholder {
    font-size: 5rem;
    color: #999;
}

.thumbnail-gallery {
    display: flex;
    gap: 10px;
}

.thumbnail {
    width: 80px;
    height: 80px;
    border-radius: 8px;
    overflow: hidden;
    cursor: pointer;
    border: 2px solid transparent;
    transition: all 0.3s;
    background: #f8f9fa;
}

.thumbnail.active {
    border-color: #667eea;
    transform: scale(1.05);
}

.thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.thumbnail-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    background: #f0f0f0;
}

/* Product Info Column */
.product-info-column {
    padding: 20px 0;
}

.product-title {
    font-size: 2rem;
    color: #333;
    margin-bottom: 15px;
}

.product-meta {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 20px;
}

.product-category-badge {
    background: #f0f4ff;
    color: #667eea;
    padding: 5px 15px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.9rem;
}

.product-rating {
    display: flex;
    align-items: center;
    gap: 5px;
}

.stars {
    color: #ffc107;
    font-size: 1.1rem;
}

.review-count {
    color: #666;
    font-size: 0.9rem;
}

.product-price-large {
    font-size: 2.5rem;
    font-weight: 700;
    color: #ff6b6b;
    margin-bottom: 20px;
}

/* Availability */
.product-availability {
    margin-bottom: 25px;
}

.in-stock {
    color: #28a745;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
}

.out-of-stock {
    color: #dc3545;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
}

.stock-icon {
    font-size: 1.2rem;
}

/* Description */
.product-description-full {
    margin-bottom: 30px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 10px;
}

.product-description-full h3 {
    margin-bottom: 15px;
    color: #333;
}

.product-description-full p {
    color: #666;
    line-height: 1.8;
}

/* Action Buttons */
.product-actions {
    margin-bottom: 30px;
}

.add-to-cart-large-form {
    margin-bottom: 15px;
}

.quantity-selector-large {
    margin-bottom: 15px;
}

.quantity-selector-large label {
    display: block;
    margin-bottom: 8px;
    color: #333;
    font-weight: 600;
}

.quantity-controls {
    display: inline-flex;
    align-items: center;
    border: 2px solid #e1e1e1;
    border-radius: 8px;
    overflow: hidden;
    background: white;
}

.qty-btn {
    width: 40px;
    height: 40px;
    background: #f8f9fa;
    border: none;
    font-size: 1.2rem;
    cursor: pointer;
    transition: all 0.3s;
}

.qty-btn:hover {
    background: #667eea;
    color: white;
}

.quantity-input-large {
    width: 60px;
    height: 40px;
    border: none;
    border-left: 2px solid #e1e1e1;
    border-right: 2px solid #e1e1e1;
    text-align: center;
    font-size: 1rem;
    font-weight: 600;
}

.btn-add-to-cart-large {
    width: 100%;
    padding: 15px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-add-to-cart-large:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
}

.btn-add-to-cart-large:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.btn-wishlist-large {
    width: 100%;
    padding: 12px;
    background: white;
    border: 2px solid #ff6b6b;
    color: #ff6b6b;
    border-radius: 8px;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-wishlist-large:hover {
    background: #ff6b6b;
    color: white;
}

.btn-wishlist-large.in-wishlist {
    background: #ff6b6b;
    color: white;
}

.login-to-purchase {
    text-align: center;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
}

.login-to-purchase a {
    color: #667eea;
    font-weight: 600;
    text-decoration: none;
}

.login-to-purchase a:hover {
    text-decoration: underline;
}

/* Product Highlights */
.product-highlights {
    margin-top: 30px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 10px;
}

.product-highlights h3 {
    margin-bottom: 15px;
    color: #333;
}

.highlights-list {
    list-style: none;
    padding: 0;
}

.highlights-list li {
    margin-bottom: 10px;
    color: #666;
}

/* Related Products */
.related-products-section {
    margin-top: 50px;
}

.related-title {
    font-size: 1.8rem;
    color: #333;
    margin-bottom: 30px;
    text-align: center;
}

.related-products-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
}

.related-product-card {
    background: white;
    border-radius: 12px;
    padding: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    transition: all 0.3s;
    text-align: center;
}

.related-product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

.related-product-card a {
    text-decoration: none;
    color: inherit;
}

.related-product-image {
    height: 150px;
    margin-bottom: 15px;
    border-radius: 8px;
    overflow: hidden;
    background: #f8f9fa;
}

.related-product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.related-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    background: #f0f0f0;
}

.related-product-card h4 {
    font-size: 1rem;
    margin-bottom: 8px;
    color: #333;
}

.related-product-price {
    color: #ff6b6b;
    font-weight: 600;
    font-size: 1.1rem;
}

/* Dark Mode Support */
body.dark-mode .product-page {
    background: #1a1a2e;
}

body.dark-mode .product-details-container,
body.dark-mode .related-product-card {
    background: #16213e;
    border-color: #0f3460;
}

body.dark-mode .product-title,
body.dark-mode .product-description-full h3,
body.dark-mode .product-highlights h3,
body.dark-mode .related-title {
    color: #fff;
}

body.dark-mode .product-description-full,
body.dark-mode .product-highlights,
body.dark-mode .login-to-purchase {
    background: #0f3460;
}

body.dark-mode .product-description-full p,
body.dark-mode .highlights-list li {
    color: #b0b0b0;
}

body.dark-mode .quantity-controls {
    background: #0f3460;
    border-color: #1a1a2e;
}

body.dark-mode .quantity-input-large {
    background: #0f3460;
    color: #fff;
}

body.dark-mode .btn-wishlist-large {
    background: #16213e;
}

body.dark-mode .related-product-card h4 {
    color: #fff;
}

/* Responsive */
@media (max-width: 992px) {
    .related-products-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .product-details-container {
        grid-template-columns: 1fr;
        gap: 30px;
        padding: 20px;
    }
    
    .product-gallery {
        position: static;
    }
    
    .main-image-container {
        height: 300px;
    }
    
    .product-title {
        font-size: 1.5rem;
    }
    
    .product-price-large {
        font-size: 2rem;
    }
    
    .related-products-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 480px) {
    .product-meta {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .quantity-controls {
        width: 100%;
    }
    
    .qty-btn,
    .quantity-input-large {
        height: 45px;
    }
}
</style>

<script>
function changeImage(thumbnail, imagePath) {
    const mainImage = document.getElementById('mainProductImage');
    if (mainImage && imagePath) {
        mainImage.src = '/echo-ember-guitars/' + imagePath;
    }
    
    // Update active thumbnail
    document.querySelectorAll('.thumbnail').forEach(thumb => {
        thumb.classList.remove('active');
    });
    thumbnail.classList.add('active');
}

function decrementQuantity() {
    const input = document.getElementById('quantity');
    if (input) {
        let val = parseInt(input.value);
        if (val > 1) {
            input.value = val - 1;
        }
    }
}

function incrementQuantity(max) {
    const input = document.getElementById('quantity');
    if (input) {
        let val = parseInt(input.value);
        if (val < max) {
            input.value = val + 1;
        }
    }
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>