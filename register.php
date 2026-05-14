<?php
require_once 'config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? '';

    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all fields';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } else {
        // Check if username or email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        
        if ($stmt->rowCount() > 0) {
            $error = 'Username or email already exists';
        } else {
            // Create new user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
            
            try {
                $stmt->execute([$username, $email, $hashed_password, $role]);
                $success = 'Registration successful! You can now login.';
                
                // Add success message for admin registration
                if ($role === 'admin') {
                    $success .= ' You have registered as an admin!';
                }
            } catch (PDOException $e) {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Travel Tour Booking</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .register-bg { 
            min-height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            background: linear-gradient(135deg, #1976d2 60%, #43a047 100%); 
        }
        .register-container { 
            background: #fff; 
            border-radius: 16px; 
            box-shadow: 0 4px 32px rgba(25, 118, 210, 0.10); 
            padding: 2.5rem 2rem; 
            max-width: 350px; 
            width: 100%; 
        }
        .register-container h2 { 
            color: #1976d2; 
            margin-bottom: 1rem; 
            text-align: center;
        }
        .register-container form { 
            display: flex; 
            flex-direction: column; 
            gap: 1rem; 
        }
        .register-container input { 
            padding: 0.8rem 1rem; 
            border: 1px solid #ddd; 
            border-radius: 8px; 
            font-size: 1rem; 
            outline: none; 
            transition: border 0.2s; 
        }
        .register-container input:focus { 
            border: 1.5px solid #1976d2; 
        }
        .register-container button { 
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
        .register-container button:hover { 
            background: #43a047; 
        }
        .register-error { 
            background: #ffebee; 
            color: #c62828; 
            padding: 0.7rem 1rem; 
            border-radius: 6px; 
            margin-bottom: 1rem; 
            font-size: 1rem; 
        }
        .register-container .login-link { 
            margin-top: 1rem; 
            text-align: center; 
        }
        .register-container .login-link a { 
            color: #1976d2; 
            text-decoration: none; 
            font-weight: 500; 
        }
        .register-container .login-link a:hover { 
            text-decoration: underline; 
        }
    </style>
</head>
<body>
    <div class="register-bg">
        <div class="register-container">
            <h2>Register</h2>
            <?php if ($error): ?>
                <div class="register-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            <form id="registerForm" method="POST" action="register.php">
                <input type="text" name="username" placeholder="Username" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                <div class="form-group">
                    <label for="role">Role</label>
                    <select id="role" name="role" required>
                        <option value="">Select role</option>
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <button type="submit">Register</button>
            </form>
            <div class="login-link">
                Already have an account? <a href="login.php">Login</a>
            </div>
        </div>
    </div>
    <script src="assets/js/main.js"></script>
</body>
</html> 