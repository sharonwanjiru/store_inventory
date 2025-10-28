<?php
header('Content-Type: application/json');
require_once 'database.php';

$db = new Database();
$conn = $db->conn;

$action = $_GET['action'] ?? '';

if ($action === 'fetch') {
    // Get all items
    $items = [];
    $result = $conn->query("SELECT id, name, quantity FROM items");
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }

    // Get all staff
    $staff = [];
    $result = $conn->query("SELECT id, name, role FROM staff");
    while ($row = $result->fetch_assoc()) {
        $staff[] = $row;
    }

    echo json_encode(['items' => $items, 'staff' => $staff]);
    exit;
}

if ($action === 'add') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (
        isset($data['item_id'], $data['quantity'], $data['requested_by'], $data['approved_by'], $data['date_dispatched'])
        && !empty($data['quantity'])
    ) {
        $item_id = (int) $data['item_id'];
        $quantity = (int) $data['quantity'];
        $requested_by = (int) $data['requested_by'];
        $approved_by = (int) $data['approved_by'];
        $date_dispatched = $data['date_dispatched'];

        // ✅ Step 1: Check if enough stock exists
        $check = $conn->prepare("SELECT quantity FROM items WHERE id = ?");
        $check->bind_param("i", $item_id);
        $check->execute();
        $result = $check->get_result();
        $item = $result->fetch_assoc();
        $check->close();

        if (!$item) {
            echo json_encode(['success' => false, 'message' => 'Item not found.']);
            exit;
        }

        if ($item['quantity'] < $quantity) {
            echo json_encode(['success' => false, 'message' => 'Not enough stock available.']);
            exit;
        }

        // ✅ Step 2: Record dispatch
        $stmt = $conn->prepare("INSERT INTO dispatch_log (item_id, quantity, requested_by, approved_by, date_dispatched)
                                VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iiiis", $item_id, $quantity, $requested_by, $approved_by, $date_dispatched);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            // ✅ Step 3: Deduct quantity from item table
            $update = $conn->prepare("UPDATE items SET quantity = quantity - ? WHERE id = ?");
            $update->bind_param("ii", $quantity, $item_id);
            $update->execute();
            $update->close();

            echo json_encode(['success' => true, 'message' => 'Dispatch recorded and quantity updated successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to record dispatch.']);
        }

        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Incomplete dispatch data.']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action.']);
?>

