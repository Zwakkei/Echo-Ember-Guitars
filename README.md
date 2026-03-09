# 🎸 Echo & Ember Guitars

A complete e-commerce website for selling guitars, amplifiers, pedals, and accessories.

## ✨ Features

### Customer Features
- ✅ User registration & login with password hashing
- ✅ Product browsing with category filtering
- ✅ Advanced search with filters and sorting
- ✅ Shopping cart with quantity controls
- ✅ Checkout process
- ✅ Order history with details modal
- ✅ Wishlist functionality
- ✅ Product details page with gallery
- ✅ Dark mode toggle
- ✅ Responsive mobile design

### Admin Features
- ✅ Admin dashboard with statistics
- ✅ Product management (CRUD)
- ✅ Order management with status updates
- ✅ Order details view
- ✅ Inventory tracking
- ✅ Low stock alerts

## 🛠️ Technologies Used

- **Frontend:** HTML5, CSS3, JavaScript
- **Backend:** PHP 8.2+
- **Database:** MySQL 8.0+
- **Server:** Apache
- **Fonts:** Google Fonts (Montserrat)

## 📁 Project Structure

```
echo-ember-guitars/
├── admin/                 # Admin panel files
├── cart/                  # Shopping cart functionality
├── config/                # Database configuration
├── css/                   # Stylesheets
├── includes/              # Core includes (header, footer, auth)
├── js/                    # JavaScript files
├── sql/                   # Database schema
├── uploads/               # Product images
├── user/                  # User management
├── index.php              # Homepage
├── product.php            # Product details page
├── shop.php               # Shop with filters
└── wishlist.php           # Wishlist page
```

## 🚀 Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/echo-ember-guitars.git
   ```

2. **Move to XAMPP htdocs**
   ```bash
   mv echo-ember-guitars C:\xampp\htdocs\
   ```

3. **Create database**
   - Open phpMyAdmin: http://localhost/phpmyadmin
   - Create database: `echo_ember_guitars`
   - Import `sql/database.sql`

4. **Configure database**
   - Copy `config/database.example.php` to `config/database.php`
   - Update database credentials

5. **Set Permissions**
   ```bash
   chmod 777 uploads/products/
   ```

6. **Run the Project**
   - Visit: http://localhost/echo-ember-guitars/

## 🔑 Default Login

| Role     | Username  | Password |
|----------|-----------|----------|
| Admin    | admin     | password |
| Customer | john_doe  | password |

## 👥 Team Members

- Benz Wacky N. Ramayla
- Ezra Jay S. Palarca
- James Fedelito A. Sawit
