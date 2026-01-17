<?php
require '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Only POST method allowed"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$category_id = intval($data['category_id'] ?? 0);
$name = trim($data['name'] ?? '');
$price = floatval($data['price'] ?? 0);

if ($category_id <= 0 || $name === '' || $price <= 0) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "category_id, name, and price are required"]);
    exit;
}

// Check if category exists
$check = $conn->prepare("SELECT id FROM categories WHERE id = ?");
$check->bind_param("i", $category_id);
$check->execute();
$check->store_result();
if ($check->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Category not found"]);
    exit;
}
$check->close();

// Insert item
$stmt = $conn->prepare("INSERT INTO items (category_id, name, price) VALUES (?, ?, ?)");
$stmt->bind_param("isd", $category_id, $name, $price);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Item added successfully", "item_id" => $stmt->insert_id]);
} else {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Failed to add item"]);
}

$stmt->close();
$conn->close();
?>
