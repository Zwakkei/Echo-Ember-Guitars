<?php
require_once __DIR__ . '/../config/database.php';

function registerUser($username, $email, $password, $full_name, $address, $phone) {
    global $conn;
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Prepare statement to prevent SQL injection
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, address, phone) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $username, $email, $hashed_password, $full_name, $address, $phone);
    
    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}

function loginUser($username, $password) {
    global $conn;
    
    // Prepare statement
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];
            
            // Get cart count
            $cart_stmt = $conn->prepare("SELECT COUNT(*) as count FROM cart WHERE user_id = ?");
            $cart_stmt->bind_param("i", $user['user_id']);
            $cart_stmt->execute();
            $cart_result = $cart_stmt->get_result();
            $cart_data = $cart_result->fetch_assoc();
            $_SESSION['cart_count'] = $cart_data['count'];
            
            return true;
        }
    }
    return false;
}

function logoutUser() {
    session_destroy();
    redirect('../index.php');
}
?>