<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'connection.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // If not admin, redirect to home page
    header("Location: index.php");
    exit();
}

include 'admin-logout.php';

// Handle delete request
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $deleteId = $_GET['delete'];
    
    // Prevent deleting yourself
    if ($deleteId != $_SESSION['user_id']) {
        $deleteStmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $deleteStmt->bind_param("i", $deleteId);
        
        if ($deleteStmt->execute()) {
            header("Location: admin-users.php?message=User deleted successfully");
            exit();
        } else {
            $error = "Error deleting user: " . $conn->error;
        }
        $deleteStmt->close();
    } else {
        $error = "You cannot delete your own account!";
    }
}

$userResult = $conn->query("SELECT id, username, email, role FROM users");

if (!$userResult) {
    die("Error fetching users: " . $conn->error);
}

$userRows = [];
while ($row = $userResult->fetch_assoc()) {
    $userRows[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kimjay&Madel - Admin - Users</title>
    <link href="https://fonts.googleapis.com/css?family=Poppins" rel="stylesheet">
    <link href="styles/style.css" rel="stylesheet">
    <link href="styles/navbar.css" rel="stylesheet">
    <link href="styles/admin-navbar.css" rel="stylesheet">
    <link href="styles/admin-users.css" rel="stylesheet">
    <style>
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
    </style>
</head>
<body>

<div class="admin-dashboard">
    <div class="sidebar">
        <div class="header-in-sidebar">
            <div class="logo">Kimjay&Madel</div>
            <ul class="nav-links">
                <li><a href="admin-statistics.php">Statistics</a></li>
                <li><a href="admin-users.php" class="active">Users</a></li>
                <li><a href="admin-status.php">Status</a></li>
                <li><a href="admin-products.php">Products</a></li>
            </ul>
            <form method="POST" action="admin-logout.php">
                <button type="submit" name="logout" class="logout-btn">Logout</button>
            </form>
        </div>
    </div>

    <div class="main-content">
        <div class="admin-container">
            <h3>All Users</h3>
            
            <?php if (isset($_GET['message'])): ?>
                <div class="success-message">
                    <?php echo htmlspecialchars($_GET['message']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <table class="users-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($userRows as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['id']); ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['role']); ?></td>
                            <td>
                                <button class="edit-btn">Edit</button>
                                <button onclick="confirmDelete(<?php echo $user['id']; ?>)" class="delete-btn">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function confirmDelete(userId) {
    if (confirm('Are you sure you want to delete this user?')) {
        window.location.href = 'admin-users.php?delete=' + userId;
    }
}
</script>
<script src="scripts/admin.js"></script>
</body>
</html>