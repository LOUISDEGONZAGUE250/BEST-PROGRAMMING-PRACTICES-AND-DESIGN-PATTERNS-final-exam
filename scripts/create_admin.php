<?php
// Creates an admin user if not exists using environment variables.
// Usage: php scripts/create_admin.php

require_once __DIR__ . '/../config.php';

$username = getenv('ADMIN_USERNAME') ?: 'admin';
$email = getenv('ADMIN_EMAIL') ?: 'admin@example.com';
$password = getenv('ADMIN_PASSWORD') ?: 'password';

try {
    // Check if admin exists
    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $row = $stmt->fetch();

    if ($row) {
        echo "Admin user '{$username}' already exists (id: {$row['id']}).\n";
        exit(0);
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ? )');
    $stmt->execute([$username, $email, $hash, 'admin']);

    echo "Admin user created: username='{$username}', password='{$password}'\n";
} catch (PDOException $e) {
    echo "Error creating admin: " . $e->getMessage() . "\n";
    exit(1);
}
