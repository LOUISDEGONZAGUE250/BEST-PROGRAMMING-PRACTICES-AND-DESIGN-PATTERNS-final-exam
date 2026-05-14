<?php
// Database setup script
$host = getenv('DB_HOST') ?: 'localhost';
$port = getenv('DB_PORT') ?: 3306;
$user = 'root';
$pass = getenv('MYSQL_ROOT_PASSWORD') ?: '';
$dbname = getenv('DB_DATABASE') ?: 'ttbooking';

try {
    // Create connection without database (use TCP host/port)
    $pdo = new PDO("mysql:host=$host;port=$port", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Database created successfully\n";
    
    // Select the database
    $pdo->exec("USE `$dbname`");
    
    // Create tables
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        role ENUM('user', 'admin') DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS tours (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        price DECIMAL(10,2) NOT NULL,
        duration INT NOT NULL,
        max_participants INT NOT NULL,
        location VARCHAR(100),
        category VARCHAR(50),
        image_path VARCHAR(255),
        availability TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS bookings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        tour_id INT NOT NULL,
        booking_date DATE NOT NULL,
        num_people INT NOT NULL,
        total_price DECIMAL(10,2) NOT NULL,
        status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (tour_id) REFERENCES tours(id)
    )");
    
    echo "Tables created successfully\n";

    // Ensure expected columns exist (safe to run multiple times)
    $addColumnIfMissing = function($table, $columnName, $columnSql) use ($pdo, $dbname) {
        $stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM information_schema.columns WHERE table_schema = ? AND table_name = ? AND column_name = ?");
        $stmt->execute([$dbname, $table, $columnName]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && $row['cnt'] == 0) {
            $pdo->exec("ALTER TABLE $table ADD COLUMN $columnSql");
        }
    };

    $addColumnIfMissing('tours', 'location', 'location VARCHAR(100)');
    $addColumnIfMissing('tours', 'category', 'category VARCHAR(50)');
    $addColumnIfMissing('tours', 'image_path', 'image_path VARCHAR(255)');
    $addColumnIfMissing('tours', 'availability', 'availability TINYINT(1) DEFAULT 1');
    // Add new column expected by the admin UI (backfill from legacy column if present)
    $addColumnIfMissing('tours', 'max_participants', 'max_participants INT NOT NULL DEFAULT 0');

    // If the legacy 'max_capacity' column exists, copy its values to 'max_participants' for existing rows
    $stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM information_schema.columns WHERE table_schema = ? AND table_name = ? AND column_name = ?");
    $stmt->execute([$dbname, 'tours', 'max_capacity']);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row && $row['cnt'] > 0) {
        $pdo->exec("UPDATE tours SET max_participants = max_capacity WHERE max_participants = 0");
        // Ensure legacy column has a default so inserts that don't specify it don't fail
        $pdo->exec("ALTER TABLE tours MODIFY COLUMN max_capacity INT NOT NULL DEFAULT 0");
    }

    // Add payment columns used by the payment flow
    $addColumnIfMissing('bookings', 'payment_date', 'payment_date DATETIME NULL');
    $addColumnIfMissing('bookings', 'receipt_number', 'receipt_number VARCHAR(100) NULL');

    // Ensure the bookings.status enum includes 'paid' (idempotent)
    $stmt = $pdo->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'bookings' AND COLUMN_NAME = 'status'");
    $stmt->execute([$dbname]);
    $col = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($col && strpos($col['COLUMN_TYPE'], "'paid'") === false) {
        // Alter enum to include 'paid' while preserving other values
        $pdo->exec("ALTER TABLE bookings MODIFY COLUMN status ENUM('pending','confirmed','cancelled','paid') DEFAULT 'pending'");
    }
    
} catch(PDOException $e) {
    die("Error: " . $e->getMessage() . "\n");
}
?> 