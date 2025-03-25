<?php
require_once 'config.php';
require_once 'functions.php';

// Get post slug from URL
$slug = isset($_GET['slug']) ? sanitize_input($_GET['slug']) : '';

if (empty($slug)) {
    header("Location: index.php");
    exit();
}

try {
    // Get post details with author and category information
    $stmt = $conn->prepare("
        SELECT p.*, u.username, c.name as category_name 
        FROM posts p 
        LEFT JOIN users u ON p.author_id = u.id 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.slug = ? AND p.status = 'published'
    ");
    $stmt->execute([$slug]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$post) {
        header("Location: index.php");
        exit();
    }

    // Increment view count
    $stmt = $conn->prepare("UPDATE posts SET views = views + 1 WHERE id = ?");
    $stmt->execute([$post['id']]);

    // Get featured image or use category-based image
    $image_url = !empty($post['featured_image']) ? $post['featured_image'] : 'https://source.unsplash.com/random/1200x600?' . urlencode($post['category_name']);
} catch(PDOException $e) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['title']); ?> - Guraba Blog Post</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
            line-height: 1.6;
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

        .post-container {
            max-width: 900px;
            margin: 100px auto 50px;
            padding: 0 20px;
        }

        .post-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .post-title {
            font-size: 2.5rem;
            color: #2c3e50;
            margin-bottom: 1rem;
            line-height: 1.3;
        }

        .post-meta {
            display: flex;
            justify-content: center;
            gap: 2rem;
            color: #666;
            font-size: 0.9rem;
        }

        .post-meta span {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .post-meta i {
            color: #6c5ce7;
        }

        .post-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .post-category {
            display: inline-block;
            background-color: #6c5ce7;
            color: #ffffff;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            margin-bottom: 1rem;
        }

        .post-content {
            background: #ffffff;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .post-content p {
            margin-bottom: 1.5rem;
            font-size: 1.1rem;
            color: #444;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #6c5ce7;
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 2rem;
            transition: gap 0.3s ease;
        }

        .back-button:hover {
            gap: 0.8rem;
        }

        @media (max-width: 768px) {
            .post-title {
                font-size: 2rem;
            }

            .post-image {
                height: 300px;
            }

            .post-meta {
                flex-direction: column;
                gap: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-content">
            <a href="index.php" class="logo">Guraba Blog Post</a>
            <div class="nav-links">
                <a href="index.php">Home</a>
                <a href="index.php#blog-posts">Blog Posts</a>
                <a href="index.php#categories">Categories</a>
                <a href="index.php#about">About</a>
                <a href="index.php#contact">Contact</a>
                <?php if (is_logged_in()): ?>
                    <div class="user-menu">
                        <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        <a href="logout.php" class="logout-btn">Logout</a>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="login-btn">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="post-container">
        <a href="index.php" class="back-button">
            <i class="fas fa-arrow-left"></i> Back to Home
        </a>

        <article>
            <div class="post-header">
                <div class="post-category"><?php echo htmlspecialchars($post['category_name']); ?></div>
                <h1 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h1>
                <div class="post-meta">
                    <span>
                        <i class="fas fa-user"></i>
                        <?php echo htmlspecialchars($post['username']); ?>
                    </span>
                    <span>
                        <i class="fas fa-calendar"></i>
                        <?php echo date('F j, Y', strtotime($post['created_at'])); ?>
                    </span>
                    <span>
                        <i class="fas fa-eye"></i>
                        <?php echo number_format($post['views']); ?> views
                    </span>
                </div>
            </div>

            <img src="<?php echo htmlspecialchars($image_url); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" class="post-image">

            <div class="post-content">
                <?php echo nl2br(htmlspecialchars($post['content'])); ?>
            </div>
        </article>
    </div>
</body>
</html> 