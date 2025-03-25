<?php
require_once 'config.php';
require_once 'functions.php';

// Check if user is logged in
if (!is_logged_in()) {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize_input($_POST['title']);
    $content = sanitize_input($_POST['content']);
    $excerpt = sanitize_input($_POST['excerpt']);
    $category_id = (int)$_POST['category_id'];
    $status = sanitize_input($_POST['status']);

    // Create URL-friendly slug from title
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));

    // Handle image upload
    $featured_image = '';
    if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['featured_image']['type'];
        
        if (in_array($file_type, $allowed_types)) {
            $upload_dir = 'uploads/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $file_extension = pathinfo($_FILES['featured_image']['name'], PATHINFO_EXTENSION);
            $file_name = uniqid() . '.' . $file_extension;
            $target_path = $upload_dir . $file_name;

            if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $target_path)) {
                $featured_image = $target_path;
            }
        }
    }

    try {
        $stmt = $conn->prepare("
            INSERT INTO posts (title, slug, content, excerpt, featured_image, author_id, category_id, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        if ($stmt->execute([
            $title,
            $slug,
            $content,
            $excerpt,
            $featured_image,
            $_SESSION['user_id'],
            $category_id,
            $status
        ])) {
            $success = "Post created successfully!";
        } else {
            $error = "Failed to create post. Please try again.";
        }
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Get categories for the dropdown
try {
    $stmt = $conn->query("SELECT id, name FROM categories ORDER BY name");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $categories = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Post - Guraba Blog Post</title>
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
            min-height: 100vh;
            padding: 2rem;
        }

        .navbar {
            background-color: #ffffff;
            padding: 1rem 2rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            position: fixed;
            width: 100%;
            top: 0;
            left: 0;
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

        .create-post-container {
            max-width: 800px;
            margin: 100px auto 50px;
            background: #ffffff;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .create-post-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .create-post-header h1 {
            color: #2c3e50;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #2c3e50;
            font-weight: 500;
        }

        .form-group input[type="text"],
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-group input[type="text"]:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #6c5ce7;
        }

        .form-group textarea {
            min-height: 200px;
            resize: vertical;
        }

        .form-group input[type="file"] {
            padding: 0.5rem 0;
        }

        .submit-btn {
            background-color: #6c5ce7;
            color: #ffffff;
            padding: 1rem 2rem;
            border: none;
            border-radius: 25px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s ease;
            width: 100%;
        }

        .submit-btn:hover {
            background-color: #5a4bd1;
        }

        .error-message {
            background-color: #ff6b6b;
            color: #ffffff;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }

        .success-message {
            background-color: #51cf66;
            color: #ffffff;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
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
            .create-post-container {
                padding: 1.5rem;
                margin: 80px 1rem 2rem;
            }

            .create-post-header h1 {
                font-size: 1.8rem;
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

    <div class="create-post-container">
        <a href="index.php" class="back-button">
            <i class="fas fa-arrow-left"></i> Back to Home
        </a>

        <div class="create-post-header">
            <h1>Create New Post</h1>
            <p>Share your thoughts with the world</p>
        </div>

        <?php if ($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="POST" action="" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Post Title</label>
                <input type="text" id="title" name="title" required>
            </div>

            <div class="form-group">
                <label for="category_id">Category</label>
                <select id="category_id" name="category_id" required>
                    <option value="">Select a category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>">
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="excerpt">Post Excerpt</label>
                <textarea id="excerpt" name="excerpt" required></textarea>
            </div>

            <div class="form-group">
                <label for="featured_image">Featured Image</label>
                <input type="file" id="featured_image" name="featured_image" accept="image/*">
            </div>

            <div class="form-group">
                <label for="content">Post Content</label>
                <textarea id="content" name="content" required></textarea>
            </div>

            <div class="form-group">
                <label for="status">Post Status</label>
                <select id="status" name="status" required>
                    <option value="draft">Draft</option>
                    <option value="published">Published</option>
                    <option value="archived">Archived</option>
                </select>
            </div>

            <button type="submit" class="submit-btn">Create Post</button>
        </form>
    </div>
</body>
</html> 