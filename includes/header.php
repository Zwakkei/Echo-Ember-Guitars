<?php
// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get wishlist count if user is logged in
$wishlist_count = 0;
if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] !== 'admin') {
    $database_path = __DIR__ . '/../config/database.php';
    if (file_exists($database_path)) {
        require_once $database_path;
        if (isset($conn) && $conn) {
            $user_id = $_SESSION['user_id'];
            $result = $conn->query("SELECT COUNT(*) as count FROM wishlist WHERE user_id = $user_id");
            if ($result && $result->num_rows > 0) {
                $wishlist_count = $result->fetch_assoc()['count'];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Echo & Ember Guitars</title>
    <link rel="stylesheet" href="/echo-ember-guitars/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
    /* Reset and base styles */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    /* Navbar styles */
    .navbar {
        background: linear-gradient(135deg, #2c3e50, #1a1a2e);
        color: white;
        padding: 1rem 0;
        position: sticky;
        top: 0;
        z-index: 1000;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .nav-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    /* Left section - Brand */
    .nav-left {
        flex: 0 0 auto;
    }

    .nav-brand a {
        color: white;
        text-decoration: none;
        font-size: 1.5rem;
        font-weight: 700;
        letter-spacing: 1px;
        white-space: nowrap;
    }

    .nav-brand a:hover {
        color: #ff6b6b;
    }

    /* Center section - Navigation links */
    .nav-center {
        flex: 1;
        display: flex;
        justify-content: center;
    }

    .nav-menu {
        display: flex;
        list-style: none;
        gap: 2rem;
        margin: 0;
        padding: 0;
    }

    .nav-menu li {
        position: relative;
    }

    .nav-menu a {
        color: white;
        text-decoration: none;
        font-weight: 500;
        transition: color 0.3s;
        display: flex;
        align-items: center;
        gap: 5px;
        white-space: nowrap;
    }

    .nav-menu a:hover {
        color: #ff6b6b;
    }

    /* Right section - User info and dark mode */
    .nav-right {
        flex: 0 0 auto;
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .user-info {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .user-name {
        color: white;
        font-weight: 500;
    }

    .logout-link {
        color: #ff6b6b;
        text-decoration: none;
        font-weight: 600;
        transition: color 0.3s;
    }

    .logout-link:hover {
        color: #ff5252;
    }

    /* Profile link styles */
    .profile-link {
        color: white;
        text-decoration: none;
        padding: 5px 12px;
        border-radius: 5px;
        background: rgba(255,255,255,0.15);
        transition: all 0.3s;
        margin: 0 5px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        border: 1px solid rgba(255,255,255,0.1);
    }

    .profile-link:hover {
        background: #ff6b6b;
        color: white;
        transform: translateY(-2px);
        border-color: transparent;
        box-shadow: 0 5px 15px rgba(255, 107, 107, 0.3);
    }

    /* Dark mode support for profile link */
    body.dark-mode .profile-link {
        background: rgba(255,255,255,0.1);
        border-color: rgba(255,255,255,0.05);
    }

    body.dark-mode .profile-link:hover {
        background: #ff6b6b;
        color: white;
    }

    /* Dark mode toggle */
    .dark-mode-btn {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        padding: 5px;
        border-radius: 50%;
        transition: all 0.3s;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .dark-mode-btn:hover {
        transform: scale(1.1);
        background: rgba(255,255,255,0.1);
    }

    /* Badges */
    .cart-badge, .wishlist-badge {
        background: #ff6b6b;
        color: white;
        border-radius: 50%;
        padding: 2px 6px;
        font-size: 0.7rem;
        margin-left: 3px;
        position: relative;
        top: -8px;
    }

    /* Search Section */
    .search-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 20px 0;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    .search-container {
        max-width: 800px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .big-search-form {
        display: flex;
        gap: 10px;
        background: white;
        border-radius: 50px;
        padding: 5px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.2);
    }

    .big-search-input {
        flex: 1;
        padding: 15px 25px;
        border: none;
        border-radius: 50px;
        font-size: 1.1rem;
        outline: none;
        background: transparent;
    }

    .big-search-btn {
        background: linear-gradient(135deg, #ff6b6b 0%, #ff8e8e 100%);
        color: white;
        border: none;
        border-radius: 50px;
        padding: 0 35px;
        font-size: 1.1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        white-space: nowrap;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .big-search-btn:hover {
        transform: scale(1.05);
        box-shadow: 0 5px 15px rgba(255, 107, 107, 0.4);
    }

    .search-suggestions {
        margin-top: 10px;
        display: flex;
        gap: 15px;
        justify-content: center;
        flex-wrap: wrap;
    }

    .search-suggestions a {
        color: rgba(255,255,255,0.8);
        text-decoration: none;
        font-size: 0.9rem;
        padding: 5px 15px;
        background: rgba(255,255,255,0.1);
        border-radius: 20px;
        transition: all 0.3s;
    }

    .search-suggestions a:hover {
        background: rgba(255,255,255,0.2);
        color: white;
    }

    /* Hamburger menu for mobile */
    .hamburger {
        display: none;
        flex-direction: column;
        cursor: pointer;
    }

    .hamburger span {
        width: 25px;
        height: 3px;
        background: white;
        margin: 2px 0;
        transition: 0.3s;
    }

    /* Dark Mode Styles */
    body.dark-mode {
        background-color: #1a1a2e;
        color: #fff;
    }

    body.dark-mode .navbar {
        background: linear-gradient(135deg, #16213e, #0f3460) !important;
    }

    body.dark-mode .search-section {
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
    }

    /* ===== LOADING SPINNER STYLES ===== */
    #loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.85);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        backdrop-filter: blur(10px);
        transition: opacity 0.3s ease;
    }

    .loading-spinner {
        position: relative;
        text-align: center;
        background: white;
        padding: 50px 60px;
        border-radius: 30px;
        box-shadow: 0 30px 60px rgba(0,0,0,0.4);
        animation: guitarSlide 0.5s ease;
        border: 3px solid transparent;
        background-clip: padding-box;
    }

    .loading-spinner::before {
        content: '';
        position: absolute;
        top: -3px;
        left: -3px;
        right: -3px;
        bottom: -3px;
        background: linear-gradient(45deg, #ff6b6b, #667eea, #4CAF50, #ffc107);
        border-radius: 32px;
        z-index: -1;
        animation: borderRotate 3s linear infinite;
    }

    @keyframes borderRotate {
        0% { filter: hue-rotate(0deg); }
        100% { filter: hue-rotate(360deg); }
    }

    .guitar-icon {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 3rem;
        animation: guitarWobble 1s ease infinite;
        z-index: 2;
        text-shadow: 0 0 20px rgba(255,107,107,0.5);
    }

    .spinner {
        width: 100px;
        height: 100px;
        border: 6px solid #f3f3f3;
        border-top: 6px solid #ff6b6b;
        border-right: 6px solid #667eea;
        border-bottom: 6px solid #4CAF50;
        border-left: 6px solid #ffc107;
        border-radius: 50%;
        animation: spin 1.2s cubic-bezier(0.68, -0.55, 0.265, 1.55) infinite;
        margin: 0 auto 25px;
        box-shadow: 0 0 30px rgba(102,126,234,0.3);
    }

    @keyframes spin {
        0% { transform: rotate(0deg) scale(1); }
        50% { transform: rotate(180deg) scale(1.1); }
        100% { transform: rotate(360deg) scale(1); }
    }

    @keyframes guitarWobble {
        0%, 100% { transform: translate(-50%, -50%) rotate(0deg) scale(1); }
        25% { transform: translate(-50%, -50%) rotate(-15deg) scale(1.1); }
        75% { transform: translate(-50%, -50%) rotate(15deg) scale(1.1); }
    }

    @keyframes guitarSlide {
        from {
            opacity: 0;
            transform: translateY(-50px) scale(0.8);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    .loading-text {
        font-size: 1.3rem;
        color: #333;
        margin: 15px 0 5px;
        font-weight: 600;
        background: linear-gradient(45deg, #ff6b6b, #667eea);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        animation: textPulse 2s ease infinite;
    }

    .loading-progress {
        width: 200px;
        height: 4px;
        background: #f0f0f0;
        border-radius: 4px;
        margin: 15px auto 0;
        overflow: hidden;
    }

    .loading-progress-bar {
        height: 100%;
        background: linear-gradient(90deg, #ff6b6b, #667eea);
        width: 0%;
        animation: progress 2s ease infinite;
    }

    @keyframes progress {
        0% { width: 0%; }
        50% { width: 70%; }
        100% { width: 100%; }
    }

    @keyframes textPulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.8; transform: scale(0.98); }
    }

    /* Dark mode loading spinner */
    body.dark-mode .loading-spinner {
        background: #16213e;
    }

    body.dark-mode .loading-text {
        background: linear-gradient(45deg, #ffb8b8, #b8b8ff);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    /* Page transition */
    main {
        animation: fadeInPage 0.5s ease;
    }

    @keyframes fadeInPage {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Mobile Responsive */
    @media (max-width: 768px) {
        .nav-container {
            flex-wrap: wrap;
        }

        .hamburger {
            display: flex;
            order: 2;
        }

        .nav-center {
            order: 3;
            flex: 0 0 100%;
            margin-top: 15px;
        }

        .nav-menu {
            flex-direction: column;
            align-items: center;
            gap: 1rem;
        }

        .nav-right {
            order: 1;
            margin-left: auto;
        }

        .big-search-form {
            flex-direction: column;
        }

        .big-search-btn {
            justify-content: center;
        }

        .loading-spinner {
            padding: 30px 40px;
        }

        .spinner {
            width: 70px;
            height: 70px;
        }

        .guitar-icon {
            font-size: 2rem;
        }
    }
    </style>
</head>
<body>
    <!-- LOADING SPINNER HTML -->
    <div id="loading-overlay">
        <div class="loading-spinner">
            <div class="spinner"></div>
            <div class="guitar-icon">🎸</div>
            <p class="loading-text">Strumming up your page...</p>
            <div class="loading-progress">
                <div class="loading-progress-bar"></div>
            </div>
        </div>
    </div>

    <header>
        <!-- Top Navigation Bar -->
        <nav class="navbar">
            <div class="nav-container">
                <!-- LEFT: Brand -->
                <div class="nav-left">
                    <div class="nav-brand">
                        <a href="/echo-ember-guitars/index.php">🎸 Echo & Ember Guitars</a>
                    </div>
                </div>

                <!-- CENTER: Navigation Links -->
                <div class="nav-center">
                    <ul class="nav-menu">
                        <li><a href="/echo-ember-guitars/index.php">Home</a></li>
                        <li><a href="/echo-ember-guitars/shop.php">Products</a></li>
                        
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <?php if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'): ?>
                            <li>
                                <a href="/echo-ember-guitars/wishlist.php">
                                    Wishlist
                                    <?php if ($wishlist_count > 0): ?>
                                        <span class="wishlist-badge"><?php echo $wishlist_count; ?></span>
                                    <?php endif; ?>
                                </a>
                            </li>
                            <?php endif; ?>
                            
                            <li><a href="/echo-ember-guitars/cart/view_cart.php">
                                Cart 
                                <?php if (isset($_SESSION['cart_count']) && $_SESSION['cart_count'] > 0): ?>
                                    <span class="cart-badge"><?php echo $_SESSION['cart_count']; ?></span>
                                <?php endif; ?>
                            </a></li>
                            
                            <li><a href="/echo-ember-guitars/user/orders.php">My Orders</a></li>
                            
                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                <li><a href="/echo-ember-guitars/admin/index.php">Admin Panel</a></li>
                            <?php endif; ?>
                        <?php else: ?>
                            <li><a href="/echo-ember-guitars/cart/view_cart.php">Cart</a></li>
                            <li><a href="/echo-ember-guitars/user/login.php">Login</a></li>
                            <li><a href="/echo-ember-guitars/user/register.php">Register</a></li>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- RIGHT: User Info & Dark Mode -->
                <div class="nav-right">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="user-info">
                            <span class="user-name"><?php echo $_SESSION['username'] ?? 'User'; ?></span>
                            <a href="/echo-ember-guitars/user/profile.php" class="profile-link">👤 Profile</a>
                            <a href="/echo-ember-guitars/user/logout.php" class="logout-link">Logout</a>
                        </div>
                    <?php endif; ?>
                    
                    <button id="darkModeToggle" class="dark-mode-btn" onclick="toggleDarkMode()">🌙</button>
                </div>

                <!-- Hamburger Menu for Mobile -->
                <div class="hamburger" onclick="toggleMenu()">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </nav>

        <!-- Search Section -->
        <div class="search-section">
            <div class="search-container">
                <form action="/echo-ember-guitars/shop.php" method="GET" class="big-search-form">
                    <input type="text" 
                           name="search" 
                           placeholder="Search for guitars, amps, pedals, strings, accessories..." 
                           value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                           class="big-search-input"
                           autocomplete="off">
                    <button type="submit" class="big-search-btn">
                        🔍 Search
                    </button>
                </form>
                
                <div class="search-suggestions">
                    <a href="/echo-ember-guitars/shop.php?search=electric+guitar">⚡ Electric Guitars</a>
                    <a href="/echo-ember-guitars/shop.php?search=acoustic+guitar">🎸 Acoustic Guitars</a>
                    <a href="/echo-ember-guitars/shop.php?search=amplifier">🔊 Amplifiers</a>
                    <a href="/echo-ember-guitars/shop.php?search=pedal">🎛️ Pedals</a>
                    <a href="/echo-ember-guitars/shop.php?search=strings">🎵 Strings</a>
                    <a href="/echo-ember-guitars/shop.php?search=accessories">🎒 Accessories</a>
                </div>
            </div>
        </div>
    </header>
    <main>

<script>
// Dark Mode Toggle
function toggleDarkMode() {
    document.body.classList.toggle('dark-mode');
    const isDark = document.body.classList.contains('dark-mode');
    localStorage.setItem('darkMode', isDark ? 'enabled' : 'disabled');
    const toggleBtn = document.getElementById('darkModeToggle');
    if (toggleBtn) {
        toggleBtn.innerHTML = isDark ? '☀️' : '🌙';
    }
}

// Check for saved dark mode preference
document.addEventListener('DOMContentLoaded', function() {
    const darkMode = localStorage.getItem('darkMode') === 'enabled';
    if (darkMode) {
        document.body.classList.add('dark-mode');
        const toggleBtn = document.getElementById('darkModeToggle');
        if (toggleBtn) {
            toggleBtn.innerHTML = '☀️';
        }
    }
});

// Mobile Menu Toggle
function toggleMenu() {
    const navMenu = document.querySelector('.nav-menu');
    navMenu.classList.toggle('active');
}

// Close mobile menu when clicking a link
document.querySelectorAll('.nav-menu a').forEach(link => {
    link.addEventListener('click', () => {
        document.querySelector('.nav-menu').classList.remove('active');
    });
});

// LOADING SPINNER SCRIPT - With minimum display time
document.addEventListener('DOMContentLoaded', function() {
    const loadingOverlay = document.getElementById('loading-overlay');
    const loadingText = document.querySelector('.loading-text');
    const messages = [
        'Strumming up your page...',
        'Tuning your experience...',
        'Amplifying content...',
        'Rocking and rolling...',
        'Plucking some strings...',
        'Finding the right chord...',
        'Setting the stage...',
        'Preparing your gear...',
        'Warming up the amps...',
        'Checking the tuning...'
    ];
    
    let loadingStartTime = 0;
    const MINIMUM_LOADING_TIME = 800; // milliseconds (0.8 seconds)
    
    // Show loading on all link clicks
    document.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href && !href.startsWith('#') && !href.startsWith('http') && !href.startsWith('javascript') && !href.startsWith('mailto')) {
                e.preventDefault(); // Stop immediate navigation
                
                // Record start time and show spinner
                loadingStartTime = Date.now();
                const randomMsg = messages[Math.floor(Math.random() * messages.length)];
                if (loadingText) loadingText.textContent = randomMsg;
                loadingOverlay.style.display = 'flex';
                
                // Navigate after a short delay
                setTimeout(() => {
                    window.location.href = href;
                }, 300);
            }
        });
    });
    
    // Show loading on form submissions
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault(); // Stop immediate submission
            
            loadingStartTime = Date.now();
            const randomMsg = messages[Math.floor(Math.random() * messages.length)];
            if (loadingText) loadingText.textContent = randomMsg;
            loadingOverlay.style.display = 'flex';
            
            // Submit form after delay
            setTimeout(() => {
                this.submit();
            }, 300);
        });
    });
    
    // Hide loading when page is fully loaded
    window.addEventListener('load', function() {
        const loadTime = Date.now() - loadingStartTime;
        const remainingTime = Math.max(0, MINIMUM_LOADING_TIME - loadTime);
        
        // Ensure spinner shows for at least MINIMUM_LOADING_TIME
        setTimeout(() => {
            loadingOverlay.style.display = 'none';
        }, remainingTime);
    });
    
    // Handle back/forward browser buttons
    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            setTimeout(() => {
                loadingOverlay.style.display = 'none';
            }, 300);
        }
    });
});

// Manual control functions for loading spinner
function showLoading(message = 'Loading...', minTime = 800) {
    const overlay = document.getElementById('loading-overlay');
    const textEl = document.querySelector('.loading-text');
    if (textEl) textEl.textContent = message;
    overlay.style.display = 'flex';
    overlay.dataset.startTime = Date.now();
    overlay.dataset.minTime = minTime;
}

function hideLoading() {
    const overlay = document.getElementById('loading-overlay');
    const startTime = parseInt(overlay.dataset.startTime || '0');
    const minTime = parseInt(overlay.dataset.minTime || '800');
    const elapsed = Date.now() - startTime;
    const remaining = Math.max(0, minTime - elapsed);
    
    setTimeout(() => {
        overlay.style.display = 'none';
    }, remaining);
}
</script>