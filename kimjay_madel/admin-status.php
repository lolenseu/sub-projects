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
    header("Location: admin-status.php");
    exit();
}

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['admin_comment']) && !empty($_POST['admin_comment'])) {
    $orderId = intval($_POST['order_id']);
    $comment = trim($_POST['admin_comment']);
    $adminId = $_SESSION['user_id'];
    
    // Insert comment
    $stmt = $conn->prepare("INSERT INTO product_comments (order_id, admin_id, comment) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $orderId, $adminId, $comment);
    $stmt->execute();
    $stmt->close();
    
    header("Location: admin-status.php");
    exit();
}

// Fetch pending orders with user and product info (NO COMMENTS HERE)
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

// Fetch all orders with comments (for delivered/failed orders)
$allOrdersSql = "
    SELECT po.id, po.user_id, po.product_id, po.quantity, po.status, po.order_time,
           u.username, u.email, u.address,
           p.name AS product_name, p.price, p.product_img,
           pc.comment, pc.comment_time, a.username as admin_username
    FROM product_status po
    JOIN users u ON po.user_id = u.id
    JOIN products p ON po.product_id = p.id
    LEFT JOIN product_comments pc ON po.id = pc.order_id
    LEFT JOIN users a ON pc.admin_id = a.id
    WHERE po.status IN ('delivered', 'failed')
    ORDER BY po.order_time DESC
";
$allOrders = $conn->query($allOrdersSql);
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
        <!-- First Container: Pending Orders (NO COMMENTS) -->
        <div class="first-container">
            <h3>Pending Orders</h3>
            <div class="orders">
                <?php if ($pendingOrders && $pendingOrders->num_rows > 0): ?>
                    <?php while ($order = $pendingOrders->fetch_assoc()): ?>
                        <div class="order-row">
                            <img src="data:image/jpeg;base64,<?php echo base64_encode($order['product_img']); ?>" alt="Product">
                            <div class="order-info">
                                <div class="product-name"><strong><?php echo htmlspecialchars($order['product_name']); ?></strong> (x<?php echo $order['quantity']; ?>)</div>
                                <div class="product-price">₱<?php echo number_format($order['price'], 2); ?></div>
                                <div class="user-detail">User: <?php echo htmlspecialchars($order['username']); ?></div>
                                <div class="user-detail">Email: <?php echo htmlspecialchars($order['email']); ?></div>
                                <div class="user-detail">Address: <?php echo htmlspecialchars($order['address']); ?></div>
                                <div class="order-time">Order Time: <?php echo $order['order_time']; ?></div>
                            </div>
                            <div class="order-actions">
                                <form method="POST" class="status-form">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <select name="new_status" class="status-select">
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
                    <p class="no-orders">No pending orders.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Second Container: Completed/Failed Orders (WITH COMMENTS) -->
        <div class="second-container">
            <h3>Completed & Failed Orders</h3>
            <?php if ($allOrders && $allOrders->num_rows > 0): ?>
                <?php while ($order = $allOrders->fetch_assoc()): ?>
                    <div class="order-row completed-order">
                        <img src="data:image/jpeg;base64,<?php echo base64_encode($order['product_img']); ?>" alt="Product">
                        <div class="order-info">
                            <div class="product-name"><strong><?php echo htmlspecialchars($order['product_name']); ?></strong> (x<?php echo $order['quantity']; ?>)</div>
                            <div class="product-price">₱<?php echo number_format($order['price'], 2); ?></div>
                            <div class="user-detail">User: <?php echo htmlspecialchars($order['username']); ?></div>
                            <div class="user-detail">Email: <?php echo htmlspecialchars($order['email']); ?></div>
                            <div class="user-detail">Address: <?php echo htmlspecialchars($order['address']); ?></div>
                            <div class="order-time">Order Time: <?php echo $order['order_time']; ?></div>
                            
                            <!-- Display existing comment if any -->
                            <?php if (!empty($order['comment'])): ?>
                                <div class="comment-display">
                                    <div class="comment-header">
                                        <span class="comment-author">Admin Comment</span>
                                        <span class="comment-date"><?php echo date('M d, Y', strtotime($order['comment_time'])); ?></span>
                                    </div>
                                    <div class="comment-body">
                                        <?php echo htmlspecialchars($order['comment']); ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="order-actions">
                            <span class="status-badge <?php echo $order['status']; ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                            
                            <!-- Comment form for admin to add comments -->
                            <div class="comment-section">
                                <form method="POST" class="comment-form">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <textarea name="admin_comment" placeholder="Add a comment for this order..." rows="3"></textarea>
                                    <button type="submit" class="comment-btn">
                                        <span class="comment-icon">💬</span> Add Comment
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="no-orders">No completed or failed orders.</p>
            <?php endif; ?>
        </div>
    </div>

    <script src="scripts/admin.js"></script>
</body>
</html>