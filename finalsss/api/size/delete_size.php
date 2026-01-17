<?php
require '../../config/database.php';
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);
$id = intval($data['id'] ?? 0);

if(!$id){
    echo json_encode(["status"=>"error","message"=>"ID required"]);
    exit;
}

$stmt = $conn->prepare("DELETE FROM sizes WHERE id=?");
$stmt->bind_param("i", $id);

if($stmt->execute()){
    echo json_encode(["status"=>"success","message"=>"Deleted"]);
}else{
    echo json_encode(["status"=>"error","message"=>"Delete failed"]);
}
