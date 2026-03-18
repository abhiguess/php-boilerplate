-- Create database
CREATE DATABASE IF NOT EXISTS php_boilerplate CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE php_boilerplate;

-- Users table (sample)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) DEFAULT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    api_token VARCHAR(64) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Posts table (belongsTo User)
CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    body TEXT NOT NULL,
    status ENUM('draft', 'published') DEFAULT 'draft',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample data
-- password is 'password' for both users
INSERT INTO users (name, email, password, phone) VALUES
('John Doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '1234567890'),
('Jane Smith', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0987654321');

INSERT INTO posts (user_id, title, body, status) VALUES
(1, 'Getting Started with PHP', 'PHP is a popular server-side scripting language suited for web development. In this post we explore the basics.', 'published'),
(1, 'Understanding MVC Pattern', 'Model-View-Controller is a design pattern that separates application logic into three components.', 'published'),
(2, 'My Draft Post', 'This is still a work in progress and not yet ready for publishing.', 'draft');
