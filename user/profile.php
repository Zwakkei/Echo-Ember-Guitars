<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /echo-ember-guitars/user/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $full_name = $_POST['full_name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $address = $_POST['address'];
        
        $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, phone = ?, address = ? WHERE user_id = ?");
        $stmt->bind_param("ssssi", $full_name, $email, $phone, $address, $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['full_name'] = $full_name;
            $success = "Profile updated successfully!";
        } else {
            $error = "Error updating profile.";
        }
    }
    
    if (isset($_POST['change_password'])) {
        $current = $_POST['current_password'];
        $new = $_POST['new_password'];
        $confirm = $_POST['confirm_password'];
        
        // Verify current password
        $stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if (password_verify($current, $user['password'])) {
            if ($new === $confirm) {
                if (strlen($new) >= 6) {
                    $hashed = password_hash($new, PASSWORD_DEFAULT);
                    $update = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                    $update->bind_param("si", $hashed, $user_id);
                    $update->execute();
                    $success = "Password changed successfully!";
                } else {
                    $error = "Password must be at least 6 characters.";
                }
            } else {
                $error = "New passwords do not match.";
            }
        } else {
            $error = "Current password is incorrect.";
        }
    }
}

// Get user data
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="profile-page">
    <div class="container">
        <h1 class="profile-title">👤 My Profile</h1>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="profile-grid">
            <!-- Left Column - Profile Info -->
            <div class="profile-card">
                <h2>Personal Information</h2>
                <form method="POST" class="profile-form">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" value="<?php echo $user['username']; ?>" disabled class="readonly-input">
                        <small>Username cannot be changed</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="full_name" value="<?php echo $user['full_name']; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="<?php echo $user['email']; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="text" name="phone" value="<?php echo $user['phone']; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Address</label>
                        <textarea name="address" rows="3"><?php echo $user['address']; ?></textarea>
                    </div>
                    
                    <button type="submit" name="update_profile" class="btn-save">Save Changes</button>
                </form>
            </div>
            
            <!-- Right Column - Change Password -->
            <div class="profile-card">
                <h2>Change Password</h2>
                <form method="POST" class="profile-form" onsubmit="return validatePassword()">
                    <div class="form-group">
                        <label>Current Password</label>
                        <input type="password" name="current_password" required id="current_password">
                    </div>
                    
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" required id="new_password" minlength="6">
                        <div class="password-strength" id="passwordStrength"></div>
                    </div>
                    
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" required id="confirm_password">
                        <div class="password-match" id="passwordMatch"></div>
                    </div>
                    
                    <button type="submit" name="change_password" class="btn-change">Change Password</button>
                </form>
            </div>
        </div>
        
        <!-- Recent Orders Summary -->
        <div class="recent-orders-card">
            <h2>Recent Orders</h2>
            <?php
            $orders = $conn->query("SELECT * FROM orders WHERE user_id = $user_id ORDER BY order_date DESC LIMIT 3");
            if ($orders->num_rows > 0):
            ?>
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($order = $orders->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $order['order_id']; ?></td>
                                <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                                <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>
                                <td><span class="status-badge <?php echo $order['status']; ?>"><?php echo $order['status']; ?></span></td>
                                <td><a href="orders.php" class="view-link">View</a></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <a href="orders.php" class="view-all-link">View All Orders →</a>
            <?php else: ?>
                <p class="no-orders">No orders yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.profile-page {
    padding: 40px 0;
    background: #f8f9fa;
    min-height: 100vh;
}

.profile-title {
    font-size: 2rem;
    color: #333;
    margin-bottom: 30px;
}

.profile-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    margin-bottom: 30px;
}

.profile-card {
    background: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
}

.profile-card h2 {
    font-size: 1.3rem;
    color: #333;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #f0f0f0;
}

.profile-form .form-group {
    margin-bottom: 20px;
}

.profile-form label {
    display: block;
    margin-bottom: 8px;
    color: #555;
    font-weight: 500;
}

.profile-form input,
.profile-form textarea {
    width: 100%;
    padding: 12px;
    border: 2px solid #e1e1e1;
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.3s;
}

.profile-form input:focus,
.profile-form textarea:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.readonly-input {
    background: #f5f5f5;
    cursor: not-allowed;
}

.btn-save,
.btn-change {
    width: 100%;
    padding: 14px;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-save {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-save:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
}

.btn-change {
    background: #ff6b6b;
    color: white;
}

.btn-change:hover {
    background: #ff5252;
    transform: translateY(-2px);
}

.password-strength {
    margin-top: 5px;
    font-size: 0.9rem;
}

.password-match {
    margin-top: 5px;
    font-size: 0.9rem;
}

/* Recent Orders */
.recent-orders-card {
    background: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
}

.recent-orders-card h2 {
    font-size: 1.3rem;
    color: #333;
    margin-bottom: 20px;
}

.orders-table {
    width: 100%;
    border-collapse: collapse;
}

.orders-table th {
    text-align: left;
    padding: 12px;
    background: #f8f9fa;
    color: #555;
    font-weight: 600;
}

.orders-table td {
    padding: 12px;
    border-bottom: 1px solid #f0f0f0;
}

.status-badge {
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.85rem;
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

.view-link {
    color: #667eea;
    text-decoration: none;
}

.view-all-link {
    display: inline-block;
    margin-top: 20px;
    color: #667eea;
    text-decoration: none;
    font-weight: 500;
}

/* Dark Mode */
body.dark-mode .profile-page {
    background: #1a1a2e;
}

body.dark-mode .profile-card,
body.dark-mode .recent-orders-card {
    background: #16213e;
}

body.dark-mode .profile-card h2,
body.dark-mode .recent-orders-card h2,
body.dark-mode .profile-title {
    color: #fff;
}

body.dark-mode .profile-form label {
    color: #b0b0b0;
}

body.dark-mode .profile-form input,
body.dark-mode .profile-form textarea {
    background: #0f3460;
    color: #fff;
    border-color: #1a1a2e;
}

body.dark-mode .orders-table th {
    background: #0f3460;
    color: #fff;
}

body.dark-mode .orders-table td {
    color: #b0b0b0;
    border-color: #1a1a2e;
}

/* Responsive */
@media (max-width: 768px) {
    .profile-grid {
        grid-template-columns: 1fr;
    }
}

/* Alert styles */
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
</style>

<script>
function validatePassword() {
    const newPass = document.getElementById('new_password').value;
    const confirmPass = document.getElementById('confirm_password').value;
    
    if (newPass !== confirmPass) {
        alert('New passwords do not match!');
        return false;
    }
    return true;
}

// Real-time password match indicator
document.getElementById('confirm_password')?.addEventListener('keyup', function() {
    const newPass = document.getElementById('new_password').value;
    const match = document.getElementById('passwordMatch');
    
    if (this.value === newPass) {
        match.innerHTML = '✅ Passwords match';
        match.style.color = 'green';
    } else {
        match.innerHTML = '❌ Passwords do not match';
        match.style.color = 'red';
    }
});

// Password strength indicator
document.getElementById('new_password')?.addEventListener('keyup', function() {
    const strength = document.getElementById('passwordStrength');
    const pass = this.value;
    
    if (pass.length < 6) {
        strength.innerHTML = 'Weak - too short';
        strength.style.color = 'red';
    } else if (pass.match(/[0-9]/) && pass.match(/[a-zA-Z]/)) {
        strength.innerHTML = 'Strong - good password';
        strength.style.color = 'green';
    } else {
        strength.innerHTML = 'Medium - add numbers for stronger password';
        strength.style.color = 'orange';
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>