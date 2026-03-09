<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

// Check if user is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: /echo-ember-guitars/user/login.php');
    exit();
}

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($order_id === 0) {
    header('Location: orders.php');
    exit();
}

// Get order details
$order_sql = "SELECT o.*, u.username, u.full_name, u.email, u.phone, u.address 
              FROM orders o 
              JOIN users u ON o.user_id = u.user_id 
              WHERE o.order_id = ?";
$order_stmt = $conn->prepare($order_sql);
$order_stmt->bind_param("i", $order_id);
$order_stmt->execute();
$order_result = $order_stmt->get_result();

if ($order_result->num_rows === 0) {
    header('Location: orders.php');
    exit();
}

$order = $order_result->fetch_assoc();

// Get order items
$items_sql = "SELECT od.*, p.product_name 
              FROM order_details od 
              JOIN products p ON od.product_id = p.product_id 
              WHERE od.order_id = ?";
$items_stmt = $conn->prepare($items_sql);
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items = $items_stmt->get_result();
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="order-details-page">
    <div class="page-header">
        <h1>📋 Order Details #<?php echo $order_id; ?></h1>
        <a href="orders.php" class="back-btn">← Back to Orders</a>
    </div>

    <div class="order-info-grid">
        <!-- Order Information -->
        <div class="info-card">
            <h2>Order Information</h2>
            <div class="info-row">
                <span class="info-label">Order ID:</span>
                <span class="info-value">#<?php echo $order['order_id']; ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Date:</span>
                <span class="info-value"><?php echo date('F j, Y, g:i a', strtotime($order['order_date'])); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Status:</span>
                <span class="info-value status-badge <?php echo $order['status']; ?>"><?php echo $order['status']; ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Payment Method:</span>
                <span class="info-value"><?php echo $order['payment_method'] ?? 'Cash on Delivery'; ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Total Amount:</span>
                <span class="info-value total">₱<?php echo number_format($order['total_amount'], 2); ?></span>
            </div>
        </div>

        <!-- Customer Information -->
        <div class="info-card">
            <h2>Customer Information</h2>
            <div class="info-row">
                <span class="info-label">Full Name:</span>
                <span class="info-value"><?php echo htmlspecialchars($order['full_name']); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Username:</span>
                <span class="info-value"><?php echo htmlspecialchars($order['username']); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Email:</span>
                <span class="info-value"><?php echo htmlspecialchars($order['email']); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Phone:</span>
                <span class="info-value"><?php echo htmlspecialchars($order['phone'] ?? 'Not provided'); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Shipping Address:</span>
                <span class="info-value"><?php echo htmlspecialchars($order['shipping_address'] ?? $order['address']); ?></span>
            </div>
        </div>
    </div>

    <!-- Order Items -->
    <div class="items-card">
        <h2>Order Items</h2>
        <table class="items-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $subtotal = 0;
                while($item = $items->fetch_assoc()): 
                    $item_subtotal = $item['quantity'] * $item['price_at_time'];
                    $subtotal += $item_subtotal;
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td>₱<?php echo number_format($item['price_at_time'], 2); ?></td>
                    <td>₱<?php echo number_format($item_subtotal, 2); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="text-right">Subtotal:</td>
                    <td>₱<?php echo number_format($subtotal, 2); ?></td>
                </tr>
                <tr>
                    <td colspan="3" class="text-right">Shipping:</td>
                    <td>Free</td>
                </tr>
                <tr>
                    <td colspan="3" class="text-right total-label">Total:</td>
                    <td class="total-value">₱<?php echo number_format($order['total_amount'], 2); ?></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Update Status Form -->
    <div class="status-card">
        <h2>Update Order Status</h2>
        <form method="POST" action="orders.php" class="status-form">
            <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
            <select name="status" class="status-select <?php echo $order['status']; ?>">
                <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="processing" <?php echo $order['status'] == 'processing' ? 'selected' : ''; ?>>Processing</option>
                <option value="completed" <?php echo $order['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
            </select>
            <button type="submit" name="update_status" class="update-btn">Update Status</button>
        </form>
    </div>
</div>

<style>
.order-details-page {
    max-width: 1200px;
    margin: 30px auto;
    padding: 0 20px;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    background: white;
    padding: 20px 25px;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.page-header h1 {
    font-size: 1.8rem;
    color: #333;
    margin: 0;
}

.back-btn {
    color: #667eea;
    text-decoration: none;
    font-weight: 500;
    padding: 8px 15px;
    border-radius: 6px;
    background: #f0f4ff;
    transition: all 0.3s;
}

.back-btn:hover {
    background: #667eea;
    color: white;
}

.order-info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 25px;
    margin-bottom: 25px;
}

.info-card, .items-card, .status-card {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.info-card h2, .items-card h2, .status-card h2 {
    font-size: 1.2rem;
    color: #333;
    margin: 0 0 20px 0;
    padding-bottom: 10px;
    border-bottom: 2px solid #f0f0f0;
}

.info-row {
    display: flex;
    justify-content: space-between;
    margin: 12px 0;
    color: #666;
}

.info-label {
    font-weight: 500;
    color: #999;
}

.info-value {
    font-weight: 500;
    color: #333;
}

.info-value.total {
    color: #ff6b6b;
    font-size: 1.2rem;
    font-weight: 700;
}

.status-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 600;
}

.status-badge.pending {
    background: #fff3cd;
    color: #856404;
}

.status-badge.processing {
    background: #cce5ff;
    color: #004085;
}

.status-badge.completed {
    background: #d4edda;
    color: #155724;
}

.status-badge.cancelled {
    background: #f8d7da;
    color: #721c24;
}

.items-table {
    width: 100%;
    border-collapse: collapse;
}

.items-table th {
    text-align: left;
    padding: 12px;
    background: #f8f9fa;
    color: #555;
    font-weight: 600;
    font-size: 0.9rem;
}

.items-table td {
    padding: 12px;
    border-bottom: 1px solid #f0f0f0;
    color: #333;
}

.items-table tfoot {
    background: #f8f9fa;
    font-weight: 500;
}

.items-table tfoot td {
    padding: 12px;
}

.text-right {
    text-align: right;
}

.total-label {
    font-weight: 600;
    color: #333;
}

.total-value {
    color: #ff6b6b;
    font-weight: 700;
    font-size: 1.1rem;
}

.status-card .status-form {
    display: flex;
    gap: 15px;
    align-items: center;
}

.status-select {
    flex: 1;
    padding: 10px;
    border: 2px solid #e1e1e1;
    border-radius: 8px;
    font-size: 1rem;
    cursor: pointer;
}

.update-btn {
    padding: 10px 25px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
}

.update-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
}

/* Dark Mode */
body.dark-mode .page-header,
body.dark-mode .info-card,
body.dark-mode .items-card,
body.dark-mode .status-card {
    background: #16213e;
}

body.dark-mode .page-header h1,
body.dark-mode .info-card h2,
body.dark-mode .items-card h2,
body.dark-mode .status-card h2 {
    color: #fff;
}

body.dark-mode .info-label {
    color: #b0b0b0;
}

body.dark-mode .info-value {
    color: #fff;
}

body.dark-mode .items-table th {
    background: #0f3460;
    color: #fff;
}

body.dark-mode .items-table td {
    color: #b0b0b0;
    border-color: #1a1a2e;
}

body.dark-mode .items-table tfoot {
    background: #0f3460;
}

/* Responsive */
@media (max-width: 768px) {
    .order-info-grid {
        grid-template-columns: 1fr;
    }
    
    .page-header {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
    
    .status-card .status-form {
        flex-direction: column;
    }
    
    .update-btn {
        width: 100%;
    }
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>