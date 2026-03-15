<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

// Check if admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get product details
$stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: products.php');
    exit();
}

$product = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = $_POST['product_name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $category = $_POST['category'];
    
    // Handle image upload
    $image_path = $product['image_path']; // keep old image by default
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/products/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $file_name = uniqid() . '.' . $file_extension;
        $target_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
            // Delete old image if exists
            if ($product['image_path'] && file_exists('../' . $product['image_path'])) {
                unlink('../' . $product['image_path']);
            }
            $image_path = 'uploads/products/' . $file_name;
        }
    }
    
    // Update product
    $update = $conn->prepare("UPDATE products SET product_name=?, description=?, price=?, stock=?, category=?, image_path=? WHERE product_id=?");
    $update->bind_param("ssdissi", $product_name, $description, $price, $stock, $category, $image_path, $product_id);
    
    if ($update->execute()) {
        header('Location: products.php?success=updated');
        exit();
    } else {
        $error = "Error updating product";
    }
}
?>

<?php include '../includes/header.php'; ?>

<div class="admin-container">
    <h2>Edit Product</h2>
    
    <?php if(isset($error)): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <form method="POST" enctype="multipart/form-data" class="admin-form">
        <div class="form-group">
            <label>Product Name</label>
            <input type="text" name="product_name" value="<?php echo htmlspecialchars($product['product_name']); ?>" required>
        </div>
        
        <div class="form-group">
            <label>Category</label>
            <select name="category" required>
                <option value="Acoustic" <?php echo $product['category'] == 'Acoustic' ? 'selected' : ''; ?>>Acoustic</option>
                <option value="Electric" <?php echo $product['category'] == 'Electric' ? 'selected' : ''; ?>>Electric</option>
                <option value="Bass" <?php echo $product['category'] == 'Bass' ? 'selected' : ''; ?>>Bass</option>
                <option value="Classical" <?php echo $product['category'] == 'Classical' ? 'selected' : ''; ?>>Classical</option>
                <option value="Amplifier" <?php echo $product['category'] == 'Amplifier' ? 'selected' : ''; ?>>Amplifier</option>
                <option value="Pedal" <?php echo $product['category'] == 'Pedal' ? 'selected' : ''; ?>>Pedal</option>
                <option value="Tool Kit" <?php echo $product['category'] == 'Tool Kit' ? 'selected' : ''; ?>>Tool Kit</option>
                <option value="Tuner" <?php echo $product['category'] == 'Tuner' ? 'selected' : ''; ?>>Tuner</option>
                <option value="Accessory" <?php echo $product['category'] == 'Accessory' ? 'selected' : ''; ?>>Accessory</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>Price</label>
            <input type="number" name="price" step="0.01" value="<?php echo $product['price']; ?>" required>
        </div>
        
        <div class="form-group">
            <label>Stock</label>
            <input type="number" name="stock" value="<?php echo $product['stock']; ?>" required>
        </div>
        
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" rows="5"><?php echo htmlspecialchars($product['description']); ?></textarea>
        </div>
        
        <div class="form-group">
            <label>Current Image</label><br>
            <?php if($product['image_path']): ?>
                <img src="/echo-ember-guitars/<?php echo $product['image_path']; ?>" style="max-width: 200px; max-height: 200px;"><br>
            <?php endif; ?>
            <label>Upload New Image (leave empty to keep current)</label>
            <input type="file" name="image" accept="image/*">
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Update Product</button>
            <a href="products.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<style>
.admin-container {
    max-width: 800px;
    margin: 30px auto;
    padding: 20px;
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.admin-container h2 {
    margin-bottom: 20px;
    color: #333;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
    color: #555;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 10px;
    border: 2px solid #e1e1e1;
    border-radius: 5px;
    font-size: 1rem;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #667eea;
}

.form-actions {
    display: flex;
    gap: 10px;
    margin-top: 30px;
}

.btn {
    padding: 12px 25px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 1rem;
    text-decoration: none;
    text-align: center;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-secondary {
    background: #f0f0f0;
    color: #333;
}

.alert {
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
    background: #f8d7da;
    color: #721c24;
}

body.dark-mode .admin-container {
    background: #16213e;
    color: #fff;
}

body.dark-mode .admin-container h2 {
    color: #fff;
}

body.dark-mode .form-group label {
    color: #b0b0b0;
}

body.dark-mode .form-group input,
body.dark-mode .form-group select,
body.dark-mode .form-group textarea {
    background: #0f3460;
    color: #fff;
    border-color: #1a1a2e;
}
</style>

<?php include '../includes/footer.php'; ?>