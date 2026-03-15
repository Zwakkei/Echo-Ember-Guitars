# 🚀 Deployment Guide for Echo & Ember Guitars

## Deploy to InfinityFree (Free Hosting)

### Step 1: Create Account
1. Go to [infinityfree.net](https://infinityfree.net)
2. Sign up for free account
3. Verify email
4. Choose subdomain (e.g., `echoember.epizy.com`)

### Step 2: Create Database
1. In control panel, go to "MySQL Databases"
2. Create new database
3. Save credentials:
   - Database name: `if0_12345678_echoember`
   - Username: `if0_12345678`
   - Password: `your_password`
   - Host: `sql123.infinityfree.com`

### Step 3: Import Database
1. Access phpMyAdmin from control panel
2. Select your database
3. Import `sql/database.sql`

### Step 4: Configure Files
1. Copy `config/database.example.php` to `config/database.php`
2. Update with your InfinityFree credentials:
   ```php
   define('DB_HOST', 'sql123.infinityfree.com');
   define('DB_USER', 'if0_12345678');
   define('DB_PASS', 'your_password');
   define('DB_NAME', 'if0_12345678_echoember');