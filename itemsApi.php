<?php
require_once '../item.php';
header('Content-Type: application/json');

$item = new Item();
$action = $_GET['action'] ?? '';

if ($action === 'fetch') {
    echo json_encode($item->fetchAll());
} elseif ($action === 'add') {
    $data = json_decode(file_get_contents('php://input'), true);
    echo json_encode($item->add($data));
} elseif ($action === 'categories') {
    echo json_encode($item->fetchCategories());
}
?>