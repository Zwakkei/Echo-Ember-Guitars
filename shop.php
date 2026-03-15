<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

// Get filter parameters - SIMPLIFIED
$category = isset($_GET['category']) ? $_GET['category'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build base query - SIMPLE VERSION
$sql = "SELECT * FROM products";
$count_sql = "SELECT COUNT(*) as total FROM products";
$where = [];
$params = [];
$types = "";

// Add conditions
if (!empty($search)) {
    $where[] = "(product_name LIKE ? OR description LIKE ? OR category LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= "sss";
}

if (!empty($category)) {
    $where[] = "category = ?";
    $params[] = $category;
    $types .= "s";
}

// Add WHERE clause if needed
if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
    $count_sql .= " WHERE " . implode(" AND ", $where);
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
    default:
        $sql .= " ORDER BY product_id DESC";
        break;
}

// Get total count
$count_stmt = $conn->prepare($count_sql);
if (!empty($params)) {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$total_products = $count_stmt->get_result()->fetch_assoc()['total'];

// Get products
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$products = $stmt->get_result();

// Get categories
$categories = $conn->query("SELECT category, COUNT(*) as count FROM products GROUP BY category ORDER BY category");
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<div class="shop-page">
    <div class="container">
        <!-- Page Header -->
        <div class="shop-header">
            <h1 class="shop-title">
                <?php if (!empty($search)): ?>
                    Search: "<?php echo htmlspecialchars($search); ?>"
                <?php elseif (!empty($category)): ?>
                    <?php echo htmlspecialchars($category); ?>
                <?php else: ?>
                    All Products
                <?php endif; ?>
            </h1>
            <span class="shop-results"><?php echo $total_products; ?> products</span>
        </div>

        <div class="shop-layout">
            <!-- FILTER SIDEBAR -->
            <aside class="filter-sidebar">
                <h3>Filter Products</h3>
                
                <form method="GET" action="shop.php">
                    <!-- Search -->
                    <div class="filter-group">
                        <label>🔍 Search</label>
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search products...">
                    </div>

                    <!-- Categories -->
                    <div class="filter-group">
                        <label>📂 Categories</label>
                        <select name="category" onchange="this.form.submit()">
                            <option value="">All Categories</option>
                            <?php while($cat = $categories->fetch_assoc()): ?>
                                <option value="<?php echo $cat['category']; ?>" <?php echo $category == $cat['category'] ? 'selected' : ''; ?>>
                                    <?php echo $cat['category']; ?> (<?php echo $cat['count']; ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <!-- Sort -->
                    <div class="filter-group">
                        <label>📊 Sort By</label>
                        <select name="sort" onchange="this.form.submit()">
                            <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Newest First</option>
                            <option value="price_low" <?php echo $sort == 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                            <option value="price_high" <?php echo $sort == 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                            <option value="name_asc" <?php echo $sort == 'name_asc' ? 'selected' : ''; ?>>Name: A to Z</option>
                            <option value="name_desc" <?php echo $sort == 'name_desc' ? 'selected' : ''; ?>>Name: Z to A</option>
                        </select>
                    </div>

                    <button type="submit" class="apply-btn">Apply Filters</button>
                    <a href="shop.php" class="clear-btn">Clear All</a>
                </form>
            </aside>

            <!-- PRODUCTS -->
            <main class="products-main">
                <?php if ($products->num_rows > 0): ?>
                    <div class="products-grid">
                        <?php while($product = $products->fetch_assoc()): ?>
                            <div class="product-card">
                                <a href="product.php?id=<?php echo $product['product_id']; ?>" class="product-link">
                                    <div class="product-image">
                                        <?php if(!empty($product['image_path'])): ?>
                                            <img src="/echo-ember-guitars/<?php echo $product['image_path']; ?>" alt="<?php echo $product['product_name']; ?>">
                                        <?php else: ?>
                                            <div class="no-image">🎸</div>
                                        <?php endif; ?>
                                    </div>
                                    <h3><?php echo $product['product_name']; ?></h3>
                                    <p class="category"><?php echo $product['category']; ?></p>
                                    <p class="price">₱<?php echo number_format($product['price'], 2); ?></p>
                                </a>
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <form action="/echo-ember-guitars/cart/add_to_cart.php" method="POST" class="add-to-cart">
                                        <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit">Add to Cart</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="no-products">
                        <h2>No products found</h2>
                        <p>Try adjusting your filters</p>
                        <a href="shop.php" class="clear-btn">Clear Filters</a>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
</div>

<style>
.shop-page {
    padding: 40px 0;
    background: #f8f9fa;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.shop-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.shop-layout {
    display: grid;
    grid-template-columns: 250px 1fr;
    gap: 30px;
}

/* Sidebar */
.filter-sidebar {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    position: sticky;
    top: 100px;
    height: fit-content;
}

.filter-sidebar h3 {
    margin: 0 0 20px 0;
    padding-bottom: 10px;
    border-bottom: 2px solid #f0f0f0;
}

.filter-group {
    margin-bottom: 20px;
}

.filter-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #555;
}

.filter-group input,
.filter-group select {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 1rem;
}

.apply-btn {
    width: 100%;
    padding: 12px;
    background: #667eea;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-weight: 600;
    margin-bottom: 10px;
}

.apply-btn:hover {
    background: #764ba2;
}

.clear-btn {
    display: block;
    text-align: center;
    padding: 12px;
    background: #f5f5f5;
    color: #666;
    text-decoration: none;
    border-radius: 5px;
}

.clear-btn:hover {
    background: #e0e0e0;
}

/* Products Grid */
.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 20px;
}

.product-card {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    transition: transform 0.2s;
    padding: 15px;
}

.product-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.15);
}

.product-link {
    text-decoration: none;
    color: inherit;
}

.product-image {
    height: 150px;
    background: #f5f5f5;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 10px;
    border-radius: 5px;
    overflow: hidden;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.no-image {
    font-size: 3rem;
}

.product-card h3 {
    margin: 0 0 5px 0;
    font-size: 1rem;
}

.category {
    color: #ff6b6b;
    font-size: 0.8rem;
    margin: 0 0 5px 0;
}

.price {
    font-weight: bold;
    font-size: 1.1rem;
    margin: 0 0 10px 0;
}

.add-to-cart button {
    width: 100%;
    padding: 8px;
    background: #ff6b6b;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

.add-to-cart button:hover {
    background: #ff5252;
}

.no-products {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 8px;
}

/* Responsive */
@media (max-width: 768px) {
    .shop-layout {
        grid-template-columns: 1fr;
    }
    
    .filter-sidebar {
        position: static;
    }
}
</style>

<?php include __DIR__ . '/includes/footer.php'; ?>