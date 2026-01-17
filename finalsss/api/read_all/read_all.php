<?php
require '../../config/db.php';

header("Content-Type: application/json");

try {

    // 1. Get all orders
    $stmt = $pdo->prepare("
        SELECT id, customer_name, total_price, created_at
        FROM orders
        ORDER BY created_at DESC
    ");
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$orders) {
        echo json_encode([
            "status" => "success",
            "data" => []
        ]);
        exit;
    }

    // 2. Get items for each order
    foreach ($orders as &$order) {

        $itemStmt = $pdo->prepare("
            SELECT 
                oi.id AS order_item_id,
                oi.item_id,
                i.name AS item_name,
                oi.quantity,
                oi.size,
                oi.addons,
                oi.item_price
            FROM order_items oi
            JOIN items i ON oi.item_id = i.id
            WHERE oi.order_id = ?
        ");

        $itemStmt->execute([$order['id']]);
        $order['items'] = $itemStmt->fetchAll(PDO::FETCH_ASSOC);
    }

    echo json_encode([
        "status" => "success",
        "data" => $orders
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Server error"
    ]);
}
