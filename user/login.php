<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: /echo-ember-guitars/index.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Login logic
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];
            
            header('Location: /echo-ember-guitars/index.php');
            exit();
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "User not found!";
    }
}
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="auth-container">
    <div class="auth-box">
        <h2>Login to Echo & Ember</h2>
        
        <?php if($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="/echo-ember-guitars/user/login.php" class="auth-form">
            <div class="form-group">
                <label for="username">Username or Email</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">Login</button>
        </form>
        
        <p class="auth-link">Don't have an account? <a href="/echo-ember-guitars/user/register.php">Register here</a></p>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>