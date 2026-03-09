<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

if (!isAdmin()) {
    redirect('../index.php');
}

// Fetch all products
$products = $conn->query("SELECT * FROM products ORDER BY created_at DESC");
?>

<?php include '../includes/header.php'; ?>

<div class="admin-container">
    <div class="admin-header">
        <h2>Manage Products</h2>
        <a href="add_product.php" class="btn btn-primary">Add New Product</a>
    </div>
    
    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    
    <div class="admin-table">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($product = $products->fetch_assoc()): ?>
                <tr>
                    <td>#<?php echo $product['product_id']; ?></td>
                    <td>
                        <?php if($product['image_path']): ?>
                            <img src="../<?php echo $product['image_path']; ?>" alt="<?php echo $product['product_name']; ?>" style="width: 50px; height: 50px; object-fit: cover;">
                        <?php else: ?>
                            🎸
                        <?php endif; ?>
                    </td>
                    <td><?php echo $product['product_name']; ?></td>
                    <td><?php echo $product['category']; ?></td>
                    <td>₱<?php echo number_format($product['price'], 2); ?></td>
                    <td><?php echo $product['stock']; ?></td>
                    <td class="actions">
                        <a href="edit_product.php?id=<?php echo $product['product_id']; ?>" class="btn btn-small">Edit</a>
                        <a href="delete_product.php?id=<?php echo $product['product_id']; ?>" class="btn btn-small btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>