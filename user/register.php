<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $full_name = $_POST['full_name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    
    // Validation
    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters!";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, phone, address) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $username, $email, $hashed_password, $full_name, $phone, $address);
        
        if ($stmt->execute()) {
            $success = "Registration successful! You can now login.";
        } else {
            $error = "Registration failed. Username or email may already exist.";
        }
    }
}
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="auth-container">
    <div class="auth-box">
        <h2>Register</h2>
        
        <?php if($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="/echo-ember-guitars/user/register.php" class="auth-form">
            <div class="form-group">
                <label for="full_name">Full Name *</label>
                <input type="text" id="full_name" name="full_name" required>
            </div>
            
            <div class="form-group">
                <label for="username">Username *</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password *</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password *</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            
            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="text" id="phone" name="phone">
            </div>
            
            <div class="form-group">
                <label for="address">Address</label>
                <textarea id="address" name="address" rows="3"></textarea>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">Register</button>
        </form>
        
        <p class="auth-link">Already have an account? <a href="/echo-ember-guitars/user/login.php">Login here</a></p>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>