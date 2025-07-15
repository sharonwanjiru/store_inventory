
<?php
// Database connection class
require_once 'database.php';

// set response header
header('Content-Type: application/json');

// connect to database
$db = new Database();
$conn = $db->conn;

// read JSON input from fetch
$data = json_decode(file_get_contents('php://input'), true);

// check if category name is provided
if (isset($data['name']) && !empty(trim($data['name']))) {
    $name = trim($data['name']);

    // prepare and execute insert
    $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
    $stmt->bind_param("s", $name);
    $stmt->execute();

    // return response
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Category added successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add category']);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Category name is required']);
}

$conn->close();
?>
