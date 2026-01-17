<?php
require '../../config/database.php';
header("Content-Type: application/json");

$order_id = intval($_GET['id'] ?? 0);

if (!$order_id) {
    echo json_encode(["status"=>"error","message"=>"Order ID required"]);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM orders WHERE id=?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_res = $stmt->get_result();

if ($order_res->num_rows === 0) {
    echo json_encode(["status"=>"error","message"=>"Order not found"]);
    exit;
}

$order = $order_res->fetch_assoc();

$stmt2 = $conn->prepare("
    SELECT oi.*, i.name AS item_name
    FROM order_items oi
    JOIN items i ON oi.item_id = i.id
    WHERE oi.order_id=?
");
$stmt2->bind_param("i", $order_id);
$stmt2->execute();
$items_res = $stmt2->get_result();

$items = [];
while ($row = $items_res->fetch_assoc()) {
    $items[] = $row;
}

echo json_encode([
    "status"=>"success",
    "order"=>$order,
    "items"=>$items
]);

$conn->close();
?>
