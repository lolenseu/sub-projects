<?php

// Start the session
session_start();

// Database connection
include 'connection.php';

// Handle Logout
include 'admin-logout.php';

// Fetch admin user profile (assuming admin is logged in)
$adminId = $_SESSION['user_id'] ?? null;
$adminData = null;
if ($adminId) {
    $stmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
    $stmt->bind_param("i", $adminId);
    $stmt->execute();
    $adminData = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['new_status'])) {
    $orderId = intval($_POST['order_id']);
    $newStatus = $_POST['new_status'];
    $stmt = $conn->prepare("UPDATE product_status SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $newStatus, $orderId);
    $stmt->execute();
    $stmt->close();
    header("Location: admin-status.php"); // <-- Change this line
    exit();
}

// Fetch pending orders with user and product info
$pendingSql = "
    SELECT po.id, po.user_id, po.product_id, po.quantity, po.status, po.order_time,
           u.username, u.email, u.address,
           p.name AS product_name, p.price, p.product_img
    FROM product_status po
    JOIN users u ON po.user_id = u.id
    JOIN products p ON po.product_id = p.id
    WHERE po.status IN ('pending', 'ondelivery')
    ORDER BY po.order_time DESC
";
$pendingOrders = $conn->query($pendingSql);

// Fetch pending/ondelivery/delivered/failed orders
$completedSql = "
    SELECT po.id, po.user_id, po.product_id, po.quantity, po.status, po.order_time,
           u.username, u.email, u.address,
           p.name AS product_name, p.price, p.product_img
    FROM product_status po
    JOIN users u ON po.user_id = u.id
    JOIN products p ON po.product_id = p.id
    WHERE po.status IN ('pending', 'ondelivery', 'delivered', 'failed')
    ORDER BY po.order_time DESC
";
$completedOrders = $conn->query($completedSql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kimjay&Madel - Admin - Status</title>

    <link href="https://fonts.googleapis.com/css?family=Poppins" rel="stylesheet">

    <link href="styles/style.css" rel="stylesheet">
    <link href="styles/navbar.css" rel="stylesheet">
    <link href="styles/admin-navbar.css" rel="stylesheet">
    <link href="styles/admin-status.css" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header class="admin-header">
        <div class="logo">Kimjay&Madel</div>
        <ul class="nav-links" id="nav-links">
            <li><a href="admin-statistics.php">Statistics</a></li>
            <li><a href="admin-users.php">Users</a></li>
            <li><a href="admin-status.php" class="active">Status</a></li>
            <li><a href="admin-products.php">Products</a></li>
        </ul>
        <form id="logoutForm" method="POST" action="admin-logout.php">
            <input type="hidden" name="action" value="logout">
            <button type="submit" class="logout-btn">Logout</button>
        </form>
    </header>

    <!-- Main Content -->
    <div class="admin-container">
        <!-- First Container: Pending Orders -->
        <div class="first-container">
            <h3>Pending Option</h3>
            <div class="orders">
                <?php if ($pendingOrders->num_rows > 0): ?>
                    <?php while ($order = $pendingOrders->fetch_assoc()): ?>
                        <div class="order-row">
                            <img src="data:image/jpeg;base64,<?php echo base64_encode($order['product_img']); ?>" alt="Product">
                            <div class="order-info">
                                <div><strong><?php echo htmlspecialchars($order['product_name']); ?></strong> (x<?php echo $order['quantity']; ?>)</div>
                                <div>₱<?php echo number_format($order['price'], 2); ?></div>
                                <div>User: <?php echo htmlspecialchars($order['username']); ?> (<?php echo htmlspecialchars($order['email']); ?>)</div>
                                <div>Address: <?php echo htmlspecialchars($order['address']); ?></div>
                                <div>Order Time: <?php echo $order['order_time']; ?></div>
                            </div>
                            <div class="order-actions">
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <select name="new_status">
                                        <option value="pending" <?php if($order['status']=='pending') echo 'selected'; ?>>Pending</option>
                                        <option value="ondelivery" <?php if($order['status']=='ondelivery') echo 'selected'; ?>>OnDelivery</option>
                                        <option value="delivered">Delivered</option>
                                        <option value="failed">Failed</option>
                                    </select>
                                    <button type="submit" class="edit-btn">Update</button>
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No pending orders.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Second Container: Completed/Delivered Orders Log -->
        <div class="second-container">
            <h3>Status</h3>
            <?php if ($completedOrders->num_rows > 0): ?>
                <?php while ($order = $completedOrders->fetch_assoc()): ?>
                    <div class="order-row">
                        <img src="data:image/jpeg;base64,<?php echo base64_encode($order['product_img']); ?>" alt="Product">
                        <div class="order-info">
                            <div><strong><?php echo htmlspecialchars($order['product_name']); ?></strong> (x<?php echo $order['quantity']; ?>)</div>
                            <div>₱<?php echo number_format($order['price'], 2); ?></div>
                            <div>User: <?php echo htmlspecialchars($order['username']); ?> (<?php echo htmlspecialchars($order['email']); ?>)</div>
                            <div>Address: <?php echo htmlspecialchars($order['address']); ?></div>
                            <div>Order Time: <?php echo $order['order_time']; ?></div>
                        </div>
                        <div class="order-actions">
                            <span class="status-badge <?php echo $order['status']; ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No completed or delivered orders.</p>
            <?php endif; ?>
        </div>
    </div>

    <script src="scripts/admin.js"></script>
</body>
</html>