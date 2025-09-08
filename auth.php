<?php
session_start();
require_once __DIR__ . '/config.php';

header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer-when-downgrade');

function db()
{
    static $mysqli;
    if (!$mysqli) {
        $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($mysqli->connect_error) {
            http_response_code(500);
            die('Database connection failed');
        }
    }
    return $mysqli;
}

function sanitize_email($email)
{
    return filter_var(trim($email), FILTER_VALIDATE_EMAIL);
}

$action = $_POST['action'] ?? '';

if ($action === 'signup') {
    $name = trim($_POST['name'] ?? '');
    $email = sanitize_email($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || strlen($password) < 6) {
        die('Invalid email or password too short (min 6).');
    }
    $mysqli = db();
    // Check if exists
    $stmt = $mysqli->prepare('SELECT id FROM users WHERE email=?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        die('Email already registered.');
    }
    $stmt->close();

    $hash = password_hash($password, PASSWORD_BCRYPT);
    $provider = 'local';
    $stmt = $mysqli->prepare('INSERT INTO users (name, email, password, provider) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('ssss', $name, $email, $hash, $provider);
    if ($stmt->execute()) {
        echo 'Signup success';
    } else {
        http_response_code(500);
        echo 'Signup failed';
    }
    exit;
}

if ($action === 'login') {
    $email = sanitize_email($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (!$email || !$password) die('Invalid credentials');

    $mysqli = db();
    $stmt = $mysqli->prepare('SELECT id, name, email, password FROM users WHERE email=? AND provider="local"');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = ['id' => $user['id'], 'name' => $user['name'], 'email' => $user['email']];
        echo 'Login success';
    } else {
        echo 'Invalid credentials';
    }
    exit;
}

if ($action === 'google') {
    $id_token = $_POST['id_token'] ?? '';
    if (!$id_token) die('Missing token');

    // Verify with Google tokeninfo endpoint
    $url = 'https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($id_token);
    $resp = @file_get_contents($url);
    if ($resp === false) {
        http_response_code(400);
        die('Token verification failed');
    }
    $payload = json_decode($resp, true);
    if (!is_array($payload) || empty($payload['aud']) || empty($payload['email'])) {
        http_response_code(400);
        die('Invalid token payload');
    }

    // ✅ FIX: Use the actual GOOGLE_CLIENT_ID constant from your config.php
    if ($payload['aud'] !== GOOGLE_CLIENT_ID) {
        http_response_code(400);
        die('Client ID mismatch');
    }

    $email = $payload['email'];
    $sub = $payload['sub'] ?? null;
    $name = $payload['name'] ?? '';
    $picture = $payload['picture'] ?? '';

    $mysqli = db();
    // Upsert user
    $stmt = $mysqli->prepare('SELECT id, name, email FROM users WHERE google_sub=? OR email=? LIMIT 1');
    $stmt->bind_param('ss', $sub, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($user = $result->fetch_assoc()) {
        $uid = $user['id'];
    } else {
        $provider = 'google';
        $stmt2 = $mysqli->prepare('INSERT INTO users (name, email, provider, google_sub, avatar_url) VALUES (?, ?, ?, ?, ?)');
        $stmt2->bind_param('sssss', $name, $email, $provider, $sub, $picture);

        if (!$stmt2->execute()) {
            http_response_code(500);
            die('Failed to create user');
        }
        $uid = $stmt2->insert_id;
        $stmt2->close();
    }
    $_SESSION['user'] = [
        'id' => $uid,
        'name' => $name,
        'email' => $email,
        'avatar' => $picture,   // ✅ Add this line
        'google' => true
    ];
    echo 'Google login success';
    exit;
}

if ($action === 'logout') {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }
    session_destroy();
    header('Location: index.html'); // redirect to login page
    exit;
}

if ($action === 'forgot_password') {
    $email = sanitize_email($_POST['email'] ?? '');
    if (!$email) {
        http_response_code(400);
        die('Invalid email');
    }

    $mysqli = db();
    $stmt = $mysqli->prepare('SELECT id, name FROM users WHERE email=?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();
    if (!$user) {
        die('No user found with this email.');
    }

    // Generate a reset token
    $token = bin2hex(random_bytes(16));
    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

    // Save token in DB (ensure reset_token and reset_expires columns exist)
    $stmt = $mysqli->prepare('UPDATE users SET reset_token=?, reset_expires=? WHERE id=?');
    $stmt->bind_param('ssi', $token, $expires, $user['id']);
    if (!$stmt->execute()) {
        http_response_code(500);
        die('Failed to generate reset link.');
    }

    // Send reset email
    $resetLink = "https://yourdomain.com/reset_password.php?token=$token";
    $subject = "SmartGov Nepal - Reset Your Password";
    $message = "Hello {$user['name']},\n\nClick the link below to reset your password (valid for 1 hour):\n\n$resetLink";
    $headers = "From: no-reply@smartgovnepal.com\r\n";

    if (mail($email, $subject, $message, $headers)) {
        echo 'success';
    } else {
        http_response_code(500);
        echo 'Failed to send email';
    }
    exit;
}

// fallback
http_response_code(400);
echo 'Bad request';
