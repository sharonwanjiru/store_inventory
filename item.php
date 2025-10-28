<?php
header('Content-Type: application/json');
require_once 'database.php';

class Item {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->conn;
    }

    // Add new item
    public function add($data) {
        $stmt = $this->conn->prepare("INSERT INTO items (name, quantity, category_id, store_number, date_added)
                                      VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("siiss",
            $data['name'],
            $data['quantity'],
            $data['category_id'],
            $data['store_number'],
            $data['date_added']
        );
        $stmt->execute();

        return ['success' => $stmt->affected_rows > 0];
    }

    // Fetch all categories
    public function fetchCategories() {
        $result = $this->conn->query("SELECT id, name FROM categories");
        $categories = [];
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
        return $categories;
    }
}

// Initialize class
$item = new Item();

// Determine request type
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Fetch categories for dropdown
    echo json_encode($item->fetchCategories());
}
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Read JSON data
    $data = json_decode(file_get_contents('php://input'), true);
    echo json_encode($item->add($data));
}
?>
