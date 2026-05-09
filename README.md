# Sivakasi Crackers Website

A complete PHP + MySQL crackers e-commerce website with admin panel.

## Features
- Full product catalog with category grouping
- 80% discount system (per-product, fully configurable)
- Add to cart (session-based), order checkout
- Admin panel: Products CRUD, Categories CRUD, Orders management
- Bulk discount updater (global or per-category)
- Order status tracking
- Product image uploads
- Responsive design (mobile-friendly)

---

## Requirements
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache with mod_rewrite (or Nginx)
- Web server running locally (XAMPP / WAMP / LAMP recommended)

---

## Installation Steps

### 1. Copy Files
Place the `crackers/` folder inside your web root:
- **XAMPP**: `C:/xampp/htdocs/crackers/`
- **WAMP**: `C:/wamp64/www/crackers/`
- **Linux**: `/var/www/html/crackers/`

### 2. Create Database
Open **phpMyAdmin** (http://localhost/phpmyadmin) and:
1. Click **Import**
2. Select the file `database.sql`
3. Click **Go**

This creates the `crackers_db` database with all tables and sample data.

### 3. Configure Database Connection
Open `includes/config.php` and update if needed:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // your MySQL username
define('DB_PASS', '');           // your MySQL password
define('DB_NAME', 'crackers_db');
```

### 4. Set Uploads Permission
Make sure the `uploads/` folder is writable:
```bash
chmod 755 uploads/
```

### 5. Visit the Website
- **Website**: http://localhost/crackers/
- **Admin Panel**: http://localhost/crackers/admin/login.php

### Default Admin Login
| Username | Password |
|----------|----------|
| admin    | admin123 |

> **Important**: Change the password after first login via Admin → Change Password

---

## Admin Panel Guide

| Page | URL | What it does |
|------|-----|-------------|
| Dashboard | /admin/dashboard.php | Overview stats & recent orders |
| Products | /admin/products.php | Add/Edit/Delete products, set price & discount |
| Categories | /admin/categories.php | Manage product categories |
| Orders | /admin/orders.php | View all orders, update status |
| Order Detail | /admin/order_view.php?id=X | Full order breakdown with discount calc |
| Bulk Discounts | /admin/discounts.php | Set discount % per category or globally |
| Change Password | /admin/change_password.php | Update admin password |

---

## How Discounts Work

- Each product has an **Actual Price (MRP)** and a **Discount %**
- **Offer Price = Actual Price × (1 - Discount% / 100)**
- Example: MRP ₹500, Discount 80% → Offer Price = ₹100
- **Total = Offer Price × Quantity**
- You can set different discounts per product or apply bulk discount per category
- The order stores: actual price, discount %, offer price, quantity, and line total — all calculated correctly

---

## Folder Structure
```
crackers/
├── index.php           # Homepage
├── products.php        # Product listing with cart
├── cart.php            # Shopping cart
├── cart_action.php     # Cart AJAX handler
├── checkout.php        # Order checkout form
├── about.php           # About page
├── contact.php         # Contact page
├── database.sql        # SQL schema + sample data
├── .htaccess           # Apache config
├── includes/
│   ├── config.php      # DB config + helper functions
│   ├── header.php      # Site header/navbar
│   └── footer.php      # Site footer
├── css/
│   └── style.css       # All styles
├── js/
│   └── main.js         # Cart AJAX + interactivity
├── uploads/            # Product images (writable)
└── admin/
    ├── login.php
    ├── logout.php
    ├── dashboard.php
    ├── products.php
    ├── categories.php
    ├── orders.php
    ├── order_view.php
    ├── discounts.php
    └── change_password.php
```

---

## Troubleshooting

**Database connection error**: Check `includes/config.php` credentials and ensure MySQL is running.

**Images not uploading**: Set `uploads/` folder permission to 755 or 777.

**404 errors**: Enable `mod_rewrite` in Apache. In XAMPP: Apache config → uncomment `LoadModule rewrite_module`.

**Session issues**: Ensure PHP session support is enabled (default in most installs).
