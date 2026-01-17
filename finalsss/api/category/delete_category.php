<?php
require '../../config/database.php';

// Only allow POST or DELETE method
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode([
        "status" => "error",
        "message" => "Only POST or DELETE method allowed"
    ]);
    exit;
}

// Get JSON input
$data = json_decode(file_get_contents("php://input"), true);

$id = intval($data['id'] ?? 0);

if ($id <= 0) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Category ID is required"
    ]);
    exit;
}

// Check if category exists
$check = $conn->prepare("SELECT id FROM categories WHERE id = ?");
$check->bind_param("i", $id);
$check->execute();
$check->store_result();

if ($check->num_rows === 0) {
    http_response_code(404);
    echo json_encode([
        "status" => "error",
        "message" => "Category not found"
    ]);
    exit;
}
$check->close();

// Delete category
$stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => "Category deleted successfully"
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Failed to delete category"
    ]);
}

$stmt->close();
$conn->close();
?>
