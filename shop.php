<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

// Pagination settings
$products_per_page = 12;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $products_per_page;

// Get filter parameters
$category = isset($_GET['category']) ? $_GET['category'] : '';
$min_price = isset($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
$max_price = isset($_GET['max_price']) ? (float)$_GET['max_price'] : 999999;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$in_stock = isset($_GET['in_stock']) ? true : false;

// Build base query
$sql = "SELECT * FROM products WHERE 1=1";
$count_sql = "SELECT COUNT(*) as total FROM products WHERE 1=1";
$params = [];
$types = "";

// Add search condition
if (!empty($search)) {
    $sql .= " AND (product_name LIKE ? OR description LIKE ? OR category LIKE ?)";
    $count_sql .= " AND (product_name LIKE ? OR description LIKE ? OR category LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= "sss";
}

// Add category filter
if (!empty($category)) {
    $sql .= " AND category = ?";
    $count_sql .= " AND category = ?";
    $params[] = $category;
    $types .= "s";
}

// Add price filters
if ($min_price > 0) {
    $sql .= " AND price >= ?";
    $count_sql .= " AND price >= ?";
    $params[] = $min_price;
    $types .= "d";
}

if ($max_price < 999999) {
    $sql .= " AND price <= ?";
    $count_sql .= " AND price <= ?";
    $params[] = $max_price;
    $types .= "d";
}

// Add stock filter
if ($in_stock) {
    $sql .= " AND stock > 0";
    $count_sql .= " AND stock > 0";
}

// Add sorting
switch($sort) {
    case 'price_low':
        $sql .= " ORDER BY price ASC";
        break;
    case 'price_high':
        $sql .= " ORDER BY price DESC";
        break;
    case 'name_asc':
        $sql .= " ORDER BY product_name ASC";
        break;
    case 'name_desc':
        $sql .= " ORDER BY product_name DESC";
        break;
    default: // newest
        $sql .= " ORDER BY created_at DESC";
}

// Add pagination
$sql .= " LIMIT ? OFFSET ?";
$params[] = $products_per_page;
$params[] = $offset;
$types .= "ii";

// Prepare and execute main query
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$products = $stmt->get_result();

// Get total count for pagination
$count_stmt = $conn->prepare($count_sql);
if (!empty($params) && count($params) > 2) {
    // Remove pagination params for count query
    $count_params = array_slice($params, 0, -2);
    $count_types = substr($types, 0, -2);
    if (!empty($count_params)) {
        $count_stmt->bind_param($count_types, ...$count_params);
    }
}
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_products = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_products / $products_per_page);

// Get all categories for filter
$categories = $conn->query("SELECT DISTINCT category, COUNT(*) as count FROM products GROUP BY category ORDER BY category");

// Get min and max prices for range slider
$price_range = $conn->query("SELECT MIN(price) as min_price, MAX(price) as max_price FROM products")->fetch_assoc();
$global_min = floor($price_range['min_price']);
$global_max = ceil($price_range['max_price']);
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<div class="shop-page">
    <div class="container">
        <!-- Page Header -->
        <div class="shop-header">
            <h1 class="shop-title">
                <?php if (!empty($search)): ?>
                    Search Results for "<?php echo htmlspecialchars($search); ?>"
                <?php else: ?>
                    All Products
                <?php endif; ?>
            </h1>
            <p class="shop-results"><?php echo $total_products; ?> products found</p>
        </div>

        <div class="shop-layout">
            <!-- FILTER SIDEBAR -->
            <aside class="filter-sidebar">
                <h3 class="filter-title">Filter Products</h3>
                
                <form method="GET" action="shop.php" id="filterForm">
                    <!-- Search Box (if not already searching) -->
                    <?php if (empty($search)): ?>
                    <div class="filter-section">
                        <h4>🔍 Search</h4>
                        <input type="text" 
                               name="search" 
                               placeholder="What are you looking for?" 
                               value="<?php echo htmlspecialchars($search); ?>"
                               class="filter-input">
                    </div>
                    <?php endif; ?>

                    <!-- Categories -->
                    <div class="filter-section">
                        <h4>📂 Categories</h4>
                        <div class="category-list">
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['category' => '', 'page' => 1])); ?>" 
                               class="category-item <?php echo empty($category) ? 'active' : ''; ?>">
                                All Categories
                                <span class="category-count">(<?php echo $conn->query("SELECT COUNT(*) as total FROM products")->fetch_assoc()['total']; ?>)</span>
                            </a>
                            <?php while($cat = $categories->fetch_assoc()): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['category' => $cat['category'], 'page' => 1])); ?>" 
                                   class="category-item <?php echo $category == $cat['category'] ? 'active' : ''; ?>">
                                    <?php 
                                    $icons = [
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
                                    echo ($icons[$cat['category']] ?? '📦') . ' ' . $cat['category'];
                                    ?>
                                    <span class="category-count">(<?php echo $cat['count']; ?>)</span>
                                </a>
                            <?php endwhile; ?>
                        </div>
                    </div>

                    <!-- Price Range -->
                    <div class="filter-section">
                        <h4>💰 Price Range</h4>
                        <div class="price-range-container">
                            <div class="price-inputs">
                                <input type="number" 
                                       name="min_price" 
                                       placeholder="Min" 
                                       value="<?php echo $min_price > 0 ? $min_price : ''; ?>"
                                       min="<?php echo $global_min; ?>"
                                       max="<?php echo $global_max; ?>"
                                       class="price-input">
                                <span class="price-separator">—</span>
                                <input type="number" 
                                       name="max_price" 
                                       placeholder="Max" 
                                       value="<?php echo $max_price < 999999 ? $max_price : ''; ?>"
                                       min="<?php echo $global_min; ?>"
                                       max="<?php echo $global_max; ?>"
                                       class="price-input">
                            </div>
                            <div class="price-range-slider">
                                <input type="range" 
                                       id="minSlider" 
                                       min="<?php echo $global_min; ?>" 
                                       max="<?php echo $global_max; ?>" 
                                       value="<?php echo $min_price > 0 ? $min_price : $global_min; ?>"
                                       class="price-slider">
                                <input type="range" 
                                       id="maxSlider" 
                                       min="<?php echo $global_min; ?>" 
                                       max="<?php echo $global_max; ?>" 
                                       value="<?php echo $max_price < 999999 ? $max_price : $global_max; ?>"
                                       class="price-slider">
                            </div>
                        </div>
                    </div>

                    <!-- Stock Filter -->
                    <div class="filter-section">
                        <label class="checkbox-label">
                            <input type="checkbox" 
                                   name="in_stock" 
                                   value="1" 
                                   <?php echo $in_stock ? 'checked' : ''; ?>
                                   onchange="this.form.submit()">
                            <span>In Stock Only</span>
                        </label>
                    </div>

                    <!-- Sort Options -->
                    <div class="filter-section">
                        <h4>📊 Sort By</h4>
                        <select name="sort" class="sort-select" onchange="this.form.submit()">
                            <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Newest First</option>
                            <option value="price_low" <?php echo $sort == 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                            <option value="price_high" <?php echo $sort == 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                            <option value="name_asc" <?php echo $sort == 'name_asc' ? 'selected' : ''; ?>>Name: A to Z</option>
                            <option value="name_desc" <?php echo $sort == 'name_desc' ? 'selected' : ''; ?>>Name: Z to A</option>
                        </select>
                    </div>

                    <!-- Apply/Clear Buttons -->
                    <div class="filter-actions">
                        <button type="submit" class="btn-apply-filters">Apply Filters</button>
                        <a href="shop.php" class="btn-clear-filters">Clear All</a>
                    </div>
                </form>
            </aside>

            <!-- PRODUCTS GRID -->
            <main class="products-main">
                <?php if ($products->num_rows > 0): ?>
                    <div class="products-grid">
                        <?php while($product = $products->fetch_assoc()): ?>
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

                                <!-- Sale Badge (if price > 500) -->
                                <?php if($product['price'] > 500): ?>
                                    <div class="sale-badge">SALE</div>
                                <?php endif; ?>

                                <!-- Product Image -->
                                <a href="/echo-ember-guitars/product.php?id=<?php echo $product['product_id']; ?>" class="product-image-link">
                                    <div class="product-image">
                                        <?php if(!empty($product['image_path']) && file_exists(__DIR__ . '/' . $product['image_path'])): ?>
                                            <img src="/echo-ember-guitars/<?php echo $product['image_path']; ?>" alt="<?php echo $product['product_name']; ?>">
                                        <?php else: ?>
                                            <div class="placeholder-image">🎸</div>
                                        <?php endif; ?>
                                    </div>
                                </a>
                                
                                <div class="product-info">
                                    <h3>
                                        <a href="/echo-ember-guitars/product.php?id=<?php echo $product['product_id']; ?>">
                                            <?php echo $product['product_name']; ?>
                                        </a>
                                    </h3>
                                    <p class="product-category"><?php echo $product['category']; ?></p>
                                    <p class="product-description"><?php echo substr($product['description'], 0, 60); ?>...</p>
                                    
                                    <div class="product-price-section">
                                        <?php if($product['price'] > 500): ?>
                                            <span class="original-price">₱<?php echo number_format($product['price'] * 1.1, 2); ?></span>
                                            <span class="sale-price">₱<?php echo number_format($product['price'], 2); ?></span>
                                        <?php else: ?>
                                            <span class="regular-price">₱<?php echo number_format($product['price'], 2); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="product-stock <?php echo $product['stock'] > 0 ? 'in-stock' : 'out-of-stock'; ?>">
                                        <?php if($product['stock'] > 0): ?>
                                            ✓ <?php echo $product['stock']; ?> in stock
                                        <?php else: ?>
                                            ✗ Out of stock
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if (isset($_SESSION['user_id'])): ?>
                                        <form action="/echo-ember-guitars/cart/add_to_cart.php" method="POST" class="add-to-cart-form">
                                            <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                                            <input type="number" name="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>" class="quantity-input" <?php echo $product['stock'] == 0 ? 'disabled' : ''; ?>>
                                            <button type="submit" class="btn-add-to-cart" <?php echo $product['stock'] == 0 ? 'disabled' : ''; ?>>
                                                Add to Cart
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <a href="/echo-ember-guitars/user/login.php" class="btn-add-to-cart">Login to Buy</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>

                    <!-- PAGINATION -->
                    <?php if ($total_pages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" class="page-link prev">← Previous</a>
                            <?php endif; ?>

                            <?php for($i = 1; $i <= $total_pages; $i++): ?>
                                <?php if ($i == $page): ?>
                                    <span class="page-link active"><?php echo $i; ?></span>
                                <?php else: ?>
                                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" class="page-link"><?php echo $i; ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>

                            <?php if ($page < $total_pages): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" class="page-link next">Next →</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                <?php else: ?>
                    <div class="no-products-found">
                        <div class="no-products-emoji">🔍</div>
                        <h2>No products found</h2>
                        <p>Try adjusting your filters or search term</p>
                        <a href="shop.php" class="btn-clear-all">Clear All Filters</a>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
</div>

<style>
/* ===== SHOP PAGE STYLES ===== */
.shop-page {
    padding: 40px 0;
    background: #f8f9fa;
    min-height: 100vh;
}

/* Header */
.shop-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    background: white;
    padding: 20px 25px;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.shop-title {
    font-size: 1.8rem;
    color: #333;
    margin: 0;
}

.shop-results {
    color: #666;
    font-size: 1rem;
    margin: 0;
}

/* Layout */
.shop-layout {
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 30px;
    align-items: start;
}

/* Filter Sidebar */
.filter-sidebar {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    position: sticky;
    top: 100px;
}

.filter-title {
    font-size: 1.2rem;
    color: #333;
    margin: 0 0 20px 0;
    padding-bottom: 15px;
    border-bottom: 2px solid #f0f0f0;
}

.filter-section {
    margin-bottom: 25px;
}

.filter-section h4 {
    font-size: 1rem;
    color: #666;
    margin: 0 0 12px 0;
}

.filter-input {
    width: 100%;
    padding: 10px 12px;
    border: 2px solid #e1e1e1;
    border-radius: 8px;
    font-size: 0.95rem;
    transition: all 0.3s;
}

.filter-input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

/* Category List */
.category-list {
    max-height: 300px;
    overflow-y: auto;
    padding-right: 10px;
}

.category-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 12px;
    border-radius: 6px;
    color: #666;
    text-decoration: none;
    transition: all 0.3s;
    font-size: 0.95rem;
    margin-bottom: 2px;
}

.category-item:hover {
    background: #f5f5f5;
    color: #667eea;
}

.category-item.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.category-count {
    color: #999;
    font-size: 0.85rem;
}

.category-item.active .category-count {
    color: rgba(255,255,255,0.8);
}

/* Price Range */
.price-range-container {
    margin-bottom: 15px;
}

.price-inputs {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 15px;
}

.price-input {
    flex: 1;
    padding: 8px;
    border: 2px solid #e1e1e1;
    border-radius: 6px;
    font-size: 0.95rem;
}

.price-separator {
    color: #999;
}

.price-range-slider {
    position: relative;
    height: 30px;
}

.price-slider {
    position: absolute;
    width: 100%;
    height: 5px;
    background: none;
    pointer-events: none;
    -webkit-appearance: none;
    appearance: none;
}

.price-slider::-webkit-slider-thumb {
    pointer-events: auto;
    -webkit-appearance: none;
    appearance: none;
    width: 18px;
    height: 18px;
    background: #667eea;
    border-radius: 50%;
    cursor: pointer;
    margin-top: -6px;
}

.price-slider::-moz-range-thumb {
    pointer-events: auto;
    width: 18px;
    height: 18px;
    background: #667eea;
    border-radius: 50%;
    cursor: pointer;
}

.price-slider::-webkit-slider-runnable-track {
    width: 100%;
    height: 5px;
    background: #e1e1e1;
    border-radius: 5px;
}

/* Checkbox */
.checkbox-label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    color: #666;
}

.checkbox-label input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

/* Sort Select */
.sort-select {
    width: 100%;
    padding: 10px;
    border: 2px solid #e1e1e1;
    border-radius: 8px;
    font-size: 0.95rem;
    background: white;
    cursor: pointer;
}

/* Filter Actions */
.filter-actions {
    display: flex;
    gap: 10px;
    margin-top: 20px;
}

.btn-apply-filters {
    flex: 1;
    padding: 12px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-apply-filters:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
}

.btn-clear-filters {
    flex: 1;
    padding: 12px;
    background: #f5f5f5;
    color: #666;
    text-align: center;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s;
}

.btn-clear-filters:hover {
    background: #e0e0e0;
}

/* Products Grid */
.products-main {
    min-height: 400px;
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 25px;
    margin-bottom: 40px;
}

/* Product Card Enhancements */
.sale-badge {
    position: absolute;
    top: 10px;
    left: 10px;
    background: #ff6b6b;
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    z-index: 10;
    box-shadow: 0 2px 10px rgba(255, 107, 107, 0.3);
}

.original-price {
    text-decoration: line-through;
    color: #999;
    font-size: 0.9rem;
    margin-right: 8px;
}

.sale-price {
    color: #ff6b6b;
    font-weight: 600;
    font-size: 1.1rem;
}

.regular-price {
    font-weight: 600;
    font-size: 1.1rem;
    color: #333;
}

.product-stock {
    font-size: 0.85rem;
    margin: 8px 0;
}

.product-stock.in-stock {
    color: #28a745;
}

.product-stock.out-of-stock {
    color: #dc3545;
}

/* Pagination */
.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 8px;
    margin-top: 40px;
}

.page-link {
    min-width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 10px;
    border: 2px solid #e1e1e1;
    border-radius: 8px;
    color: #666;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s;
    background: white;
}

.page-link:hover {
    border-color: #667eea;
    color: #667eea;
}

.page-link.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-color: transparent;
}

.page-link.prev,
.page-link.next {
    padding: 0 15px;
}

/* No Products Found */
.no-products-found {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.no-products-emoji {
    font-size: 4rem;
    margin-bottom: 20px;
    opacity: 0.7;
}

.no-products-found h2 {
    color: #333;
    margin-bottom: 10px;
}

.no-products-found p {
    color: #666;
    margin-bottom: 25px;
}

.btn-clear-all {
    display: inline-block;
    padding: 12px 30px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s;
}

.btn-clear-all:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
}

/* Dark Mode Support */
body.dark-mode .shop-page {
    background: #1a1a2e;
}

body.dark-mode .shop-header,
body.dark-mode .filter-sidebar,
body.dark-mode .no-products-found {
    background: #16213e;
    border-color: #0f3460;
}

body.dark-mode .shop-title,
body.dark-mode .filter-title,
body.dark-mode .no-products-found h2 {
    color: #fff;
}

body.dark-mode .shop-results,
body.dark-mode .filter-section h4,
body.dark-mode .category-item,
body.dark-mode .no-products-found p {
    color: #b0b0b0;
}

body.dark-mode .filter-input,
body.dark-mode .price-input,
body.dark-mode .sort-select {
    background: #0f3460;
    color: #fff;
    border-color: #1a1a2e;
}

body.dark-mode .checkbox-label {
    color: #b0b0b0;
}

body.dark-mode .page-link {
    background: #16213e;
    border-color: #0f3460;
    color: #b0b0b0;
}

body.dark-mode .page-link:hover {
    border-color: #667eea;
    color: #667eea;
}

/* Responsive */
@media (max-width: 992px) {
    .shop-layout {
        grid-template-columns: 1fr;
    }
    
    .filter-sidebar {
        position: static;
        margin-bottom: 20px;
    }
}

@media (max-width: 768px) {
    .shop-header {
        flex-direction: column;
        text-align: center;
        gap: 10px;
    }
    
    .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    }
}

@media (max-width: 480px) {
    .products-grid {
        grid-template-columns: 1fr;
    }
    
    .filter-actions {
        flex-direction: column;
    }
}
</style>

<script>
// Price range slider sync
document.addEventListener('DOMContentLoaded', function() {
    const minSlider = document.getElementById('minSlider');
    const maxSlider = document.getElementById('maxSlider');
    const minInput = document.querySelector('input[name="min_price"]');
    const maxInput = document.querySelector('input[name="max_price"]');
    
    if (minSlider && maxSlider) {
        function updateMinInput() {
            let minVal = parseInt(minSlider.value);
            let maxVal = parseInt(maxSlider.value);
            
            if (minVal > maxVal) {
                minSlider.value = maxVal;
                minVal = maxVal;
            }
            
            if (minInput) minInput.value = minVal;
        }
        
        function updateMaxInput() {
            let minVal = parseInt(minSlider.value);
            let maxVal = parseInt(maxSlider.value);
            
            if (maxVal < minVal) {
                maxSlider.value = minVal;
                maxVal = minVal;
            }
            
            if (maxInput) maxInput.value = maxVal;
        }
        
        minSlider.addEventListener('input', updateMinInput);
        maxSlider.addEventListener('input', updateMaxInput);
    }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>