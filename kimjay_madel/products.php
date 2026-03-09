<?php
// Fetch products from the database
$sql = "SELECT * FROM products";
$result = $conn->query($sql);

// Handle AJAX request for product suggestions
if (isset($_GET['query'])) {
    $query = $_GET['query'];
    $stmt = $conn->prepare("
        SELECT name, price 
        FROM products 
        WHERE name LIKE CONCAT('%', ?, '%') 
        OR price LIKE CONCAT('%', ?, '%') 
        LIMIT 10
    ");
    $stmt->bind_param("ss", $query, $query);
    $stmt->execute();
    $result = $stmt->get_result();

    $suggestions = [];
    while ($row = $result->fetch_assoc()) {
        $suggestions[] = [
            'name' => $row['name'],
            'price' => $row['price']
        ];
    }

    echo json_encode($suggestions);
    exit();
}
?>