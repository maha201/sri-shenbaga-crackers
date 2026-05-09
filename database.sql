-- Crackers Website Database Schema
CREATE DATABASE IF NOT EXISTS crackers_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE crackers_db;

-- Categories
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(200) NOT NULL,
    sale_type VARCHAR(50) DEFAULT 'Pkt',
    actual_price DECIMAL(10,2) NOT NULL,
    discount_percent DECIMAL(5,2) DEFAULT 80.00,
    image VARCHAR(255) DEFAULT '',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- Orders
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(20) NOT NULL UNIQUE,
    customer_name VARCHAR(100) NOT NULL,
    customer_phone VARCHAR(20) NOT NULL,
    customer_email VARCHAR(100),
    customer_address TEXT NOT NULL,
    customer_city VARCHAR(100) NOT NULL,
    customer_state VARCHAR(100) DEFAULT 'Tamil Nadu',
    customer_pincode VARCHAR(10) NOT NULL,
    total_actual_price DECIMAL(10,2) NOT NULL,
    total_discount_amount DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    status ENUM('pending','confirmed','processing','shipped','delivered','cancelled') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Order Items
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(200) NOT NULL,
    sale_type VARCHAR(50),
    actual_price DECIMAL(10,2) NOT NULL,
    discount_percent DECIMAL(5,2) NOT NULL,
    discount_price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT
);

-- Admin users
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin (password: admin123)
INSERT INTO admin_users (username, password) VALUES 
('admin', '$2y$10$88ZGFZHsj8qB2Ja6RxsWou8n07F2mAEFQczhdGkqxVEXbV3cxwg9y')
ON DUPLICATE KEY UPDATE username=username;

-- Insert Categories
INSERT INTO categories (name, sort_order) VALUES
('Sound Crackers', 1),
('Flower Pots', 2),
('Fancy Shower Pots', 3),
('Ground Chakkars', 4),
('Fancy Chakkars', 5),
('Pencil', 6),
('Bijili Crackers', 7),
('Bombs', 8),
('Kids Novelties', 9),
('Rockets', 10),
('Aerial Fancy', 11),
('Sky Shots', 12),
('Colour Matches', 13),
('Sparklers', 14),
('Gift Boxes', 15)
ON DUPLICATE KEY UPDATE name=name;

-- Insert Sample Products
INSERT INTO products (category_id, name, sale_type, actual_price, discount_percent) VALUES
-- Sound Crackers (cat 1)
(1, '4 Gold Lakshmi', 'Pkt', 155.00, 80.00),
(1, '4" Lakshmi', 'Pkt', 80.00, 80.00),
(1, '3 1/2" Lakshmi', 'Pkt', 65.00, 80.00),
(1, '2 3/4" Kuruvi', 'Pkt', 35.00, 80.00),
(1, '2 3/4" Kuruvi Deluxe', 'Pkt', 50.00, 80.00),
(1, '3 1/2" 2 Sound Crackers', 'Pkt', 140.00, 80.00),
(1, '4" 2 Sound Crackers', 'Pkt', 200.00, 80.00),
-- Flower Pots (cat 2)
(2, 'Flower Pot Small (10 Pcs)', '1 Box', 225.00, 80.00),
(2, 'Flower Pots Big (10 pcs)', 'Box', 275.00, 80.00),
(2, 'Flower Pots Special (10 pcs)', 'Box', 375.00, 80.00),
(2, 'Flower Pots Ashoka (10 pcs)', 'Box', 500.00, 80.00),
(2, 'Flower Pots Deluxe (5 pcs)', 'Box', 1000.00, 80.00),
(2, 'Colour Koti (10 pcs)', 'Box', 900.00, 80.00),
(2, 'Colour Koti Mega Deluxe (10 pcs)', 'Box', 2100.00, 80.00),
(2, 'Tri Colour Mega Fountain (5 pcs)', 'Box', 1250.00, 80.00),
-- Fancy Shower Pots (cat 3)
(3, 'Asharafi Pops (5 pcs)', 'Box', 175.00, 80.00),
(3, 'Teddy', 'Box', 250.00, 80.00),
(3, 'Pogo', 'Box', 375.00, 80.00),
(3, 'Mega Siren (3 pcs)', 'Box', 900.00, 80.00),
(3, 'Peacock', 'Box', 800.00, 80.00),
(3, 'Queen Shower', 'Box', 800.00, 80.00),
(3, 'Bada Peacock', 'Box', 2000.00, 80.00),
-- Ground Chakkars (cat 4)
(4, 'Ground Chakkar Special (10 pcs)', 'Box', 350.00, 80.00),
(4, 'Ground Chakkar Mega Special (10 pcs)', 'Box', 450.00, 80.00),
(4, 'Ground Chakkar Deluxe (10 pcs)', 'Box', 600.00, 80.00),
-- Sparklers (cat 14)
(14, 'Electric Sparklers (10 pcs)', 'Box', 150.00, 80.00),
(14, 'Colour Sparklers (10 pcs)', 'Box', 200.00, 80.00),
(14, 'Red Sparklers (10 pcs)', 'Box', 120.00, 80.00),
-- Gift Boxes (cat 15)
(15, 'Baby Gift Box', 'Box', 500.00, 80.00),
(15, 'Magical Gift Box', 'Box', 1000.00, 80.00),
(15, 'VIP Gift Box', 'Box', 2500.00, 80.00);
