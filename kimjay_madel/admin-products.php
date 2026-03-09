<?php

session_start();
include 'connection.php';
include 'admin-logout.php';
$sql = "SELECT id, name, price, description, product_img, quantity FROM products";
$result = $conn->query($sql);
if (isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $quantity = (int)$_POST['quantity'];
    $image = null;
    if (!empty($_FILES['product_img']['tmp_name'])) {
        $image = file_get_contents($_FILES['product_img']['tmp_name']);
    }
    $res = $conn->query("SELECT MAX(id) AS max_id FROM products");
    $row = $res->fetch_assoc();
    $nextId = $row['max_id'] ? $row['max_id'] + 1 : 1;
    $stmt = $conn->prepare("INSERT INTO products (id, name, price, description, product_img, quantity) VALUES (?,?,?,?,?,?)");
    $null = null;
    $stmt->bind_param("isdsii", $nextId, $name, $price, $description, $null, $quantity);
    $stmt->send_long_data(4, $image);
    if ($stmt->execute()) {
        header("Location: admin-products.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
if (isset($_POST['edit_product'])) {
    $id = (int)$_POST['product_id'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $quantity = (int)$_POST['quantity'];
    $image = null;
    if (!empty($_FILES['product_img']['tmp_name'])) {
        $image = file_get_contents($_FILES['product_img']['tmp_name']);
        $stmt = $conn->prepare("UPDATE products SET name=?, price=?, description=?, product_img=?, quantity=? WHERE id=?");
        $null = null;
        $stmt->bind_param("sdsiii", $name, $price, $description, $null, $quantity, $id);
        $stmt->send_long_data(3, $image);
    } else {
        $stmt = $conn->prepare("UPDATE products SET name=?, price=?, description=?, quantity=? WHERE id=?");
        $stmt->bind_param("sdsii", $name, $price, $description, $quantity, $id);
    }
    if ($stmt->execute()) {
        header("Location: admin-products.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM products WHERE id=?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $stmt->close();
        header("Location: admin-products.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kimjay&Madel - Admin - Products</title>
    <link href="https://fonts.googleapis.com/css?family=Poppins" rel="stylesheet">
    <link href="styles/style.css" rel="stylesheet">
    <link href="styles/navbar.css" rel="stylesheet">
    <link href="styles/admin-navbar.css" rel="stylesheet">
    <link href="styles/admin-products.css" rel="stylesheet">
</head>
<body>
<header class="admin-header">
    <div class="logo">Kimjay&Madel</div>
    <ul class="nav-links" id="nav-links">
        <li><a href="admin-statistics.php">Statistics</a></li>
        <li><a href="admin-users.php">Users</a></li>
        <li><a href="admin-status.php">Status</a></li>
        <li><a href="admin-products.php" class="active">Products</a></li>
    </ul>
    <form method="POST" action="admin-logout.php">
        <input type="hidden" name="action" value="logout">
        <button type="submit" class="logout-btn">Logout</button>
    </form>
</header>
<div class="admin-container">
    <button popovertarget="add-product-popover" class="add-product-btn">Add Product</button>
    <div popover id="add-product-popover" class="add-product-popover-container">
        <button popovertarget="add-product-popover" popovertargetaction="hide" class="add-product-popover-close-btn">&times;</button>
        <h3>Add Product</h3>
        <form method="POST" enctype="multipart/form-data" action="admin-products.php" class="add-product-form">
            <label>Product Name:</label>
            <input type="text" name="name" required>
            <label>Price:</label>
            <input type="number" name="price" step="0.01" required>
            <label>Quantity:</label>
            <input type="number" name="quantity" min="0" required>
            <label>Description:</label>
            <textarea name="description" rows="4" required></textarea>
            <label>Product Image:</label>
            <input type="file" name="product_img" accept="image/*" required>
            <button type="submit" name="add_product">Add Product</button>
        </form>
    </div>
    <h3>Products</h3>
    <?php while ($row = $result->fetch_assoc()): 
        $imageData = base64_encode($row['product_img']);
        $imageSrc = "data:image/jpeg;base64," . $imageData;
    ?>
        <div class="product-row">
            <img src="<?php echo $imageSrc; ?>">
            <div class="product-info">
                <h4><?php echo $row['name']; ?></h4>
                <p>Price: ₱<?php echo number_format($row['price'],2); ?></p>
                <p>Stock: <strong><?php echo $row['quantity']; ?></strong></p>
                <p>Description: <?php echo $row['description']; ?></p>
            </div>
            <div class="product-actions">
                <button class="edit-btn"
                        data-id="<?php echo $row['id']; ?>"
                        data-name="<?php echo htmlspecialchars($row['name'],ENT_QUOTES); ?>"
                        data-price="<?php echo $row['price']; ?>"
                        data-description="<?php echo htmlspecialchars($row['description'],ENT_QUOTES); ?>"
                        data-quantity="<?php echo $row['quantity']; ?>"
                        popovertarget="edit-product-popover">Edit</button>
                <form method="GET" action="admin-products.php">
                    <input type="hidden" name="delete" value="<?php echo $row['id']; ?>">
                    <button type="submit" class="delete-btn">Delete</button>
                </form>
            </div>
        </div>
    <?php endwhile; ?>
    <div popover id="edit-product-popover" class="edit-product-popover-container">
        <button popovertarget="edit-product-popover" popovertargetaction="hide" class="edit-product-popover-close-btn">&times;</button>
        <h3>Edit Product</h3>
        <form method="POST" enctype="multipart/form-data" action="admin-products.php" class="edit-product-form">
            <input type="hidden" name="product_id" id="edit-product-id">
            <label>Product Name:</label>
            <input type="text" name="name" id="edit-name">
            <label>Price:</label>
            <input type="number" name="price" step="0.01" id="edit-price">
            <label>Quantity:</label>
            <input type="number" name="quantity" min="0" id="edit-quantity">
            <label>Description:</label>
            <textarea name="description" rows="4" id="edit-description"></textarea>
            <label>Product Image:</label>
            <input type="file" name="product_img" accept="image/*">
            <button type="submit" name="edit_product">Update Product</button>
        </form>
    </div>
</div>
<script src="scripts/admin.js"></script>
</body>
</html>