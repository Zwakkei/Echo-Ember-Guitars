<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

// Check if admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: /echo-ember-guitars/user/login.php');
    exit();
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['product_image'])) {
    $product_id = $_POST['product_id'] ?? 0;
    $file = $_FILES['product_image'];
    
    // Check if product exists
    $check = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
    $check->bind_param("i", $product_id);
    $check->execute();
    $result = $check->get_result();
    
    if ($result->num_rows === 0) {
        $error = "Product not found!";
    } else {
        $product = $result->fetch_assoc();
        
        // Image settings
        $target_dir = __DIR__ . '/../uploads/products/';
        $max_width = 800;
        $max_height = 800;
        $max_size = 500 * 1024; // 500KB
        
        // Create filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = strtolower(str_replace(' ', '_', $product['product_name'])) . '_' . $product['product_id'] . '.' . $extension;
        $target_file = $target_dir . $filename;
        
        // Check file size
        if ($file['size'] > $max_size) {
            $error = "File too large! Max size: 500KB";
        } else {
            // Get image dimensions
            list($width, $height) = getimagesize($file['tmp_name']);
            
            // Resize if needed
            if ($width > $max_width || $height > $max_height) {
                // Calculate new dimensions
                $ratio = min($max_width / $width, $max_height / $height);
                $new_width = round($width * $ratio);
                $new_height = round($height * $ratio);
                
                // Create resized image
                $src = imagecreatefromstring(file_get_contents($file['tmp_name']));
                $dst = imagecreatetruecolor($new_width, $new_height);
                imagecopyresampled($dst, $src, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                
                // Save resized image
                if ($extension == 'jpg' || $extension == 'jpeg') {
                    imagejpeg($dst, $target_file, 85);
                } elseif ($extension == 'png') {
                    imagepng($dst, $target_file, 6);
                }
                
                imagedestroy($src);
                imagedestroy($dst);
            } else {
                // Just move the file if already small enough
                move_uploaded_file($file['tmp_name'], $target_file);
            }
            
            // Update database
            $image_path = 'uploads/products/' . $filename;
            $update = $conn->prepare("UPDATE products SET image_path = ? WHERE product_id = ?");
            $update->bind_param("si", $image_path, $product_id);
            
            if ($update->execute()) {
                $message = "✅ Image uploaded and resized successfully!";
            } else {
                $error = "❌ Database update failed!";
            }
        }
    }
}

// Get all products
$products = $conn->query("SELECT * FROM products ORDER BY category, product_name");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload Product Images</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; }
        h1 { color: #333; }
        .message { background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
        .product-card { background: white; border-radius: 10px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .product-card img { max-width: 100%; height: 150px; object-fit: contain; display: block; margin: 0 auto 15px; }
        .upload-form { margin-top: 15px; }
        .btn { background: #667eea; color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; }
        .btn:hover { background: #764ba2; }
        .file-input { margin-bottom: 10px; }
        .current-image { color: #666; font-size: 0.9rem; margin: 5px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📸 Upload Product Images</h1>
        <p>Max size: 500KB | Max dimensions: 800x800 (auto-resized)</p>
        
        <?php if ($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="product-grid">
            <?php while($product = $products->fetch_assoc()): ?>
                <div class="product-card">
                    <?php if (!empty($product['image_path']) && file_exists(__DIR__ . '/../' . $product['image_path'])): ?>
                        <img src="/echo-ember-guitars/<?php echo $product['image_path']; ?>" alt="<?php echo $product['product_name']; ?>">
                    <?php else: ?>
                        <div style="height: 150px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; margin-bottom: 15px;">
                            <span style="font-size: 3rem;">🎸</span>
                        </div>
                    <?php endif; ?>
                    
                    <h3><?php echo $product['product_name']; ?></h3>
                    <p><?php echo $product['category']; ?> - ₱<?php echo $product['price']; ?></p>
                    
                    <?php if (!empty($product['image_path'])): ?>
                        <p class="current-image">Current: <?php echo basename($product['image_path']); ?></p>
                    <?php endif; ?>
                    
                    <form method="POST" enctype="multipart/form-data" class="upload-form">
                        <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                        <input type="file" name="product_image" accept="image/*" class="file-input" required>
                        <button type="submit" class="btn">Upload Image</button>
                    </form>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html>