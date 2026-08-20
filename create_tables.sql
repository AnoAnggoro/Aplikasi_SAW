-- Database: buat database bernama 'ta' atau ubah sesuai kebutuhan
CREATE DATABASE IF NOT EXISTS ta CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE ta;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) UNIQUE,
  password VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  kode VARCHAR(50),
  nama VARCHAR(255),
  stok INT DEFAULT 0
);

CREATE TABLE IF NOT EXISTS sales (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT,
  qty INT,
  sales_date DATE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS criteria (
  id INT AUTO_INCREMENT PRIMARY KEY,
  kode VARCHAR(50),
  nama VARCHAR(255),
  bobot DOUBLE DEFAULT 1,
  atribut ENUM('benefit','cost') DEFAULT 'benefit'
);

CREATE TABLE IF NOT EXISTS product_values (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT,
  criteria_id INT,
  nilai DOUBLE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  FOREIGN KEY (criteria_id) REFERENCES criteria(id) ON DELETE CASCADE
);

-- Insert demo admin (password: admin)
INSERT INTO users (username, password) VALUES ('admin', 'admin') ON DUPLICATE KEY UPDATE username=username;
