<?php
require '../../config/database.php';
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status"=>"error","message"=>"Only POST allowed"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$id = intval($data['id'] ?? 0);
$name = trim($data['name'] ?? '');
$price_modifier = floatval($data['price_modifier'] ?? 0);

if (!$id || !$name) {
    echo json_encode(["status"=>"error","message"=>"ID and name are required"]);
    exit;
}

$stmt = $conn->prepare("UPDATE sizes SET name=?, price_modifier=? WHERE id=?");
$stmt->bind_param("sdi", $name, $price_modifier, $id);

if ($stmt->execute()) {
    echo json_encode([
        "status"=>"success",
        "message"=>"Size updated",
        "data"=>[
            "id"=>$id,
            "name"=>$name,
            "price_modifier"=>$price_modifier
        ]
    ]);
} else {
    echo json_encode(["status"=>"error","message"=>"Database error"]);
}
?>

