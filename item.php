<?php
require_once '../database.php';

class Item {
    private $conn;

    public function __construct() {
        //Call the database class  to create a database object
        $db = new Database();
        //Create a connection
        $this->conn = $db->conn;
    }

    // Add new item
    public function add($data) {
        $stmt = $this->conn->prepare("INSERT INTO items (name, quantity, category_id, store_number, date_added)
                                      VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("siiss", $data['name'], $data['quantity'], $data['category_id'], $data['store_number'], $data['date_added']);
        $stmt->execute();
        return ['success' => $stmt->affected_rows > 0];
    }

    // Get all items
    public function fetchAll() {
        $sql = "SELECT items.id, items.name, items.quantity, categories.name AS category, items.store_number, items.date_added
                FROM items
                LEFT JOIN categories ON items.category_id = categories.id";
        $result = $this->conn->query($sql);
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        return $items;
    }

    // Get all categories
    public function fetchCategories() {
        $result = $this->conn->query("SELECT id, name FROM categories");
        $categories = [];
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
        return $categories;
    }
}
?>
