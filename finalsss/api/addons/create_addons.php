<?php
require '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Only POST allowed"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$name = trim($data['name'] ?? '');
$price = floatval($data['price'] ?? 0);

if ($name === '' || $price < 0) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Name and price are required"]);
    exit;
}


$stmt = $conn->prepare("INSERT INTO addons (name, price) VALUES (?, ?)");
$stmt->bind_param("sd", $name, $price);

if ($stmt->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => "Add-on added successfully",
        "addon_id" => $stmt->insert_id
    ]);
} else {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Failed to add add-on"]);
}

$stmt->close();
$conn->close();
?>
