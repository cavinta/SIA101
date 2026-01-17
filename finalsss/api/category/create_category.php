<?php
require '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        "status" => "error",
        "message" => "Only POST method allowed"
    ]);
    exit;
}

// Read JSON input
$data = json_decode(file_get_contents("php://input"), true);

$name = trim($data['name'] ?? '');

if ($name === '') {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Category name is required"
    ]);
    exit;
}

// Insert safely using prepared statement
$stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
$stmt->bind_param("s", $name);

if ($stmt->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => "Category added successfully",
        "id" => $stmt->insert_id
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Failed to add category"
    ]);
}

$stmt->close();
$conn->close();
?>
