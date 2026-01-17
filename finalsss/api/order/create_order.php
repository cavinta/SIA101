<?php
require '../../config/database.php';
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status"=>"error","message"=>"Only POST allowed"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$customer_name = trim($data['customer_name'] ?? '');
$order_items = $data['items'] ?? [];

if ($customer_name === '' || empty($order_items)) {
    http_response_code(400);
    echo json_encode(["status"=>"error","message"=>"Customer name and items are required"]);
    exit;
}

$total_order_price = 0;

$conn->begin_transaction();

try {

    foreach ($order_items as &$item) {

        $item_id  = intval($item['item_id'] ?? 0);
        $quantity = max(1, intval($item['quantity'] ?? 1));
        $addon_ids = $item['addons'] ?? [];
        $size_ids  = $item['size'] ?? [];

        // Item price
        $stmt = $conn->prepare("SELECT price FROM items WHERE id=?");
        $stmt->bind_param("i", $item_id);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 0) {
            throw new Exception("Item ID $item_id not found");
        }

        $item_price = floatval($res->fetch_assoc()['price']);

        // Addons total
        $addons_total = 0;
        if (!empty($addon_ids)) {
            $ids = implode(",", array_map('intval', $addon_ids));
            $r = $conn->query("SELECT SUM(price) AS total FROM addons WHERE id IN ($ids)");
            $addons_total = floatval($r->fetch_assoc()['total'] ?? 0);
        }

        // Sizes total ✅ FIXED
        $size_total = 0;
        if (!empty($size_ids)) {
            $ids_size = implode(",", array_map('intval', $size_ids));
            $r = $conn->query("SELECT SUM(price_modifier) AS total FROM sizes WHERE id IN ($ids_size)");
            $size_total = floatval($r->fetch_assoc()['total'] ?? 0);
        }

        $item['total_price'] = ($item_price + $addons_total + $size_total) * $quantity;
        $total_order_price += $item['total_price'];
    }

    // Insert order
    $stmt_order = $conn->prepare("INSERT INTO orders (customer_name, total_price) VALUES (?, ?)");
    $stmt_order->bind_param("sd", $customer_name, $total_order_price);
    $stmt_order->execute();
    $order_id = $stmt_order->insert_id;

    // Insert order items
    foreach ($order_items as $item) {

        $item_id = intval($item['item_id']);
        $quantity = intval($item['quantity']);
        $addons_str = !empty($item['addons']) ? implode(",", $item['addons']) : null;
        $size_str = !empty($item['size']) ? implode(",", $item['size']) : null;
        $item_price = $item['total_price'];

        $stmt_item = $conn->prepare(
            "INSERT INTO order_items (order_id, item_id, quantity, addons, size, item_price)
             VALUES (?, ?, ?, ?, ?, ?)"
        );

        $stmt_item->bind_param("iiisds", $order_id, $item_id, $quantity, $addons_str, $size_str, $item_price);
        $stmt_item->execute();
    }

    $conn->commit();

    echo json_encode([
        "status"=>"success",
        "message"=>"Order created successfully",
        "order_id"=>$order_id,
        "total_price"=>$total_order_price
    ]);

} catch (Exception $e) {

    $conn->rollback();

    http_response_code(400);
    echo json_encode([
        "status"=>"error",
        "message"=>$e->getMessage()
    ]);
}

$conn->close();
?>