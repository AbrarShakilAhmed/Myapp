<?php
require_once 'config.php';
require_once 'functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize_input($_POST['username']);
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (strlen($username) < 8) {
        $error = "Username must be at least 8 characters long";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } else {
        if (register_user($username, $email, $password)) {
            $success = "Registration successful! Please login.";
        } else {
            $error = "Registration failed. Username or email might already exist.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Guraba Blog Post</title>
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
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .register-container {
            background: #ffffff;
            padding: 2.5rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }

        .register-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .register-header h1 {
            color: #2c3e50;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .register-header p {
            color: #666;
            font-size: 0.9rem;
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

        .form-group input {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #6c5ce7;
            box-shadow: 0 0 0 3px rgba(108, 92, 231, 0.1);
        }

        .error-message {
            color: #e74c3c;
            font-size: 0.9rem;
            margin-bottom: 1rem;
            text-align: center;
        }

        .success-message {
            color: #2ecc71;
            font-size: 0.9rem;
            margin-bottom: 1rem;
            text-align: center;
        }

        .register-btn {
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
        }

        .register-btn:hover {
            background-color: #5a4bd1;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(108, 92, 231, 0.3);
        }

        .register-btn:active {
            transform: translateY(0);
        }

        .auth-links {
            margin-top: 1.5rem;
            text-align: center;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .login-link, .back-to-home {
            color: #6c5ce7;
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }

        .login-link:hover, .back-to-home:hover {
            color: #5a4bd1;
        }

        @media (max-width: 480px) {
            .register-container {
                padding: 2rem;
            }

            .register-header h1 {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <h1>Create Account</h1>
            <p>Join our community today</p>
        </div>

        <?php if ($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required
                       minlength="8"
                       title="Username must be at least 8 characters long"
                       placeholder="Enter username">
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required
                       placeholder="Enter your email">
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required
                       minlength="8"
                       placeholder="Enter password">
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required
                       minlength="8"
                       placeholder="Confirm password">
            </div>

            <button type="submit" class="register-btn">Register</button>
        </form>

        <div class="auth-links">
            <a href="login.php" class="login-link">Already have an account? Login</a>
            <a href="index.php" class="back-to-home">
                <i class="fas fa-arrow-left"></i> Back to Home
            </a>
        </div>
    </div>

    <script>
        document.getElementById('username').addEventListener('input', function(e) {
            if (this.value.length < 8) {
                this.setCustomValidity('Username must be at least 8 characters long');
            } else {
                this.setCustomValidity('');
            }
        });

        document.getElementById('password').addEventListener('input', function(e) {
            if (this.value.length < 8) {
                this.setCustomValidity('Password must be at least 8 characters long');
            } else {
                this.setCustomValidity('');
            }
        });

        document.getElementById('confirm_password').addEventListener('input', function(e) {
            const password = document.getElementById('password').value;
            if (this.value !== password) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>