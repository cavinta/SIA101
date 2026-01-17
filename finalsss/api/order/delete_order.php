<?php
require '../../config/database.php';
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status"=>"error","message"=>"Only POST allowed"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$order_id = intval($data['id'] ?? 0);

if (!$order_id) {
    echo json_encode(["status"=>"error","message"=>"Order ID required"]);
    exit;
}

$conn->begin_transaction();

try {

    $stmt1 = $conn->prepare("DELETE FROM order_items WHERE order_id=?");
    $stmt1->bind_param("i", $order_id);
    $stmt1->execute();

    $stmt2 = $conn->prepare("DELETE FROM orders WHERE id=?");
    $stmt2->bind_param("i", $order_id);
    $stmt2->execute();

    $conn->commit();

    echo json_encode(["status"=>"success","message"=>"Order deleted"]);

} catch (Exception $e) {

    $conn->rollback();
    echo json_encode(["status"=>"error","message"=>"Delete failed"]);

}

$conn->close();
?>
