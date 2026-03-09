<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

// Check if user is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: /echo-ember-guitars/user/login.php');
    exit();
}

// Handle order status update
if (isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $status = $_POST['status'];
    
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
    $stmt->bind_param("si", $status, $order_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Order #$order_id status updated to $status";
    } else {
        $_SESSION['error'] = "Error updating order status";
    }
    
    header('Location: orders.php');
    exit();
}

// Get all orders with customer details
$orders = $conn->query("
    SELECT o.*, u.username, u.full_name, u.email 
    FROM orders o 
    JOIN users u ON o.user_id = u.user_id 
    ORDER BY o.order_date DESC
");
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="admin-orders">
    <div class="page-header">
        <h1>📋 Order Management</h1>
        <div class="header-stats">
            <span class="total-orders">Total Orders: <?php echo $orders->num_rows; ?></span>
        </div>
    </div>
    
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
    
    <?php if ($orders->num_rows > 0): ?>
        <div class="orders-table-container">
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($order = $orders->fetch_assoc()): ?>
                        <tr>
                            <td class="order-id">#<?php echo $order['order_id']; ?></td>
                            <td class="customer-info">
                                <strong><?php echo htmlspecialchars($order['full_name']); ?></strong>
                                <br>
                                <small><?php echo htmlspecialchars($order['email']); ?></small>
                            </td>
                            <td class="order-total">₱<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td><?php echo $order['payment_method'] ?? 'Cash on Delivery'; ?></td>
                            <td>
                                <form method="POST" class="status-form">
                                    <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                    <select name="status" class="status-select <?php echo $order['status']; ?>" onchange="this.form.submit()">
                                        <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="processing" <?php echo $order['status'] == 'processing' ? 'selected' : ''; ?>>Processing</option>
                                        <option value="completed" <?php echo $order['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                        <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                    <input type="hidden" name="update_status" value="1">
                                </form>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                            <td>
                                <a href="order-details.php?id=<?php echo $order['order_id']; ?>" class="btn-view">View Details</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="no-orders">
            <p>No orders found</p>
        </div>
    <?php endif; ?>
</div>

<style>
.admin-orders {
    max-width: 1400px;
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
    font-size: 2rem;
    color: #333;
    margin: 0;
}

.header-stats {
    background: #f0f0f0;
    padding: 8px 16px;
    border-radius: 20px;
    color: #666;
    font-weight: 500;
}

.orders-table-container {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    overflow: hidden;
}

.orders-table {
    width: 100%;
    border-collapse: collapse;
}

.orders-table th {
    background: #f8f9fa;
    padding: 15px;
    text-align: left;
    font-weight: 600;
    color: #333;
    font-size: 0.95rem;
    border-bottom: 2px solid #e1e1e1;
}

.orders-table td {
    padding: 15px;
    border-bottom: 1px solid #f0f0f0;
    color: #555;
}

.orders-table tr:hover {
    background: #f8f9fa;
}

.order-id {
    font-weight: 600;
    color: #667eea;
}

.customer-info {
    line-height: 1.4;
}

.customer-info strong {
    color: #333;
}

.customer-info small {
    color: #999;
    font-size: 0.85rem;
}

.order-total {
    font-weight: 600;
    color: #ff6b6b;
}

.status-form {
    margin: 0;
}

.status-select {
    padding: 6px 12px;
    border-radius: 20px;
    border: none;
    font-size: 0.9rem;
    font-weight: 500;
    cursor: pointer;
    outline: none;
}

.status-select.pending {
    background: #fff3cd;
    color: #856404;
}

.status-select.processing {
    background: #cce5ff;
    color: #004085;
}

.status-select.completed {
    background: #d4edda;
    color: #155724;
}

.status-select.cancelled {
    background: #f8d7da;
    color: #721c24;
}

.btn-view {
    display: inline-block;
    padding: 6px 12px;
    background: #667eea;
    color: white;
    text-decoration: none;
    border-radius: 6px;
    font-size: 0.9rem;
    transition: all 0.3s;
}

.btn-view:hover {
    background: #764ba2;
    transform: translateY(-2px);
}

.alert {
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
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

.no-orders {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 12px;
    color: #666;
}

/* Dark Mode */
body.dark-mode .page-header,
body.dark-mode .orders-table-container {
    background: #16213e;
}

body.dark-mode .page-header h1 {
    color: #fff;
}

body.dark-mode .header-stats {
    background: #0f3460;
    color: #b0b0b0;
}

body.dark-mode .orders-table th {
    background: #0f3460;
    color: #fff;
}

body.dark-mode .orders-table td {
    color: #b0b0b0;
    border-color: #1a1a2e;
}

body.dark-mode .customer-info strong {
    color: #fff;
}

/* Responsive */
@media (max-width: 1024px) {
    .orders-table {
        font-size: 0.9rem;
    }
    
    .orders-table th,
    .orders-table td {
        padding: 12px 8px;
    }
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
    
    .orders-table-container {
        overflow-x: auto;
    }
    
    .orders-table {
        min-width: 800px;
    }
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>