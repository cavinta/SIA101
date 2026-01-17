<?php
require '../../config/database.php';
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status"=>"error","message"=>"Only POST allowed"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$name = trim($data['name'] ?? '');
$price_modifier = floatval($data['price_modifier'] ?? 0);

if (!$name) {
    echo json_encode(["status"=>"error","message"=>"Size name required"]);
    exit;
}

$stmt = $conn->prepare("INSERT INTO sizes (name, price_modifier) VALUES (?, ?)");
$stmt->bind_param("sd", $name, $price_modifier);

if ($stmt->execute()) {
    echo json_encode([
        "status"=>"success",
        "message"=>"Size created",
        "data"=>[
            "id"=>$stmt->insert_id,
            "name"=>$name,
            "price_modifier"=>$price_modifier
        ]
    ]);
} else {
    echo json_encode(["status"=>"error","message"=>"Database error"]);
}
?>
