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

// Handle Logout - include this after session check
include 'admin-logout.php';

// Initialize counts with default values to prevent undefined variable errors
$userCount = 0;
$productCount = 0;
$pendingCount = 0;
$ondeliveryCount = 0;
$completedCount = 0;
$failedCount = 0;
$totalSales = 0;
$totalOrders = 0;

// Arrays for chart data
$chartMonths = [];
$chartSales = [];
$chartOrders = [];

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

// Fetch sales data
$salesData = [];
$salesResult = $conn->query("SELECT * FROM sales ORDER BY month DESC LIMIT 12");
if ($salesResult) {
    while ($row = $salesResult->fetch_assoc()) {
        $salesData[] = $row;
        $totalSales += $row['total_sales'];
        $totalOrders += $row['order_count'];
        
        // Prepare chart data (in reverse order for chronological display)
        array_unshift($chartMonths, date('M Y', strtotime($row['month'])));
        array_unshift($chartSales, $row['total_sales']);
        array_unshift($chartOrders, $row['order_count']);
    }
}

// Calculate average monthly sales
$avgMonthlySales = count($salesData) > 0 ? $totalSales / count($salesData) : 0;
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
    
    <!-- Chart.js for graphs -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        <!-- Stats Cards -->
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

        <!-- Sales Summary Cards -->
        <div class="sales-summary-cards">
            <div class="summary-card">
                <div class="summary-icon">💰</div>
                <div class="summary-content">
                    <span class="summary-card-label">Total Sales (12 Months)</span>
                    <span class="summary-card-value">₱<?php echo number_format($totalSales, 2); ?></span>
                </div>
            </div>
            <div class="summary-card">
                <div class="summary-icon">📦</div>
                <div class="summary-content">
                    <span class="summary-card-label">Total Orders (12 Months)</span>
                    <span class="summary-card-value"><?php echo $totalOrders; ?></span>
                </div>
            </div>
            <div class="summary-card">
                <div class="summary-icon">📊</div>
                <div class="summary-content">
                    <span class="summary-card-label">Monthly Average</span>
                    <span class="summary-card-value">₱<?php echo number_format($avgMonthlySales, 2); ?></span>
                </div>
            </div>
        </div>

        <!-- Chart Section -->
        <div class="chart-section">
            <h4>Sales & Orders Overview</h4>
            <div class="chart-container">
                <canvas id="salesChart"></canvas>
            </div>
        </div>

        <!-- Sales Table Section -->
        <div class="sales-section">
            <div class="sales-header">
                <h4>Monthly Sales Report (Last 12 Months)</h4>
                <div class="table-controls">
                    <span class="table-info">Showing <?php echo count($salesData); ?> records</span>
                </div>
            </div>
            
            <div class="sales-table-container">
                <?php if (count($salesData) > 0): ?>
                    <table class="sales-table">
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th>Year</th>
                                <th>Total Sales (₱)</th>
                                <th>Number of Orders</th>
                                <th>Average Order Value</th>
                                <th>Performance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $maxSales = max(array_column($salesData, 'total_sales'));
                            foreach ($salesData as $sale): 
                                $monthYear = date('F', strtotime($sale['month']));
                                $year = date('Y', strtotime($sale['month']));
                                $avgOrderValue = $sale['order_count'] > 0 ? $sale['total_sales'] / $sale['order_count'] : 0;
                                $performance = ($sale['total_sales'] / $maxSales) * 100;
                            ?>
                                <tr>
                                    <td class="month-name"><?php echo $monthYear; ?></td>
                                    <td><?php echo $year; ?></td>
                                    <td class="sales-amount">₱<?php echo number_format($sale['total_sales'], 2); ?></td>
                                    <td class="order-count">
                                        <span class="order-badge"><?php echo $sale['order_count']; ?></span>
                                    </td>
                                    <td>₱<?php echo number_format($avgOrderValue, 2); ?></td>
                                    <td>
                                        <div class="progress-bar">
                                            <div class="progress-fill" style="width: <?php echo $performance; ?>%;"></div>
                                            <span class="progress-text"><?php echo round($performance); ?>%</span>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2" style="text-align: right; font-weight: 600;">Totals / Averages:</td>
                                <td class="sales-amount">₱<?php echo number_format($totalSales, 2); ?></td>
                                <td class="order-count"><?php echo $totalOrders; ?></td>
                                <td>₱<?php echo number_format($totalOrders > 0 ? $totalSales / $totalOrders : 0, 2); ?></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                <?php else: ?>
                    <div class="no-data-message">
                        <p>No sales data available. Please run the SQL script to create and populate the sales table.</p>
                        <p style="font-size: 0.9em; color: #999; margin-top: 10px;">
                            Run the following SQL to create the sales table:<br>
                            <code style="background: #f4f4f4; padding: 5px; display: block; margin-top: 5px;">
                                CREATE TABLE sales (id INT AUTO_INCREMENT PRIMARY KEY, month DATE UNIQUE, total_sales DECIMAL(10,2), order_count INT);
                            </code>
                        </p>
                    </div>
                <?php endif; ?>
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

        // Create chart
        const ctx = document.getElementById('salesChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($chartMonths); ?>,
                datasets: [{
                    label: 'Sales (₱)',
                    data: <?php echo json_encode($chartSales); ?>,
                    borderColor: '#4CAF50',
                    backgroundColor: 'rgba(76, 175, 80, 0.1)',
                    borderWidth: 3,
                    pointBackgroundColor: '#4CAF50',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    yAxisID: 'y-sales',
                    tension: 0.3,
                    fill: true
                }, {
                    label: 'Number of Orders',
                    data: <?php echo json_encode($chartOrders); ?>,
                    borderColor: '#2196F3',
                    backgroundColor: 'rgba(33, 150, 243, 0.1)',
                    borderWidth: 3,
                    pointBackgroundColor: '#2196F3',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    yAxisID: 'y-orders',
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            font: {
                                size: 12,
                                family: 'Poppins'
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleFont: {
                            size: 13,
                            family: 'Poppins'
                        },
                        bodyFont: {
                            size: 12,
                            family: 'Poppins'
                        },
                        padding: 10,
                        cornerRadius: 6
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                family: 'Poppins',
                                size: 11
                            }
                        }
                    },
                    'y-sales': {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Sales (₱)',
                            font: {
                                family: 'Poppins',
                                size: 11,
                                weight: '500'
                            }
                        },
                        ticks: {
                            callback: function(value) {
                                return '₱' + value.toLocaleString();
                            },
                            font: {
                                family: 'Poppins',
                                size: 10
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    'y-orders': {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Number of Orders',
                            font: {
                                family: 'Poppins',
                                size: 11,
                                weight: '500'
                            }
                        },
                        ticks: {
                            stepSize: 5,
                            font: {
                                family: 'Poppins',
                                size: 10
                            }
                        },
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>