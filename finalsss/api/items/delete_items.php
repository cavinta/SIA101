<?php
require '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Only POST or DELETE allowed"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$id = intval($data['id'] ?? 0);

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Item ID is required"]);
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

// Delete item
$stmt = $conn->prepare("DELETE FROM items WHERE id=?");
$stmt->bind_param("i", $id);
if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Item deleted successfully"]);
} else {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Failed to delete item"]);
}

$stmt->close();
$conn->close();
?>
