<?php
require '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Only POST or PUT allowed"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$id = intval($data['id'] ?? 0);
$name = trim($data['name'] ?? '');
$price = floatval($data['price'] ?? 0);

if ($id <= 0 || $name === '' || $price < 0) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "ID, name, and price are required"]);
    exit;
}

// Check if add-on exists
$check = $conn->prepare("SELECT id FROM addons WHERE id=?");
$check->bind_param("i", $id);
$check->execute();
$check->store_result();
if ($check->num_rows === 0) {
    http_response_code(404);
    echo json_encode(["status" => "error", "message" => "Add-on not found"]);
    exit;
}
$check->close();

// Update add-on
$stmt = $conn->prepare("UPDATE addons SET name=?, price=? WHERE id=?");
$stmt->bind_param("sdi", $name, $price, $id);
if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Add-on updated successfully"]);
} else {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Failed to update add-on"]);
}

$stmt->close();
$conn->close();
?>
