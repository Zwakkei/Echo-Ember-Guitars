<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /echo-ember-guitars/user/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user orders
$sql = "SELECT o.*, 
        (SELECT COUNT(*) FROM order_details WHERE order_id = o.order_id) as item_count 
        FROM orders o 
        WHERE o.user_id = ? 
        ORDER BY o.order_date DESC";
        
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders = $stmt->get_result();
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="orders-page">
    <h1 class="orders-title">
        <span class="orders-icon">📦</span> 
        My Orders
    </h1>
    
    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php 
            echo $_SESSION['success']; 
            unset($_SESSION['success']); 
        ?></div>
    <?php endif; ?>
    
    <?php if ($orders->num_rows > 0): ?>
        <div class="orders-grid">
            <?php while($order = $orders->fetch_assoc()): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div class="order-id">
                            <span class="order-label">Order #</span>
                            <span class="order-number"><?php echo $order['order_id']; ?></span>
                        </div>
                        <span class="order-status <?php echo strtolower($order['status']); ?>">
                            <?php echo $order['status']; ?>
                        </span>
                    </div>
                    
                    <div class="order-body">
                        <div class="order-info">
                            <div class="info-row">
                                <span class="info-label">Date:</span>
                                <span class="info-value"><?php echo date('F j, Y, g:i a', strtotime($order['order_date'])); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Total Amount:</span>
                                <span class="info-value price">₱<?php echo number_format($order['total_amount'], 2); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Items:</span>
                                <span class="info-value"><?php echo $order['item_count']; ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Payment Method:</span>
                                <span class="info-value"><?php echo $order['payment_method'] ?? 'Cash on Delivery'; ?></span>
                            </div>
                        </div>
                        
                        <!-- FIXED: View Details Button -->
                        <button class="view-details-btn" onclick="openOrderDetails(<?php echo $order['order_id']; ?>)">
                            <span>View Details</span>
                            <span class="arrow">→</span>
                        </button>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="no-orders">
            <div class="no-orders-emoji">📭</div>
            <h2>No orders yet</h2>
            <p>Looks like you haven't placed any orders.</p>
            <a href="/echo-ember-guitars/index.php#products" class="shop-now-btn">Start Shopping →</a>
        </div>
    <?php endif; ?>
</div>

<!-- Order Details Modal -->
<div id="orderModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Order Details</h2>
            <span class="close-modal" onclick="closeModal()">&times;</span>
        </div>
        <div id="orderDetailsContent" class="modal-body">
            <div class="loading-spinner">Loading...</div>
        </div>
    </div>
</div>

<script>
function openOrderDetails(orderId) {
    // Show modal
    const modal = document.getElementById('orderModal');
    modal.style.display = 'block';
    
    // Show loading
    document.getElementById('orderDetailsContent').innerHTML = '<div class="loading-spinner">🔄 Loading order details...</div>';
    
    // Fetch order details
    fetch(`/echo-ember-guitars/user/get_order_details.php?order_id=${orderId}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                document.getElementById('orderDetailsContent').innerHTML = `<div class="error-message">❌ ${data.error}</div>`;
            } else {
                displayOrderDetails(data);
            }
        })
        .catch(error => {
            document.getElementById('orderDetailsContent').innerHTML = '<div class="error-message">❌ Failed to load order details</div>';
        });
}

function displayOrderDetails(data) {
    const order = data.order;
    const items = data.items;
    
    let html = `
        <div class="order-detail-header">
            <div class="detail-row">
                <span class="detail-label">Order #:</span>
                <span class="detail-value">${order.order_id}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Date:</span>
                <span class="detail-value">${new Date(order.order_date).toLocaleString()}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Status:</span>
                <span class="detail-value status-badge ${order.status}">${order.status}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Payment Method:</span>
                <span class="detail-value">${order.payment_method || 'Cash on Delivery'}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Shipping Address:</span>
                <span class="detail-value">${order.shipping_address || 'Not provided'}</span>
            </div>
        </div>
        
        <h3 class="items-title">Items</h3>
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
    `;
    
    items.forEach(item => {
        html += `
            <tr>
                <td>${item.product_name}</td>
                <td>${item.quantity}</td>
                <td>₱${parseFloat(item.price_at_time).toFixed(2)}</td>
                <td>₱${(item.quantity * item.price_at_time).toFixed(2)}</td>
            </tr>
        `;
    });
    
    html += `
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="total-label">Total</td>
                    <td class="total-value">₱${parseFloat(order.total_amount).toFixed(2)}</td>
                </tr>
            </tfoot>
        </table>
    `;
    
    document.getElementById('orderDetailsContent').innerHTML = html;
}

function closeModal() {
    document.getElementById('orderModal').style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('orderModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}
</script>

<style>
/* Orders Page Styles */
.orders-page {
    max-width: 1200px;
    margin: 40px auto;
    padding: 0 20px;
}

.orders-title {
    font-size: 2rem;
    color: #333;
    margin-bottom: 30px;
    display: flex;
    align-items: center;
    gap: 10px;
    border-bottom: 3px solid #f0f0f0;
    padding-bottom: 15px;
}

.orders-icon {
    font-size: 2rem;
}

/* Orders Grid */
.orders-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 25px;
}

/* Order Card */
.order-card {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    transition: all 0.3s;
    border: 1px solid #f0f0f0;
}

.order-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    border-color: #667eea;
}

.order-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.order-id {
    display: flex;
    align-items: center;
    gap: 5px;
}

.order-label {
    font-size: 0.9rem;
    opacity: 0.9;
}

.order-number {
    font-size: 1.2rem;
    font-weight: 700;
}

.order-status {
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    background: rgba(255,255,255,0.2);
}

.order-status.pending { background: #fff3cd; color: #856404; }
.order-status.processing { background: #cce5ff; color: #004085; }
.order-status.completed { background: #d4edda; color: #155724; }
.order-status.cancelled { background: #f8d7da; color: #721c24; }

.order-body {
    padding: 20px;
}

.order-info {
    margin-bottom: 20px;
}

.info-row {
    display: flex;
    justify-content: space-between;
    margin: 10px 0;
    color: #666;
    font-size: 0.95rem;
}

.info-label {
    color: #999;
}

.info-value {
    font-weight: 500;
    color: #333;
}

.info-value.price {
    color: #ff6b6b;
    font-weight: 600;
}

/* View Details Button */
.view-details-btn {
    width: 100%;
    padding: 12px;
    background: #f8f9fa;
    border: 2px solid #e1e1e1;
    border-radius: 8px;
    color: #333;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.3s;
}

.view-details-btn:hover {
    background: #667eea;
    border-color: #667eea;
    color: white;
}

.view-details-btn .arrow {
    transition: transform 0.3s;
}

.view-details-btn:hover .arrow {
    transform: translateX(5px);
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    overflow-y: auto;
}

.modal-content {
    background: white;
    margin: 5% auto;
    padding: 0;
    width: 90%;
    max-width: 800px;
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.2);
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from {
        transform: translateY(-50px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px 25px;
    border-radius: 20px 20px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h2 {
    margin: 0;
    font-size: 1.5rem;
}

.close-modal {
    font-size: 2rem;
    cursor: pointer;
    opacity: 0.8;
    transition: opacity 0.3s;
}

.close-modal:hover {
    opacity: 1;
}

.modal-body {
    padding: 25px;
}

/* Order Details in Modal */
.order-detail-header {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 20px;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    margin: 8px 0;
}

.detail-label {
    color: #666;
    font-weight: 500;
}

.detail-value {
    font-weight: 600;
    color: #333;
}

.status-badge {
    padding: 3px 12px;
    border-radius: 20px;
    font-size: 0.9rem;
}

.items-title {
    margin: 20px 0 15px;
    color: #333;
}

.items-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
}

.items-table th {
    background: #f8f9fa;
    padding: 12px;
    text-align: left;
    color: #666;
    font-weight: 600;
    border-bottom: 2px solid #e1e1e1;
}

.items-table td {
    padding: 12px;
    border-bottom: 1px solid #f0f0f0;
    color: #333;
}

.items-table tfoot tr {
    background: #f8f9fa;
    font-weight: 600;
}

.total-label {
    text-align: right;
    color: #666;
}

.total-value {
    color: #ff6b6b;
    font-size: 1.2rem;
}

/* Loading Spinner */
.loading-spinner {
    text-align: center;
    padding: 40px;
    color: #666;
    font-size: 1.1rem;
}

.error-message {
    text-align: center;
    padding: 40px;
    color: #ff6b6b;
    font-size: 1.1rem;
}

/* No Orders */
.no-orders {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 20px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
}

.no-orders-emoji {
    font-size: 4rem;
    margin-bottom: 20px;
    opacity: 0.7;
}

.no-orders h2 {
    color: #333;
    margin-bottom: 10px;
    font-size: 1.8rem;
}

.no-orders p {
    color: #666;
    margin-bottom: 25px;
}

.shop-now-btn {
    display: inline-block;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 15px 40px;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s;
}

.shop-now-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
}

/* Alert */
.alert {
    padding: 15px 20px;
    border-radius: 10px;
    margin-bottom: 25px;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

/* Responsive */
@media (max-width: 768px) {
    .orders-grid {
        grid-template-columns: 1fr;
    }
    
    .orders-title {
        font-size: 1.5rem;
    }
    
    .modal-content {
        width: 95%;
        margin: 10% auto;
    }
}

@media (max-width: 480px) {
    .order-header {
        flex-direction: column;
        gap: 10px;
        text-align: center;
    }
    
    .info-row {
        flex-direction: column;
        gap: 5px;
        text-align: center;
    }
    
    .items-table {
        font-size: 0.9rem;
    }
    
    .items-table th, 
    .items-table td {
        padding: 8px;
    }
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>