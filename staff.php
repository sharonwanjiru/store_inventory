<?php
header('Content-Type: application/json');
require_once 'database.php';

// Connect to database
$db = new Database();
$conn = $db->conn;

// Read JSON data from frontend
$data = json_decode(file_get_contents('php://input'), true);

// Validate input
if (isset($data['name'], $data['role']) && !empty(trim($data['name'])) && !empty(trim($data['role']))) {
    $name = trim($data['name']);
    $role = trim($data['role']);

    // Prepare SQL insert
    $stmt = $conn->prepare("INSERT INTO staff (name, role) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $role);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Staff member added successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add staff.']);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Name and role are required.']);
}

$conn->close();
?>
