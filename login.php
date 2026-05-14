<?php
require_once 'config.php'; // Include the database configuration
$error = ''; // Initialize the error variable

if (isset($_SESSION['user_id'])) {
    // If there's a redirect URL, go there, otherwise go to index
    if (isset($_SESSION['redirect_url'])) {
        $redirect = $_SESSION['redirect_url'];
        unset($_SESSION['redirect_url']);
        header('Location: ' . $redirect);
    } else {
        header('Location: index.php');
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        // Check if the user exists
        $stmt = $pdo->prepare("SELECT id, username, password, role, email FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            // If there's a redirect URL, go there
            if (isset($_SESSION['redirect_url'])) {
                $redirect = $_SESSION['redirect_url'];
                unset($_SESSION['redirect_url']);
                header('Location: ' . $redirect);
            } else {
                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header('Location: admin/reports.php');
                } else {
                    header('Location: index.php');
                }
            }
            exit;
        } else {
            $error = 'Invalid username or password';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Travel Tour Booking</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .login-bg { 
            min-height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            background: linear-gradient(135deg, #1976d2 60%, #43a047 100%); 
        }
        .login-container { 
            background: #fff; 
            border-radius: 16px; 
            box-shadow: 0 4px 32px rgba(25, 118, 210, 0.10); 
            padding: 2.5rem 2rem; 
            max-width: 350px; 
            width: 100%; 
        }
        .login-container h2 { 
            color: #1976d2; 
            margin-bottom: 1rem; 
            text-align: center;
        }
        .login-container form { 
            display: flex; 
            flex-direction: column; 
            gap: 1rem; 
        }
        .login-container input { 
            padding: 0.8rem 1rem; 
            border: 1px solid #ddd; 
            border-radius: 8px; 
            font-size: 1rem; 
            outline: none; 
            transition: border 0.2s; 
        }
        .login-container input:focus { 
            border: 1.5px solid #1976d2; 
        }
        .login-container button { 
            background: #1976d2; 
            color: #fff; 
            border: none; 
            border-radius: 20px; 
            padding: 0.8rem 0; 
            font-size: 1.1rem; 
            font-weight: 600; 
            cursor: pointer; 
            margin-top: 0.5rem; 
            transition: background 0.2s; 
        }
        .login-container button:hover { 
            background: #43a047; 
        }
        .login-error { 
            background: #ffebee; 
            color: #c62828; 
            padding: 0.7rem 1rem; 
            border-radius: 6px; 
            margin-bottom: 1rem; 
            font-size: 1rem; 
        }
        .login-container .register-link { 
            margin-top: 1rem; 
            text-align: center; 
        }
        .login-container .register-link a { 
            color: #1976d2; 
            text-decoration: none; 
            font-weight: 500; 
        }
        .login-container .register-link a:hover { 
            text-decoration: underline; 
        }
    </style>
</head>
<body>
    <div class="login-bg">
        <div class="login-container">
            <h2>Welcome back! Sign in to continue your journey.</h2>
            <?php if (isset($_GET['timeout'])): ?>
                <div class="error-message">Your session has expired. Please log in again.</div>
            <?php endif; ?>
            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form id="loginForm" method="POST" action="login.php">
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Sign In</button>
            </form>
            <div class="register-link">
                Don't have an account? <a href="register.php">Register</a>
            </div>
        </div>
    </div>
    <script src="assets/js/main.js"></script>
</body>
</html> 