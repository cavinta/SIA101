<?php
require '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Only POST or PUT allowed"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$id = intval($data['id'] ?? 0);
$category_id = intval($data['category_id'] ?? 0);
$name = trim($data['name'] ?? '');
$price = floatval($data['price'] ?? 0);

if ($id <= 0 || $category_id <= 0 || $name === '' || $price <= 0) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "id, category_id, name, and price are required"]);
    exit;
}

// Check if item exists
$check = $conn->prepare("SELECT id FROM items WHERE id = ?");
$check->bind_param("i", $id);
$check->execute();
$check->store_result();
if ($check->num_rows === 0) {
    http_response_code(404);
    echo json_encode(["status" => "error", "message" => "Item not found"]);
    exit;
}
$check->close();

// Check if category exists
$checkCat = $conn->prepare("SELECT id FROM categories WHERE id = ?");
$checkCat->bind_param("i", $category_id);
$checkCat->execute();
$checkCat->store_result();
if ($checkCat->num_rows === 0) {
    http_response_code(404);
    echo json_encode(["status" => "error", "message" => "Category not found"]);
    exit;
}
$checkCat->close();

// Update item
$stmt = $conn->prepare("UPDATE items SET name=?, price=?, category_id=? WHERE id=?");
$stmt->bind_param("sdii", $name, $price, $category_id, $id);
if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Item updated successfully"]);
} else {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Failed to update item"]);
}

$stmt->close();
$conn->close();
?>
