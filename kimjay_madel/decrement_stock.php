<?php
session_start();
include 'connection.php';
header('Content-Type: application/json');

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (empty($data) || !is_array($data)) {
    echo json_encode(['error' => 'Invalid payload']);
    exit;
}
$ok = true;
foreach ($data as $item) {
    if (!isset($item['id'], $item['qty']) || !is_numeric($item['id']) || !is_numeric($item['qty']) || $item['qty'] <= 0) {
        $ok = false;
        break;
    }
    $stmt = $conn->prepare('SELECT quantity FROM products WHERE id=? FOR UPDATE');
    $stmt->bind_param('i', $item['id']);
    $stmt->execute();
    $stmt->bind_result($cur_qty);
    if (!$stmt->fetch()) {
        $ok = false;
        $stmt->close();
        break;
    }
    $new_qty = $cur_qty - $item['qty'];
    if ($new_qty < 0) {
        $ok = false;
        $stmt->close();
        break;
    }
    $upd = $conn->prepare('UPDATE products SET quantity=? WHERE id=?');
    $upd->bind_param('di', $new_qty, $item['id']);
    if (!$upd->execute()) {
        $ok = false;
        $upd->close();
        break;
    }
    $stmt->close();
    $upd->close();
}
if ($ok) {
    echo json_encode(['status' => 'ok']);
} else {
    echo json_encode(['error' => 'Stock update failed']);
}
?>