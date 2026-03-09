<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = $_GET['order_id'] ?? 0;

// Verify order belongs to user
$stmt = $conn->prepare("
    SELECT * FROM orders 
    WHERE order_id = ? AND user_id = ?
");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order_result = $stmt->get_result();

if ($order_result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Order not found']);
    exit();
}

$order = $order_result->fetch_assoc();

// Get order details with product names
$details_sql = "
    SELECT od.*, p.product_name 
    FROM order_details od
    JOIN products p ON od.product_id = p.product_id
    WHERE od.order_id = ?
";
$details_stmt = $conn->prepare($details_sql);
$details_stmt->bind_param("i", $order_id);
$details_stmt->execute();
$details_result = $details_stmt->get_result();

$items = [];
while ($item = $details_result->fetch_assoc()) {
    $items[] = $item;
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode([
    'order' => $order,
    'items' => $items
]);
?>