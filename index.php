<?php
require_once 'config.php';
require_once 'functions.php';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];

    if (login_user($email, $password)) {
        $_SESSION['login_time'] = time();
        header("Location: index.php");
        exit();
    } else {
        $login_error = "Invalid email or password";
    }
}

// Handle contact form submission with AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact'])) {
    $name = sanitize_input($_POST['name']);
    $email = sanitize_input($_POST['email']);
    $message = sanitize_input($_POST['message']);

    if (add_contact_message($name, $email, $message)) {
        $response = ['success' => true, 'message' => 'Message sent successfully!'];
    } else {
        $response = ['success' => false, 'message' => 'Failed to send message. Please try again.'];
    }

    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }
}

// Get featured content with caching
$cache_key = 'featured_content';
$featured_content = get_cached_data($cache_key);

if ($featured_content === false) {
    $featured_content = get_featured_content();
    cache_data($cache_key, $featured_content, 3600); // Cache for 1 hour
}

// Get user statistics
$user_stats = get_user_statistics();

// Check session timeout (30 minutes)
if (is_logged_in() && isset($_SESSION['login_time'])) {
    $session_timeout = 1800; // 30 minutes
    if (time() - $_SESSION['login_time'] > $session_timeout) {
        logout_user();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guraba Blog Post</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: #f5f5f5;
            color: #333;
        }

        .navbar {
            background-color: #ffffff;
            padding: 1rem 2rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }

        .nav-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2c3e50;
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
        }

        .nav-links a {
            text-decoration: none;
            color: #2c3e50;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .nav-links a:hover {
            color: #3498db;
        }

        .nav-links .login-btn {
            background-color: #6c5ce7;
            color: #ffffff;
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            transition: background-color 0.3s ease;
        }

        .nav-links .login-btn:hover {
            background-color: #5a4bd1;
        }

        .hero {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 0 1rem;
            background-image: radial-gradient(circle at 52% 94%, rgba(169, 169, 169,0.04) 0%, rgba(169, 169, 169,0.04) 50%,rgba(199, 199, 199,0.04) 50%, rgba(199, 199, 199,0.04) 100%),radial-gradient(circle at 96% 98%, rgba(61, 61, 61,0.04) 0%, rgba(61, 61, 61,0.04) 50%,rgba(201, 201, 201,0.04) 50%, rgba(201, 201, 201,0.04) 100%),radial-gradient(circle at 93% 97%, rgba(227, 227, 227,0.04) 0%, rgba(227, 227, 227,0.04) 50%,rgba(145, 145, 145,0.04) 50%, rgba(145, 145, 145,0.04) 100%),radial-gradient(circle at 79% 52%, rgba(245, 245, 245,0.04) 0%, rgba(245, 245, 245,0.04) 50%,rgba(86, 86, 86,0.04) 50%, rgba(86, 86, 86,0.04) 100%),linear-gradient(90deg, rgb(210, 9, 198),rgb(25, 38, 118));
        }

        .hero-content {
            max-width: 800px;
        }

        .hero h1 {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            color: #ffffff;
        }

        .hero p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            color: #ffffff;
        }

        .cta-button {
            display: inline-block;
            padding: 1rem 2rem;
            background-color: #ffffff;
            color: #6c5ce7;
            text-decoration: none;
            border-radius: 30px;
            font-weight: 600;
            transition: transform 0.3s ease;
        }

        .cta-button:hover {
            transform: translateY(-3px);
        }

        .features {
            padding: 5rem 1rem;
            background-color: #ffffff;
        }

        .features-grid {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .feature-card {
            padding: 2rem;
            text-align: center;
            background-color: #f8f9fa;
            border-radius: 10px;
            transition: transform 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-5px);
        }

        .feature-icon {
            font-size: 2.5rem;
            color: #6c5ce7;
            margin-bottom: 1rem;
        }

        .feature-card h3 {
            margin-bottom: 1rem;
            color: #2c3e50;
        }

        .feature-card p {
            color: #666;
            line-height: 1.6;
        }

        footer {
            background-color: #2c3e50;
            color: #ffffff;
            padding: 2rem;
            text-align: center;
        }

        .social-links {
            margin: 1rem 0;
        }

        .social-links a {
            color: #ffffff;
            font-size: 1.5rem;
            margin: 0 0.5rem;
            transition: color 0.3s ease;
        }

        .social-links a:hover {
            color: #3498db;
        }

        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }

            .hero h1 {
                font-size: 2.5rem;
            }
        }

        .login-form {
            max-width: 400px;
            margin: 2rem auto;
            padding: 2rem;
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #2c3e50;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: inherit;
        }

        .error-message {
            color: #e74c3c;
            margin-bottom: 1rem;
        }

        .success-message {
            color: #2ecc71;
            margin-bottom: 1rem;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-menu span {
            color: #2c3e50;
        }

        .logout-btn {
            padding: 0.5rem 1rem;
            background-color: #e74c3c;
            color: #ffffff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }

        .logout-btn:hover {
            background-color: #c0392b;
        }

        /* Additional inline styles for dynamic content */
        .stats-section {
            padding: 4rem 1rem;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
        }

        .stats-grid {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            text-align: center;
        }

        .stat-item {
            padding: 2rem;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            backdrop-filter: blur(10px);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 1rem 2rem;
            border-radius: 5px;
            color: white;
            transform: translateX(120%);
            transition: transform 0.3s ease;
            z-index: 1000;
        }

        .notification.show {
            transform: translateX(0);
        }

        .notification.success {
            background-color: var(--secondary-color);
        }

        .notification.error {
            background-color: var(--accent-color);
        }

        .menu-toggle {
            display: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--text-color);
        }

        @media (max-width: 768px) {
            .menu-toggle {
                display: block;
            }

            .nav-links {
                position: fixed;
                top: 70px;
                left: -100%;
                width: 100%;
                height: calc(100vh - 70px);
                background: white;
                flex-direction: column;
                padding: 2rem;
                transition: left 0.3s ease;
            }

            .nav-links.active {
                left: 0;
            }
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .blog-posts {
            padding: 5rem 0;
            background-color: #f8f9fa;
        }

        .blog-posts h2 {
            text-align: center;
            margin-bottom: 3rem;
            color: #2c3e50;
            font-size: 2.5rem;
            position: relative;
        }

        .blog-posts h2::after {
            content: '';
            display: block;
            width: 50px;
            height: 3px;
            background: #6c5ce7;
            margin: 1rem auto;
            border-radius: 3px;
        }

        .posts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2.5rem;
            padding: 0 1rem;
        }

        .post-card {
            background: #ffffff;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            position: relative;
        }

        .post-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .post-image {
            height: 250px;
            overflow: hidden;
            position: relative;
        }

        .post-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .post-card:hover .post-image img {
            transform: scale(1.1);
        }

        .post-category {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background-color: #6c5ce7;
            color: #ffffff;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            z-index: 1;
        }

        .post-content {
            padding: 2rem;
        }

        .post-content h3 {
            margin-bottom: 1rem;
            color: #2c3e50;
            font-size: 1.5rem;
            line-height: 1.4;
        }

        .post-excerpt {
            color: #666;
            margin-bottom: 1.5rem;
            line-height: 1.6;
            font-size: 0.95rem;
        }

        .post-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #999;
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
            padding-top: 1rem;
            border-top: 1px solid #eee;
        }

        .post-author {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .post-author i {
            color: #6c5ce7;
        }

        .read-more {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #6c5ce7;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .read-more:hover {
            color: #5a4bd1;
            gap: 0.8rem;
        }

        .read-more i {
            transition: transform 0.3s ease;
        }

        .read-more:hover i {
            transform: translateX(3px);
        }

        @media (max-width: 768px) {
            .posts-grid {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .post-image {
                height: 200px;
            }
        }

        .categories {
            padding: 5rem 0;
            background-color: #ffffff;
        }

        .categories h2 {
            text-align: center;
            margin-bottom: 3rem;
            color: #2c3e50;
            font-size: 2.5rem;
        }

        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }

        .category-card {
            text-align: center;
            padding: 2rem;
            background-color: #f8f9fa;
            border-radius: 10px;
            transition: transform 0.3s ease;
        }

        .category-card:hover {
            transform: translateY(-5px);
        }

        .category-card i {
            font-size: 2.5rem;
            color: #6c5ce7;
            margin-bottom: 1rem;
        }

        .category-card h3 {
            margin-bottom: 0.5rem;
            color: #2c3e50;
        }

        .category-card p {
            color: #666;
            font-size: 0.9rem;
        }

        .contact {
            padding: 5rem 0;
            background-color: #f8f9fa;
        }

        .contact-form {
            max-width: 600px;
            margin: 0 auto;
            padding: 2.5rem;
            background: #ffffff;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .contact-form h2 {
            text-align: center;
            color: #2c3e50;
            font-size: 2.5rem;
            margin-bottom: 2rem;
            position: relative;
        }

        .contact-form h2::after {
            content: '';
            display: block;
            width: 50px;
            height: 3px;
            background: #6c5ce7;
            margin: 1rem auto;
            border-radius: 3px;
        }

        .contact .form-group {
            margin-bottom: 1.5rem;
        }

        .contact .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #2c3e50;
            font-weight: 500;
            font-size: 0.95rem;
        }

        .contact .form-group input,
        .contact .form-group textarea {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            font-family: inherit;
        }

        .contact .form-group input:focus,
        .contact .form-group textarea:focus {
            outline: none;
            border-color: #6c5ce7;
            box-shadow: 0 0 0 3px rgba(108, 92, 231, 0.1);
        }

        .contact .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }

        .contact .cta-button {
            width: 100%;
            padding: 1rem;
            background-color: #6c5ce7;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
        }

        .contact .cta-button:hover {
            background-color: #5a4bd1;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(108, 92, 231, 0.3);
        }

        .contact .cta-button:active {
            transform: translateY(0);
        }

        @media (max-width: 768px) {
            .contact {
                padding: 3rem 1rem;
            }

            .contact-form {
                padding: 2rem;
            }

            .contact-form h2 {
                font-size: 2rem;
            }
        }

        @media (max-width: 480px) {
            .contact-form {
                padding: 1.5rem;
            }

            .contact-form h2 {
                font-size: 1.8rem;
            }

            .contact .form-group input,
            .contact .form-group textarea {
                padding: 0.7rem;
            }
        }

        .create-post-btn {
            background-color: #6c5ce7;
            color: #ffffff;
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .create-post-btn:hover {
            background-color: #5a4bd1;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 3rem;
            padding: 0 1rem;
        }

        .section-header h2 {
            margin-bottom: 0;
            position: relative;
            display: inline-block;
        }

        .section-header h2::after {
            content: '';
            display: block;
            width: 50px;
            height: 3px;
            background: #6c5ce7;
            margin: 1rem auto;
            border-radius: 3px;
        }

        .create-post-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background-color: #6c5ce7;
            color: #ffffff;
            padding: 0.8rem 1.5rem;
            border-radius: 25px;
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .create-post-btn:hover {
            background-color: #5a4bd1;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(108, 92, 231, 0.3);
        }

        .create-post-btn i {
            font-size: 1.1rem;
        }
    </style>
</head>
<body>
    <div class="loading"></div>

    <nav class="navbar">
        <div class="nav-content">
            <a href="#" class="logo">Guraba Blog Post</a>
            <div class="menu-toggle">
                <i class="fas fa-bars"></i>
            </div>
            <div class="nav-links">
                <a href="#home">Home</a>
                <a href="#blog-posts">Blog Posts</a>
                <a href="#categories">Categories</a>
                <a href="#about">About</a>
                <a href="#contact">Contact</a>
                <?php if (is_logged_in()): ?>
                    <div class="user-menu">
                        <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        <a href="create_post.php" class="create-post-btn">Create New Blog</a>
                        <a href="logout.php" class="logout-btn">Logout</a>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="login-btn">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <section class="hero" id="home">
        <div class="hero-content">
            <h1>Welcome to Guraba Blog Post</h1>
            <p>Discover amazing stories, insights, and perspectives from our community of writers</p>
            <a href="#blog-posts" class="cta-button">Read Posts</a>
        </div>
    </section>

    <section class="blog-posts" id="blog-posts">
        <div class="container">
            <div class="section-header">
                <h2>Latest Blog Posts</h2>
                <?php if (is_logged_in()): ?>
                    <a href="create_post.php" class="create-post-btn">
                        <i class="fas fa-plus"></i> Create New Blog
                    </a>
                <?php endif; ?>
            </div>
            <div class="posts-grid">
                <?php
                try {
                    $stmt = $conn->query("
                        SELECT p.*, u.username, c.name as category_name 
                        FROM posts p 
                        LEFT JOIN users u ON p.author_id = u.id 
                        LEFT JOIN categories c ON p.category_id = c.id 
                        WHERE p.status = 'published' 
                        ORDER BY p.created_at DESC 
                        LIMIT 4
                    ");
                    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if (empty($posts)) {
                        echo '<p class="no-posts">No blog posts available yet.</p>';
                    } else {
                        foreach ($posts as $post):
                            $image_url = !empty($post['featured_image']) ? $post['featured_image'] : 'https://source.unsplash.com/random/800x600?' . urlencode($post['category_name']);
                ?>
                    <article class="post-card">
                        <div class="post-image">
                            <img src="<?php echo htmlspecialchars($image_url); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
                            <div class="post-category"><?php echo htmlspecialchars($post['category_name']); ?></div>
                        </div>
                        <div class="post-content">
                            <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                            <p class="post-excerpt"><?php echo htmlspecialchars($post['excerpt']); ?></p>
                            <div class="post-meta">
                                <span class="post-date"><?php echo date('F j, Y', strtotime($post['created_at'])); ?></span>
                                <span class="post-author">
                                    <i class="fas fa-user"></i> <?php echo htmlspecialchars($post['username']); ?>
                                </span>
                            </div>
                            <a href="post.php?slug=<?php echo htmlspecialchars($post['slug']); ?>" class="read-more">
                                Read More <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </article>
                <?php
                        endforeach;
                    }
                } catch(PDOException $e) {
                    echo '<p class="error-message">Error loading blog posts. Please try again later.</p>';
                    error_log("Database error: " . $e->getMessage());
                }
                ?>
            </div>
        </div>
    </section>

    <section class="categories" id="categories">
        <div class="container">
            <h2>Categories</h2>
            <div class="categories-grid">
                <div class="category-card">
                    <i class="fas fa-code"></i>
                    <h3>Technology</h3>
                    <p>Latest tech trends and innovations</p>
                </div>
                <div class="category-card">
                    <i class="fas fa-paint-brush"></i>
                    <h3>Design</h3>
                    <p>UI/UX and creative design</p>
                </div>
                <div class="category-card">
                    <i class="fas fa-book"></i>
                    <h3>Education</h3>
                    <p>Learning and development</p>
                </div>
                <div class="category-card">
                    <i class="fas fa-heart"></i>
                    <h3>Lifestyle</h3>
                    <p>Health, wellness, and personal growth</p>
                </div>
            </div>
        </div>
    </section>

    <section class="stats-section" id="stats">
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-number counter" data-target="<?php echo $user_stats['total_users']; ?>">0</div>
                <div class="stat-label">Total Users</div>
            </div>
            <div class="stat-item">
                <div class="stat-number counter" data-target="<?php echo $user_stats['active_users']; ?>">0</div>
                <div class="stat-label">Active Users</div>
            </div>
            <div class="stat-item">
                <div class="stat-number counter" data-target="<?php echo $user_stats['total_messages']; ?>">0</div>
                <div class="stat-label">Messages Sent</div>
            </div>
        </div>
    </section>

    <section id="contact" class="contact">
        <div class="contact-form">
            <h2>Contact Us</h2>
            <form method="POST" action="" id="contactForm">
                <div class="form-group">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="message">Message:</label>
                    <textarea id="message" name="message" rows="5" required></textarea>
                </div>
                <button type="submit" name="contact" class="cta-button">Send Message</button>
            </form>
        </div>
    </section>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Guraba Blog Post. All rights reserved.</p>
        <div class="social-links">
            <a href="#"><i class="fab fa-facebook"></i></a>
            <a href="#"><i class="fab fa-twitter"></i></a>
            <a href="#"><i class="fab fa-instagram"></i></a>
            <a href="#"><i class="fab fa-linkedin"></i></a>
        </div>
    </footer>

    <script src="script.js"></script>
    <script>
        // AJAX form submission
        document.getElementById('contactForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('index.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                showNotification(data.message, data.success ? 'success' : 'error');
                if (data.success) {
                    this.reset();
                }
            })
            .catch(error => {
                showNotification('An error occurred. Please try again.', 'error');
            });
        });
    </script>
</body>
</html>
