<?php
require_once 'config.php';

// Function to sanitize user input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Function to get user data
function get_user_data($user_id) {
    global $conn;
    try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        return false;
    }
}

// Function to register a new user
function register_user($username, $email, $password) {
    global $conn;
    try {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        return $stmt->execute([$username, $email, $hashed_password]);
    } catch(PDOException $e) {
        return false;
    }
}

// Function to login user
function login_user($username, $password) {
    global $conn;
    
    // Check if input is email or username
    $is_email = filter_var($username, FILTER_VALIDATE_EMAIL);
    
    try {
        if ($is_email) {
            $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE email = ?");
        } else {
            $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
        }
        
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            return true;
        }
        
        return false;
    } catch(PDOException $e) {
        return false;
    }
}

// Function to logout user
function logout_user() {
    session_destroy();
    header("Location: index.php");
    exit();
}

// Function to get featured content
function get_featured_content() {
    global $conn;
    try {
        $stmt = $conn->query("SELECT * FROM content WHERE featured = 1 ORDER BY created_at DESC LIMIT 3");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        return [];
    }
}

// Function to add a new contact message
function add_contact_message($name, $email, $message) {
    global $conn;
    try {
        $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)");
        return $stmt->execute([$name, $email, $message]);
    } catch(PDOException $e) {
        return false;
    }
}

// Function to get user statistics
function get_user_statistics() {
    global $conn;
    try {
        $stats = [
            'total_users' => 0,
            'active_users' => 0,
            'total_messages' => 0
        ];

        // Get total users
        $stmt = $conn->query("SELECT COUNT(*) as total FROM users");
        $stats['total_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Get active users (users who logged in in the last 24 hours)
        $stmt = $conn->query("SELECT COUNT(DISTINCT user_id) as active FROM user_activity WHERE activity_time > DATE_SUB(NOW(), INTERVAL 24 HOUR)");
        $stats['active_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['active'];

        // Get total messages
        $stmt = $conn->query("SELECT COUNT(*) as total FROM contact_messages");
        $stats['total_messages'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        return $stats;
    } catch(PDOException $e) {
        return [
            'total_users' => 0,
            'active_users' => 0,
            'total_messages' => 0
        ];
    }
}

// Function to log user activity
function log_user_activity($user_id) {
    global $conn;
    try {
        $stmt = $conn->prepare("INSERT INTO user_activity (user_id, activity_time) VALUES (?, NOW())");
        return $stmt->execute([$user_id]);
    } catch(PDOException $e) {
        return false;
    }
}

// Function to get cached data
function get_cached_data($key) {
    $cache_file = "cache/{$key}.json";
    if (file_exists($cache_file)) {
        $data = json_decode(file_get_contents($cache_file), true);
        if ($data['expires'] > time()) {
            return $data['content'];
        }
    }
    return false;
}

// Function to cache data
function cache_data($key, $content, $duration = 3600) {
    $cache_dir = "cache";
    if (!file_exists($cache_dir)) {
        mkdir($cache_dir, 0777, true);
    }

    $cache_file = "{$cache_dir}/{$key}.json";
    $data = [
        'content' => $content,
        'expires' => time() + $duration
    ];

    return file_put_contents($cache_file, json_encode($data));
}

// Function to clear cache
function clear_cache($key = null) {
    $cache_dir = "cache";
    if ($key) {
        $cache_file = "{$cache_dir}/{$key}.json";
        if (file_exists($cache_file)) {
            return unlink($cache_file);
        }
    } else {
        array_map('unlink', glob("{$cache_dir}/*.json"));
        return true;
    }
    return false;
}

// Function to validate email
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Function to generate random token
function generate_token($length = 32) {
    return bin2hex(random_bytes($length));
}

// Function to check if request is AJAX
function is_ajax_request() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

// Function to send JSON response
function send_json_response($data, $status_code = 200) {
    http_response_code($status_code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

// Function to get client IP
function get_client_ip() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if(isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}
?>