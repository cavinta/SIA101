<?php
require '../../config/database.php';

$result = $conn->query("SELECT id, name, price_modifier FROM sizes ORDER BY name ASC");

$sizes = [];

while($row = $result->fetch_assoc()){
    $sizes[] = $row;
}

echo json_encode(["status" => "success", "data" => $sizes]);
$conn->close();
?>