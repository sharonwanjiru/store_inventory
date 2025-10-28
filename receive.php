<?php
require_once 'database.php';

// Ensure response is always JSON
header('Content-Type: application/json');

// Create DB connection
$db = new Database();
$conn = $db->conn;

// ---------- 1️⃣ Handle GET requests for dropdowns ----------
if (isset($_GET['action'])) {
    if ($_GET['action'] === 'getItems') {
        $result = $conn->query("SELECT id, name FROM items ORDER BY name ASC");
        $items = [];
        while ($row = $result->fetch_assoc()) $items[] = $row;
        echo json_encode($items);
        exit;
    }

    if ($_GET['action'] === 'getStaff') {
        $result = $conn->query("SELECT id, name FROM staff ORDER BY name ASC");
        $staff = [];
        while ($row = $result->fetch_assoc()) $staff[] = $row;
        echo json_encode($staff);
        exit;
    }
}

// ---------- 2️⃣ Handle POST request for receiving ----------
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid input data.']);
    exit;
}

$item_id = intval($data['item_id']);
$quantity = intval($data['quantity']);
$received_by = intval($data['received_by']);
$date_received = $data['date_received'] ?? date('Y-m-d');

// Validation
if ($item_id <= 0 || $quantity <= 0 || $received_by <= 0) {
    echo json_encode(['success' => false, 'message' => 'Please fill all fields correctly.']);
    exit;
}

// Start a transaction so both queries happen together
$conn->begin_transaction();

try {
    // Insert into receive_log
    $stmt = $conn->prepare("INSERT INTO receive_log (item_id, quantity, received_by, date_received)
                            VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiis", $item_id, $quantity, $received_by, $date_received);
    $stmt->execute();

    // Update stock
    $update = $conn->prepare("UPDATE items SET quantity = quantity + ? WHERE id = ?");
    $update->bind_param("ii", $quantity, $item_id);
    $update->execute();

    // Get the new quantity
    $res = $conn->query("SELECT quantity FROM items WHERE id = $item_id");
    $row = $res->fetch_assoc();
    $newQuantity = $row ? $row['quantity'] : 0;

    // Commit transaction
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => "Item received successfully. New stock quantity: $newQuantity"
    ]);
} catch (Exception $e) {
    // Roll back if anything fails
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

// Close everything
$conn->close();
?>
