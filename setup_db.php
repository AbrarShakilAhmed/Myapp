<?php
require_once 'config.php';

try {
    // Create database
    $conn = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
    $conn->exec("USE " . DB_NAME);

    // Create tables
    $conn->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT PRIMARY KEY AUTO_INCREMENT,
            username VARCHAR(50) NOT NULL UNIQUE,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            profile_image VARCHAR(255),
            bio TEXT,
            role ENUM('admin', 'author', 'user') DEFAULT 'user',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");

    $conn->exec("
        CREATE TABLE IF NOT EXISTS categories (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(50) NOT NULL UNIQUE,
            slug VARCHAR(50) NOT NULL UNIQUE,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $conn->exec("
        CREATE TABLE IF NOT EXISTS posts (
            id INT PRIMARY KEY AUTO_INCREMENT,
            title VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL UNIQUE,
            content TEXT NOT NULL,
            excerpt TEXT,
            featured_image VARCHAR(255),
            author_id INT,
            category_id INT,
            status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
            views INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL,
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
        )
    ");

    $conn->exec("
        CREATE TABLE IF NOT EXISTS contact_messages (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            message TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // Insert sample data
    $conn->exec("
        INSERT INTO users (username, email, password, role) 
        VALUES ('admin', 'admin@example.com', '" . password_hash('password123', PASSWORD_DEFAULT) . "', 'admin')
        ON DUPLICATE KEY UPDATE id=id
    ");

    $conn->exec("
        INSERT INTO categories (name, slug, description) 
        VALUES 
        ('Technology', 'technology', 'Posts about technology and innovation'),
        ('Design', 'design', 'UI/UX and creative design'),
        ('Education', 'education', 'Learning and development'),
        ('Lifestyle', 'lifestyle', 'Health, wellness, and personal growth')
        ON DUPLICATE KEY UPDATE id=id
    ");

    // Insert sample blog posts
    $conn->exec("
        INSERT INTO posts (title, slug, content, excerpt, author_id, category_id, status) 
        VALUES 
        (
            'Getting Started with Web Development',
            'getting-started-with-web-development',
            'Web development is an exciting field that combines creativity with technical skills. In this comprehensive guide, we will explore the fundamental concepts and tools you need to begin your journey as a web developer...',
            'Learn the basics of web development and start your journey in this exciting field.',
            1,
            1,
            'published'
        ),
        (
            'The Art of Minimalist Design',
            'art-of-minimalist-design',
            'Minimalist design principles focus on simplicity and functionality. This post explores how to create clean, effective designs that communicate your message without unnecessary elements...',
            'Discover the principles of minimalist design and how they can enhance your creative projects.',
            1,
            2,
            'published'
        ),
        (
            'Effective Learning Strategies',
            'effective-learning-strategies',
            'Learning is a lifelong journey, and having the right strategies can make all the difference. This post covers proven techniques for improving your learning efficiency and retention...',
            'Explore proven techniques to enhance your learning experience and improve knowledge retention.',
            1,
            3,
            'published'
        ),
        (
            'Balancing Work and Wellness',
            'balancing-work-and-wellness',
            'In today\'s fast-paced world, maintaining a healthy work-life balance is more important than ever. Learn practical tips and strategies for managing your time and energy effectively...',
            'Practical tips and strategies for maintaining work-life balance in the digital age.',
            1,
            4,
            'published'
        )
        ON DUPLICATE KEY UPDATE id=id
    ");

    echo "Database setup completed successfully!";
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
} 