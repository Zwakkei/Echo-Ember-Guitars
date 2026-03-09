-- Create Database
CREATE DATABASE IF NOT EXISTS echo_ember_guitars;
USE echo_ember_guitars;

-- Users Table
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    address TEXT,
    phone VARCHAR(20),
    role ENUM('admin', 'customer') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products Table
CREATE TABLE products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    category VARCHAR(50),
    image_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Cart Table
CREATE TABLE cart (
    cart_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    product_id INT,
    quantity INT NOT NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
);

-- Orders Table
CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'processing', 'completed', 'cancelled') DEFAULT 'pending',
    shipping_address TEXT,
    payment_method VARCHAR(50),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Order Details Table
CREATE TABLE order_details (
    order_detail_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    product_id INT,
    quantity INT NOT NULL,
    price_at_time DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
);

-- Insert Sample Data
INSERT INTO users (username, email, password, full_name, role) VALUES 
('admin', 'admin@echoember.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin User', 'admin'),
('john_doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John Doe', 'customer');

INSERT INTO products (product_name, description, price, stock, category, image_path) VALUES
('Echo Vintage Acoustic', 'Classic acoustic guitar with rich, warm tone. Perfect for beginners and professionals.', 299.99, 10, 'Acoustic', 'uploads/products/acoustic1.jpg'),
('Ember Electric Pro', 'High-performance electric guitar with dual humbuckers and flamed maple top.', 599.99, 5, 'Electric', 'uploads/products/electric1.jpg'),
('Classical Nylon String', 'Traditional classical guitar with nylon strings, ideal for fingerstyle playing.', 249.99, 8, 'Classical', 'uploads/products/classical1.jpg'),
('Bass Guitar 4-String', 'Professional bass guitar with active electronics for deep, punchy sound.', 399.99, 6, 'Bass', 'uploads/products/bass1.jpg'),
('Acoustic-Electric Cutaway', 'Versatile acoustic-electric guitar with built-in tuner and pickup system.', 449.99, 7, 'Acoustic', 'uploads/products/acoustic2.jpg');