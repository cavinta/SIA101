<?php
require '../../config/database.php';
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

$id = intval($data['id'] ?? 0);
$status = trim($data['status'] ?? '');

if (!$id || !$status) {
    echo json_encode(["status"=>"error","message"=>"ID and status required"]);
    exit;
}

$stmt = $conn->prepare("UPDATE orders SET status=? WHERE id=?");
$stmt->bind_param("si", $status, $id);

if ($stmt->execute()) {
    echo json_encode(["status"=>"success","message"=>"Status updated"]);
} else {
    echo json_encode(["status"=>"error","message"=>"Update failed"]);
}

$conn->close();
?>
