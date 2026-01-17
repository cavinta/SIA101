<?php
require '../../config/database.php';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Item ID is required"]);
    exit;
}

$stmt = $conn->prepare("
    SELECT items.id, items.name, items.price, categories.id AS category_id, categories.name AS category_name
    FROM items 
    JOIN categories ON items.category_id = categories.id
    WHERE items.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(["status" => "error", "message" => "Item not found"]);
} else {
    $item = $result->fetch_assoc();
    echo json_encode(["status" => "success", "data" => $item]);
}

$stmt->close();
$conn->close();
?>