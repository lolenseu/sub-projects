<?php
header('Content-Type: application/json');

// Database connection
include 'connection.php';

$query = trim($_GET['q'] ?? '');
if (!$query) {
    echo json_encode(['success' => false, 'error' => 'No query']);
    exit;
}

// Use simple LIKE search instead of SOUNDEX
$stmt = $conn->prepare("SELECT id, name, price, product_img FROM products WHERE name LIKE CONCAT('%', ?, '%') LIMIT 1");
$stmt->bind_param("s", $query);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode([
        'success' => true,
        'product' => [
            'id' => $row['id'],
            'name' => $row['name'],
            'price' => $row['price'],
            'img' => $row['product_img'] // Return image path instead of base64
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'No product found']);
}
?>
