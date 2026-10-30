CREATE DATABASE IF NOT EXISTS nailart; USE nailart;
CREATE TABLE roles(role_id INT AUTO_INCREMENT PRIMARY KEY, role_name VARCHAR(20) NOT NULL UNIQUE);
INSERT INTO roles(role_name) VALUES('admin'),('client');
CREATE TABLE users(user_id INT AUTO_INCREMENT PRIMARY KEY, role_id INT NOT NULL, full_name VARCHAR(100) NOT NULL,
 email VARCHAR(100) NOT NULL UNIQUE, phone VARCHAR(20), password_hash VARCHAR(255) NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(role_id) REFERENCES roles(role_id));
CREATE TABLE services(service_id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100) NOT NULL, price DECIMAL(10,2) NOT NULL,
 duration_min INT NOT NULL, is_active TINYINT(1) DEFAULT 1);
CREATE TABLE designs(design_id INT AUTO_INCREMENT PRIMARY KEY, service_id INT NULL, title VARCHAR(100) NOT NULL,
 style VARCHAR(50) NOT NULL, image_path VARCHAR(255) NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(service_id) REFERENCES services(service_id) ON DELETE SET NULL);
CREATE TABLE appointments(appointment_id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NULL, guest_name VARCHAR(100), guest_email VARCHAR(100),
 service_id INT NOT NULL, start_time DATETIME NOT NULL, end_time DATETIME NOT NULL,
 status ENUM('pending','confirmed','completed','cancelled','no_show') DEFAULT 'pending', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(user_id) REFERENCES users(user_id) ON DELETE SET NULL, FOREIGN KEY(service_id) REFERENCES services(service_id));
CREATE TABLE blocked_slots(block_id INT AUTO_INCREMENT PRIMARY KEY, start_time DATETIME NOT NULL, end_time DATETIME NOT NULL, reason VARCHAR(100));
CREATE TABLE reviews(review_id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NULL, rating TINYINT NOT NULL, comment TEXT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(user_id) REFERENCES users(user_id) ON DELETE SET NULL);
INSERT INTO services(name,price,duration_min) VALUES('Gel Manicure',450,60),('Pedicure',500,60),('Nail Art',300,90),('Extensions',1200,120),('Removal',150,30);
