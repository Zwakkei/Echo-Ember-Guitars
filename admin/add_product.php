<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

if (!isAdmin()) {
    redirect('../index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = sanitize($_POST['product_name']);
    $description = sanitize($_POST['description']);
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];
    $category = sanitize($_POST['category']);
    
    // Handle image upload
    $image_path = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/products/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $file_name = uniqid() . '.' . $file_extension;
        $target_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
            $image_path = 'uploads/products/' . $file_name;
        }
    }
    
    // Insert product
    $stmt = $conn->prepare("INSERT INTO products (product_name, description, price, stock, category, image_path) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdiss", $product_name, $description, $price, $stock, $category, $image_path);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Product added successfully!";
        redirect('products.php');
    } else {
        $error = "Error adding product";
    }
}
?>

<?php include '../includes/header.php'; ?>

<div class="admin-container">
    <h2>Add New Product</h2>
    
    <?php if(isset($error)): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <form method="POST" action="" enctype="multipart/form-data" class="admin-form">
        <div class="form-group">
            <label for="product_name">Product Name *</label>
            <input type="text" id="product_name" name="product_name" required>
        </div>
        
        <div class="form-group">
            <label for="category">Category *</label>
            <select id="category" name="category" required>
                <option value="Acoustic">Acoustic</option>
                <option value="Electric">Electric</option>
                <option value="Classical">Classical</option>
                <option value="Bass">Bass</option>
                <option value="Accessories">Accessories</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="price">Price *</label>
            <input type="number" id="price" name="price" step="0.01" min="0" required>
        </div>
        
        <div class="form-group">
            <label for="stock">Stock *</label>
            <input type="number" id="stock" name="stock" min="0" required>
        </div>
        
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="5"></textarea>
        </div>
        
        <div class="form-group">
            <label for="image">Product Image</label>
            <input type="file" id="image" name="image" accept="image/*">
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Add Product</button>
            <a href="products.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>