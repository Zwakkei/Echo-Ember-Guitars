<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

// Check if user is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: /echo-ember-guitars/user/login.php');
    exit();
}

// Get statistics
$total_products = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$total_orders = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$total_customers = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'customer'")->fetch_assoc()['count'];
$total_revenue = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE status != 'cancelled'")->fetch_assoc()['total'] ?? 0;

// Get recent orders
$recent_orders = $conn->query("SELECT o.*, u.username FROM orders o JOIN users u ON o.user_id = u.user_id ORDER BY o.order_date DESC LIMIT 5");

// Get low stock products
$low_stock = $conn->query("SELECT * FROM products WHERE stock < 5 ORDER BY stock ASC LIMIT 5");
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="admin-enhanced">
    <div class="admin-header">
        <h1>🎸 Admin Dashboard</h1>
        <div class="admin-date">
            <?php echo date('F j, Y'); ?>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card products">
            <div class="stat-icon">📦</div>
            <div class="stat-content">
                <h3>TOTAL PRODUCTS</h3>
                <p class="stat-number"><?php echo $total_products; ?></p>
                <a href="/echo-ember-guitars/admin/products.php" class="stat-link">Manage Products →</a>
            </div>
        </div>

        <div class="stat-card orders">
            <div class="stat-icon">🛒</div>
            <div class="stat-content">
                <h3>TOTAL ORDERS</h3>
                <p class="stat-number"><?php echo $total_orders; ?></p>
                <!-- FIXED: Changed from add_product.php to orders.php -->
                <a href="/echo-ember-guitars/admin/orders.php" class="stat-link">View Orders →</a>
            </div>
        </div>

        <div class="stat-card customers">
            <div class="stat-icon">👥</div>
            <div class="stat-content">
                <h3>TOTAL CUSTOMERS</h3>
                <p class="stat-number"><?php echo $total_customers; ?></p>
            </div>
        </div>

        <div class="stat-card revenue">
            <div class="stat-icon">💰</div>
            <div class="stat-content">
                <h3>TOTAL REVENUE</h3>
                <p class="stat-number">₱<?php echo number_format($total_revenue, 2); ?></p>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <h2>Quick Actions</h2>
        <div class="action-buttons">
            <a href="/echo-ember-guitars/admin/add_product.php" class="action-btn primary">
                <span class="action-icon">➕</span>
                Add New Product
            </a>
            <!-- FIXED: Changed from add_product.php to orders.php -->
            <a href="/echo-ember-guitars/admin/orders.php" class="action-btn secondary">
                <span class="action-icon">📋</span>
                View Recent Orders
            </a>
            <a href="/echo-ember-guitars/admin/products.php" class="action-btn secondary">
                <span class="action-icon">✏️</span>
                Manage Inventory
            </a>
        </div>
    </div>

    <!-- Two Column Layout -->
    <div class="dashboard-grid">
        <!-- Recent Orders -->
        <div class="dashboard-card">
            <div class="card-header">
                <h2>📋 Recent Orders</h2>
                <!-- FIXED: Changed from add_product.php to orders.php -->
                <a href="/echo-ember-guitars/admin/orders.php" class="view-all">View All →</a>
            </div>
            <div class="card-body">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($recent_orders->num_rows > 0): ?>
                            <?php while($order = $recent_orders->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo $order['order_id']; ?></td>
                                    <td><?php echo $order['username']; ?></td>
                                    <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo $order['status']; ?>">
                                            <?php echo $order['status']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">No orders yet</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Low Stock Alert -->
        <div class="dashboard-card">
            <div class="card-header">
                <h2>⚠️ Low Stock Alert</h2>
                <a href="/echo-ember-guitars/admin/products.php" class="view-all">Manage →</a>
            </div>
            <div class="card-body">
                <?php if ($low_stock->num_rows > 0): ?>
                    <div class="stock-list">
                        <?php while($product = $low_stock->fetch_assoc()): ?>
                            <div class="stock-item">
                                <div class="stock-info">
                                    <strong><?php echo $product['product_name']; ?></strong>
                                    <span class="stock-category"><?php echo $product['category']; ?></span>
                                </div>
                                <div class="stock-level">
                                    <div class="stock-bar">
                                        <div class="stock-fill" style="width: <?php echo min(100, ($product['stock']/10)*100); ?>%"></div>
                                    </div>
                                    <span class="stock-count <?php echo $product['stock'] < 3 ? 'critical' : 'warning'; ?>">
                                        <?php echo $product['stock']; ?> left
                                    </span>
                                </div>
                                <a href="/echo-ember-guitars/admin/edit_product.php?id=<?php echo $product['product_id']; ?>" class="stock-action">Restock →</a>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p class="text-center text-success">✅ All products are well-stocked!</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
/* Your existing CSS styles here */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    display: flex;
    align-items: center;
    gap: 15px;
}

.stat-icon {
    font-size: 2rem;
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(102, 126, 234, 0.1);
    border-radius: 10px;
}

.stat-content h3 {
    font-size: 0.9rem;
    color: #666;
    margin: 0 0 5px 0;
}

.stat-number {
    font-size: 1.8rem;
    font-weight: 700;
    color: #333;
    margin: 0 0 5px 0;
}

.stat-link {
    color: #667eea;
    text-decoration: none;
    font-size: 0.9rem;
}

.quick-actions {
    background: white;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 30px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.action-buttons {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-top: 15px;
}

.action-btn {
    padding: 12px 20px;
    border-radius: 8px;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 10px;
}

.action-btn.primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.action-btn.secondary {
    background: #f8f9fa;
    color: #333;
}

.dashboard-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 25px;
}

.dashboard-card {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 2px solid #f0f0f0;
}

.view-all {
    color: #667eea;
    text-decoration: none;
}

.admin-table {
    width: 100%;
    border-collapse: collapse;
}

.admin-table th {
    text-align: left;
    padding: 10px;
    background: #f8f9fa;
    color: #555;
    font-size: 0.9rem;
}

.admin-table td {
    padding: 10px;
    border-bottom: 1px solid #f0f0f0;
}

.status-badge {
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 0.8rem;
}

.status-badge.pending {
    background: #fff3cd;
    color: #856404;
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    .dashboard-grid {
        grid-template-columns: 1fr;
    }
}

/* Dark mode */
body.dark-mode .stat-card,
body.dark-mode .quick-actions,
body.dark-mode .dashboard-card {
    background: #16213e;
}

body.dark-mode .stat-number {
    color: #fff;
}

body.dark-mode .admin-table th {
    background: #0f3460;
    color: #fff;
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>