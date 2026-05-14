<?php
namespace Repository;

use PDO;

class TourRepository {
    private $pdo;
    
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }
    
    public function findAll($filters = []) {
        $query = "SELECT * FROM tours WHERE 1=1";
        $params = [];
        
        if (!empty($filters['search'])) {
            $query .= " AND (name LIKE ? OR location LIKE ?)";
            $params[] = "%{$filters['search']}%";
            $params[] = "%{$filters['search']}%";
        }
        
        if (!empty($filters['category'])) {
            $query .= " AND category = ?";
            $params[] = $filters['category'];
        }
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM tours WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function create(array $data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO tours (name, description, price, duration, max_capacity, location, category, image_path)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['name'],
            $data['description'],
            $data['price'],
            $data['duration'],
            $data['max_capacity'],
            $data['location'],
            $data['category'],
            $data['image_path'] ?? null
        ]);
    }
    
    public function update($id, array $data) {
        $stmt = $this->pdo->prepare("
            UPDATE tours 
            SET name = ?, description = ?, price = ?, duration = ?, 
                max_capacity = ?, location = ?, category = ?, image_path = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['name'],
            $data['description'],
            $data['price'],
            $data['duration'],
            $data['max_capacity'],
            $data['location'],
            $data['category'],
            $data['image_path'] ?? null,
            $id
        ]);
    }
    
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM tours WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function getStatistics() {
        $stmt = $this->pdo->query("
            SELECT 
                COUNT(*) as total_tours,
                AVG(price) as avg_price,
                SUM(CASE WHEN availability = 1 THEN 1 ELSE 0 END) as available_tours
            FROM tours
        ");
        return $stmt->fetch();
    }
}