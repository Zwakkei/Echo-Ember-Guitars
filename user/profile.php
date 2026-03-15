<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = intval($_SESSION['user_id']);
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // UPDATE PROFILE
    if (isset($_POST['update_profile'])) {

        $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $phone = mysqli_real_escape_string($conn, $_POST['phone']);
        $address = mysqli_real_escape_string($conn, $_POST['address']);

        $sql = "UPDATE users SET 
                full_name='$full_name',
                email='$email',
                phone='$phone',
                address='$address'
                WHERE user_id='$user_id'";

        if (mysqli_query($conn, $sql)) {
            $_SESSION['full_name'] = $full_name;
            header("Location: profile.php?success=updated");
            exit();
        } else {
            $message = "❌ Error updating profile: " . mysqli_error($conn);
        }
    }

    // CHANGE PASSWORD
    if (isset($_POST['change_password'])) {

        $current = $_POST['current_password'];
        $new = $_POST['new_password'];
        $confirm = $_POST['confirm_password'];

        $result = mysqli_query($conn, "SELECT password FROM users WHERE user_id='$user_id'");
        $user_data = mysqli_fetch_assoc($result);

        if (password_verify($current, $user_data['password'])) {

            if ($new === $confirm) {

                $hashed = password_hash($new, PASSWORD_DEFAULT);

                $sql = "UPDATE users SET password='$hashed' WHERE user_id='$user_id'";

                if (mysqli_query($conn, $sql)) {
                    header("Location: profile.php?success=password");
                    exit();
                } else {
                    $message = "❌ Error updating password.";
                }

            } else {
                $message = "❌ New passwords do not match!";
            }

        } else {
            $message = "❌ Current password incorrect!";
        }
    }
}

// GET USER DATA
$result = mysqli_query($conn, "SELECT * FROM users WHERE user_id='$user_id'");
$user = mysqli_fetch_assoc($result);

$_SESSION['full_name'] = $user['full_name'];

if (isset($_GET['success'])) {
    if ($_GET['success'] == 'updated') {
        $message = "✅ Profile updated successfully!";
    }
    if ($_GET['success'] == 'password') {
        $message = "✅ Password changed successfully!";
    }
}
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<div style="max-width:800px;margin:40px auto;padding:20px;">

<h1 style="text-align:center;">👤 My Profile</h1>

<?php if ($message): ?>
<div style="padding:15px;background:#d4edda;color:#155724;border-radius:5px;margin-bottom:20px;text-align:center;font-weight:bold;">
<?php echo $message; ?>
</div>
<?php endif; ?>

<!-- CURRENT DATA -->
<div style="background:#e3f2fd;padding:20px;margin-bottom:20px;border-radius:5px;">
<h3>Current Information in Database</h3>

<p><b>Name:</b> <?php echo $user['full_name']; ?></p>
<p><b>Email:</b> <?php echo $user['email']; ?></p>
<p><b>Phone:</b> <?php echo $user['phone']; ?></p>
<p><b>Address:</b> <?php echo $user['address']; ?></p>

</div>

<!-- PROFILE FORM -->
<div style="background:white;padding:30px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.1);margin-bottom:30px;">

<h2>Edit Profile</h2>

<form method="POST">

<input type="hidden" name="update_profile" value="1">

<div style="margin-bottom:15px;">
<label>Full Name</label>
<input type="text" name="full_name" value="<?php echo $user['full_name']; ?>" style="width:100%;padding:10px;">
</div>

<div style="margin-bottom:15px;">
<label>Email</label>
<input type="email" name="email" value="<?php echo $user['email']; ?>" style="width:100%;padding:10px;">
</div>

<div style="margin-bottom:15px;">
<label>Phone</label>
<input type="text" name="phone" value="<?php echo $user['phone']; ?>" style="width:100%;padding:10px;">
</div>

<div style="margin-bottom:15px;">
<label>Address</label>
<textarea name="address" style="width:100%;padding:10px;"><?php echo $user['address']; ?></textarea>
</div>

<button type="submit" style="width:100%;padding:12px;background:#667eea;color:white;border:none;border-radius:5px;font-size:16px;">
Update Profile
</button>

</form>

</div>

<!-- PASSWORD FORM -->
<div style="background:white;padding:30px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.1);">

<h2>Change Password</h2>

<form method="POST">

<input type="hidden" name="change_password" value="1">

<div style="margin-bottom:15px;">
<label>Current Password</label>
<input type="password" name="current_password" required style="width:100%;padding:10px;">
</div>

<div style="margin-bottom:15px;">
<label>New Password</label>
<input type="password" name="new_password" required style="width:100%;padding:10px;">
</div>

<div style="margin-bottom:15px;">
<label>Confirm New Password</label>
<input type="password" name="confirm_password" required style="width:100%;padding:10px;">
</div>

<button type="submit" style="width:100%;padding:12px;background:#ff6b6b;color:white;border:none;border-radius:5px;font-size:16px;">
Change Password
</button>

</form>

</div>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
