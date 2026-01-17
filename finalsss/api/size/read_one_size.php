<?php
require '../../config/database.php';
header("Content-Type: application/json");

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    echo json_encode(["status"=>"error","message"=>"ID required"]);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM sizes WHERE id=? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if ($data) {
    echo json_encode(["status"=>"success","data"=>$data]);
} else {
    echo json_encode(["status"=>"error","message"=>"Size not found"]);
}
?>