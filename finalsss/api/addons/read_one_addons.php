<?php
require '../../config/database.php';

// Get the category ID from query string ?id=
$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Please provide a valid category ID using ?id="
    ]);
    exit;
}

// Prepare statement to fetch one category
$stmt = $conn->prepare("SELECT id, name FROM addons WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode([
        "status" => "error",
        "message" => "Category not found"
    ]);
} else {
    $category = $result->fetch_assoc();
    echo json_encode([
        "status" => "success",
        "data" => $category
    ]);
}

$stmt->close();
$conn->close();
?>
