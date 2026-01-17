<?php
require '../../config/database.php';

$category_id = intval($_GET['category_id'] ?? 0);

if ($category_id > 0) {
    $stmt = $conn->prepare("
        SELECT items.id, items.name, items.price, categories.name AS category_name
        FROM items 
        JOIN categories ON items.category_id = categories.id
        WHERE category_id = ?
        ORDER BY items.name ASC
    ");
    $stmt->bind_param("i", $category_id);
} else {
    $stmt = $conn->prepare("
        SELECT items.id, items.name, items.price, categories.name AS category_name
        FROM items 
        JOIN categories ON items.category_id = categories.id
        ORDER BY items.name ASC
    ");
}

$stmt->execute();
$result = $stmt->get_result();
$items = [];
while ($row = $result->fetch_assoc()) {
    $items[] = $row;
}

echo json_encode(["status" => "success", "data" => $items]);

$stmt->close();
$conn->close();
?>
