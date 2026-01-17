<?php
require '../../config/database.php';

/* Fetch all categories */
$result = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");

$categories = [];

while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}

echo json_encode([
    "status" => "success",
    "data" => $categories
]);

$conn->close();
?>


