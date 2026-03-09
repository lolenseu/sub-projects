<?php
// Start the session
session_start();

// Database connection
include 'connection.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id'])) {
    // Not logged in - redirect to home page
    header("Location: index.php");
    exit();
}

// Check if user has admin role (if role column exists)
// You can uncomment this if you have role column in your users table
/*
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // Logged in but not admin - redirect to home page
    header("Location: index.php");
    exit();
}
*/

// Handle Logout - include this after session check
include 'admin-logout.php';

// Initialize counts with default values to prevent undefined variable errors
$userCount = 0;
$productCount = 0;
$pendingCount = 0;
$ondeliveryCount = 0;
$completedCount = 0;
$failedCount = 0;

// Count users with error handling
$userResult = $conn->query("SELECT COUNT(id) AS user_count FROM users");
if ($userResult) {
    $userRow = $userResult->fetch_assoc();
    $userCount = $userRow['user_count'];
} else {
    error_log("Error counting users: " . $conn->error);
}

// Count products with error handling
$productResult = $conn->query("SELECT COUNT(id) AS product_count FROM products");
if ($productResult) {
    $productRow = $productResult->fetch_assoc();
    $productCount = $productRow['product_count'];
} else {
    error_log("Error counting products: " . $conn->error);
}

// Check if product_status table exists before querying
$tableCheck = $conn->query("SHOW TABLES LIKE 'product_status'");
if ($tableCheck && $tableCheck->num_rows > 0) {
    // Count pending orders
    $pendingResult = $conn->query("SELECT COUNT(id) AS pending_count FROM product_status WHERE status = 'pending'");
    if ($pendingResult) {
        $pendingRow = $pendingResult->fetch_assoc();
        $pendingCount = $pendingRow['pending_count'];
    }

    // Count ondelivery orders
    $ondeliveryResult = $conn->query("SELECT COUNT(id) AS ondelivery_count FROM product_status WHERE status = 'ondelivery'");
    if ($ondeliveryResult) {
        $ondeliveryRow = $ondeliveryResult->fetch_assoc();
        $ondeliveryCount = $ondeliveryRow['ondelivery_count'];
    }

    // Count delivered orders
    $completedResult = $conn->query("SELECT COUNT(id) AS completed_count FROM product_status WHERE status IN ('delivered')");
    if ($completedResult) {
        $completedRow = $completedResult->fetch_assoc();
        $completedCount = $completedRow['completed_count'];
    }

    // Count failed orders
    $failedResult = $conn->query("SELECT COUNT(id) AS failed_count FROM product_status WHERE status IN ('failed')");
    if ($failedResult) {
        $failedRow = $failedResult->fetch_assoc();
        $failedCount = $failedRow['failed_count'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kimjay&Madel - Admin - Statistics</title>

    <link href="https://fonts.googleapis.com/css?family=Poppins" rel="stylesheet">

    <link href="styles/style.css" rel="stylesheet">
    <link href="styles/navbar.css" rel="stylesheet">
    <link href="styles/admin-navbar.css" rel="stylesheet">
    <link href="styles/admin-statistics.css" rel="stylesheet">
    
    <style>
        .welcome-message {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            margin-bottom: 20px;
            border-radius: 4px;
            text-align: center;
        }
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="admin-header">
        <div class="logo">Kimjay&Madel</div>
        <ul class="nav-links" id="nav-links">
            <li><a href="admin-statistics.php" class="active">Statistics</a></li>
            <li><a href="admin-users.php">Users</a></li>
            <li><a href="admin-status.php">Status</a></li>
            <li><a href="admin-products.php">Products</a></li>
        </ul>
        <div class="admin-user-info">
            <span class="admin-name">Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></span>
            <form id="logoutForm" method="POST" action="admin-logout.php" style="display: inline;">
                <input type="hidden" name="action" value="logout">
                <button type="submit" class="logout-btn">Logout</button>
            </form>
        </div>
    </header>

    <!-- Main Content -->
    <div class="admin-container">
        <h3>Statistics Dashboard</h3>
        
        <?php if (isset($_SESSION['just_logged_in'])): ?>
            <div class="welcome-message">
                Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!
            </div>
            <?php unset($_SESSION['just_logged_in']); ?>
        <?php endif; ?>

        <div class="stats-container">
            <div class="stat-box">
                <div class="stat-number"><?php echo $userCount; ?></div>
                <span class="stat-label">Total Users</span>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?php echo $productCount; ?></div>
                <span class="stat-label">Total Products</span>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?php echo $pendingCount; ?></div>
                <span class="stat-label">Pending Orders</span>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?php echo $ondeliveryCount; ?></div>
                <span class="stat-label">On Delivery</span>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?php echo $completedCount; ?></div>
                <span class="stat-label">Completed Orders</span>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?php echo $failedCount; ?></div>
                <span class="stat-label">Failed Orders</span>
            </div>
        </div>
        
        <!-- Quick Actions Section -->
        <div class="quick-actions">
            <h4>Quick Actions</h4>
            <div class="action-buttons">
                <a href="admin-users.php" class="action-btn">Manage Users</a>
                <a href="admin-products.php" class="action-btn">Manage Products</a>
                <a href="admin-status.php" class="action-btn">View Orders</a>
            </div>
        </div>
    </div>

    <script src="scripts/admin.js"></script>
    
    <script>
        // Add confirmation for logout
        document.getElementById('logoutForm').addEventListener('submit', function(e) {
            if (!confirm('Are you sure you want to logout?')) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>