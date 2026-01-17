<?php
require '../../config/database.php';
header("Content-Type: application/json");

$result = $conn->query("SELECT * FROM orders ORDER BY id DESC");

$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}

echo json_encode([
    "status" => "success",
    "data" => $orders
]);

$conn->close();
?>
