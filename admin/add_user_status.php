<?php
require_once '../config.php';

try {
    // Add status column to users table
    $pdo->exec("ALTER TABLE users ADD COLUMN status ENUM('active', 'inactive') NOT NULL DEFAULT 'active'");
    echo "Status column added successfully to users table.";
} catch (PDOException $e) {
    echo "Error adding status column: " . $e->getMessage();
}
?> 