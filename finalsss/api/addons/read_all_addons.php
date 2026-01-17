<?php
require '../../config/database.php';

$result = $conn->query("SELECT id, name, price FROM addons ORDER BY name ASC");

$addons = [];
while ($row = $result->fetch_assoc()) {
    $addons[] = $row;
}

echo json_encode(["status" => "success", "data" => $addons]);

$conn->close();
?>
