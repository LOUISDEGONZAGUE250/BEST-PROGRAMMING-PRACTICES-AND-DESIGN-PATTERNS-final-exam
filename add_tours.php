<?php
require_once 'config.php';

$tours = [
    [
        'name' => 'Bali Paradise Tour',
        'description' => 'Experience the beauty of Bali with its stunning beaches, lush rice terraces, and vibrant culture. Visit Ubud Monkey Forest, Tanah Lot Temple, and enjoy a traditional dance performance.',
        'price' => 899.99,
        'duration' => 5,
        'max_capacity' => 20
    ],
    [
        'name' => 'Tokyo Explorer',
        'description' => 'Discover the perfect blend of traditional and modern Japan in Tokyo. Visit the iconic Tokyo Tower, experience the bustling Shibuya crossing, and enjoy authentic sushi making.',
        'price' => 1299.99,
        'duration' => 4,
        'max_capacity' => 15
    ],
    [
        'name' => 'Paris City Break',
        'description' => 'Fall in love with the City of Light. Visit the Eiffel Tower, Louvre Museum, and enjoy a Seine River cruise. Experience French cuisine and charming cafés.',
        'price' => 799.99,
        'duration' => 3,
        'max_capacity' => 25
    ],
    [
        'name' => 'New York Adventure',
        'description' => 'Experience the energy of the Big Apple. Visit Times Square, Central Park, and the Statue of Liberty. Enjoy Broadway shows and world-class shopping.',
        'price' => 999.99,
        'duration' => 4,
        'max_capacity' => 20
    ],
    [
        'name' => 'Sydney Explorer',
        'description' => 'Discover the beauty of Sydney with its iconic Opera House and Harbour Bridge. Enjoy Bondi Beach, Blue Mountains, and a sunset cruise.',
        'price' => 1099.99,
        'duration' => 5,
        'max_capacity' => 18
    ],
    [
        'name' => 'Rome Historical Tour',
        'description' => 'Step back in time in the Eternal City. Visit the Colosseum, Vatican City, and Trevi Fountain. Enjoy authentic Italian cuisine and wine tasting.',
        'price' => 899.99,
        'duration' => 4,
        'max_capacity' => 22
    ]
];

try {
    $stmt = $pdo->prepare("INSERT INTO tours (name, description, price, duration, max_capacity) VALUES (?, ?, ?, ?, ?)");
    
    foreach ($tours as $tour) {
        $stmt->execute([
            $tour['name'],
            $tour['description'],
            $tour['price'],
            $tour['duration'],
            $tour['max_capacity']
        ]);
    }
    
    echo "Tours added successfully!\n";
} catch (PDOException $e) {
    echo "Error adding tours: " . $e->getMessage() . "\n";
}
?> 