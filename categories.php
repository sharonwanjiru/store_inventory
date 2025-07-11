// File: api/categories.php
<?php
require_once '../Database.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$db = new Database();
$conn = $db->conn;

if ($action === 'add') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!empty($data['name'])) {
        $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->bind_param("s", $data['name']);
        $stmt->execute();

        echo json_encode(['success' => $stmt->affected_rows > 0]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Category name required']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid action']);
}

$conn->close();
?>
