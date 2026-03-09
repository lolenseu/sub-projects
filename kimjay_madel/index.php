<?php

// Start the session
session_start();

// Database connection
include 'connection.php';

// Fetch products
include 'products.php';

// Fetch User
include 'user.php';

// Fetch Cart items
include 'cart.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        Kimjay & Madel - 
        <?php
            if (isset($userData['username']) && !empty($userData['username'])) {
                echo htmlspecialchars($userData['username']);
            } else {
                echo "Home";
            }
        ?>
    </title>

    <link href='https://fonts.googleapis.com/css?family=Poppins' rel='stylesheet'>

    <link href="styles/style.css" rel="stylesheet">
    <link href="styles/navbar.css" rel="stylesheet">
    <link href="styles/popover.css" rel="stylesheet">
    <link href="styles/containers.css" rel="stylesheet">
    <link href="styles/mobile.css" rel="stylesheet">
    <link href="styles/emmabot.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <header class="navbar">
            <div class="logo-container">
                <div class="logo">Kimjay&Madel</div>
                <button class="hamburger-menu" id="hamburger-menu">☰</button>
            </div>

            <div class="search-container">
                <input type="text" id="searchInput" placeholder="Enter product name..." oninput="fetchSuggestions()">
                <div id="suggestions" class="suggestions-container"></div>
                <button onclick="scrollToProduct()">Search</button>
            </div>

            <ul class="nav-links" id="nav-links">
                <li><a href="#" id="home">Home</a></li>
                <li><a href="#products-section">Products</a></li>
                <li><a href="#services-section" id="services">Services</a></li>
                <li><a href="#about-section" id="about">About</a></li>
                <li><a href="#faq-section" id="faq">FAQ</a></li>
                <li><a href="#contact-section" id="contact">Contact</a></li>
            </ul>

            <div class="top-container">
                <div class="profile-container">
                    <?php if ($isLoggedIn): ?>
                    <button popovertarget="profilePopover" id="profileButton" class="profile-icon">
                        <?php
                            $profileImg = 'img/nopic.jpg';
                            if (!empty($userData['profile_img'])) {
                                $profileImg = 'data:image/jpeg;base64,' . base64_encode($userData['profile_img']);
                            }
                        ?>
                        <img src="<?php echo htmlspecialchars($profileImg); ?>" alt="Profile" class="profile-img">
                    </button>
                    <?php else: ?>
                        <button popovertarget="userPopover" id="userButton" class="user-icon">👤</button>
                    <?php endif; ?>
                </div>
                
                <!-- Orders Button - Only show when logged in -->
                <?php if ($isLoggedIn): ?>
                <div class="orders-container">
                    <button popovertarget="buyOrdersPopover" id="ordersButton" class="orders-icon" title="My Orders">
                        📋 <span id="ordersCount" class="orders-badge">0</span>
                    </button>
                </div>
                <?php endif; ?>
                
                <div class="cart-container">
                    <button popovertarget="cartPopover" id="cartButton" class="cart-icon">
                        🛒 <span id="cartCount">0</span>
                    </button>
                </div>
            </div>
        </header>

        <!-- Header Popovers -->
        <div popover id="userPopover" class="user-popover-container">
            <button popovertarget="userPopover" popovertargetaction="hide" class="user-close-btn" aria-label="Close">&times;</button>
            <h3>User Options</h3>
            <button class="user-btn" popovertarget="loginPopover">Login</button>
            <button class="user-btn" popovertarget="signupPopover">Signup</button>
        </div>

        <div popover id="loginPopover" class="login-popover-container">
            <div class="login-modal-content">
                <button popovertarget="loginPopover" popovertargetaction="hide" class="login-close-btn" aria-label="Close">&times;</button>
                <h3>Login</h3>
                <form method="POST" action="index.php">
                    <input type="hidden" name="action" value="login">
                    <label for="loginUsername">Username</label>
                    <input type="text" id="loginUsername" name="username" placeholder="Enter your username" required>
                    <label for="loginPassword">Password</label>
                    <input type="password" id="loginPassword" name="password" placeholder="Enter your password" required>
                    <button type="submit" class="login-btn">Login</button>
                </form>
            </div>
        </div>

        <div popover id="signupPopover" class="signup-popover-container">
            <div class="signup-modal-content">
                <button popovertarget="signupPopover" popovertargetaction="hide" class="signup-close-btn" aria-label="Close">&times;</button>
                <h3>Signup</h3>
                <form method="POST" action="index.php">
                    <input type="hidden" name="action" value="signup">
                    <label for="signupUsername">Username</label>
                    <input type="text" id="signupUsername" name="username" placeholder="Enter your username" required>
                    <label for="signupEmail">Email</label>
                    <input type="email" id="signupEmail" name="email" placeholder="Enter your email" required>
                    <label for="signupPassword">Password</label>
                    <input type="password" id="signupPassword" name="password" placeholder="Enter your password" required>
                    <button type="submit" class="signup-btn">Signup</button>
                </form>
            </div>
        </div>

        <div popover id="profilePopover" class="profile-popover-container">
                <button popovertarget="profilePopover" popovertargetaction="hide" class="profile-close-btn" aria-label="Close">&times;</button>
                <h3>Profile</h3>
                <?php if ($isLoggedIn && $userData): ?>
                    <div class="profile-info">
                        <?php
                            $profileImg = 'img/nopic.jpg';
                            if (!empty($userData['profile_img'])) {
                                $profileImg = 'data:image/jpeg;base64,' . base64_encode($userData['profile_img']);
                            }
                        ?>
                        <img src="<?php echo htmlspecialchars($profileImg); ?>" alt="Profile" class="profile-img">
                        <p><strong>Username:</strong></p>
                        <p><?php echo htmlspecialchars($userData['username']); ?></p>
                        <p><strong>Email:</strong></p>
                        <p><?php echo htmlspecialchars($userData['email']); ?></p>
                        <p><strong>Birthday:</strong></p>
                        <p><?php echo htmlspecialchars($userData['birthday'] ?? ''); ?></p>
                        <p><strong>Address:</strong></p>
                        <p><?php echo htmlspecialchars($userData['address'] ?? ''); ?></p>
                    </div>
                    <div>
                        <button popovertarget="buyOrdersPopover" class="profile-btn">My Orders</button>
                        <button popovertarget="editPopover" class="profile-btn">Edit Profile</button>
                        <form id="logoutForm" method="POST" action="index.php">
                            <input type="hidden" name="action" value="logout">
                            <button type="submit" class="profile-btn logout-confirm">Logout</button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        
        <!-- Main Orders Popover (Now directly opened by the orders icon) -->
        <div popover id="buyOrdersPopover" class="buyorder-popover-container">
            <button popovertarget="buyOrdersPopover" popovertargetaction="hide" class="buyorder-close-btn" aria-label="Close">&times;</button>
            <h3>My Orders</h3>
            <div class="orders-list">
                <?php
                if ($isLoggedIn) {
                    // Fetch orders for this user
                    $userId = $_SESSION['user_id'];
                    $ordersSql = "
                        SELECT ps.*, p.name AS product_name, p.price, p.product_img,
                               pc.comment, pc.comment_time, a.username as admin_username
                        FROM product_status ps
                        JOIN products p ON ps.product_id = p.id
                        LEFT JOIN product_comments pc ON ps.id = pc.order_id
                        LEFT JOIN users a ON pc.admin_id = a.id
                        WHERE ps.user_id = ?
                        ORDER BY ps.order_time DESC
                    ";
                    $stmt = $conn->prepare($ordersSql);
                    $stmt->bind_param("i", $userId);
                    $stmt->execute();
                    $ordersResult = $stmt->get_result();
                    if ($ordersResult->num_rows > 0) {
                        while ($order = $ordersResult->fetch_assoc()) {
                            $imgSrc = 'img/nopic.jpg';
                            if (!empty($order['product_img'])) {
                                $imgSrc = 'data:image/jpeg;base64,' . base64_encode($order['product_img']);
                            }
                            $statusClass = htmlspecialchars($order['status']);
                            echo '<div class="order-item">';
                            echo '<img src="'.htmlspecialchars($imgSrc).'" alt="Product" class="order-item-img">';
                            echo '<div class="order-item-details">';
                            echo '<div class="order-item-name">'.htmlspecialchars($order['product_name']).'</div>';
                            echo '<div class="order-item-qty">Quantity: '.intval($order['quantity']).'</div>';
                            echo '<div class="order-item-price">₱'.number_format($order['price'],2).'</div>';
                            echo '<div class="order-item-status">';
                            echo '<span class="status-badge '.$statusClass.'">'.ucfirst($statusClass).'</span>';
                            echo '</div>';
                            
                            // Display comment if exists
                            if (!empty($order['comment'])) {
                                echo '<div class="order-item-comment">';
                                echo '<div class="comment-header">';
                                echo '<span class="comment-author">Admin</span>';
                                echo '<span class="comment-time">'.date('M d, Y', strtotime($order['comment_time'])).'</span>';
                                echo '</div>';
                                echo '<p class="comment-text">'.htmlspecialchars($order['comment']).'</p>';
                                echo '</div>';
                            }
                            
                            echo '</div>'; // Close order-item-details
                            echo '</div>'; // Close order-item
                        }
                    } else {
                        echo '<p class="no-orders-message">You haven\'t placed any orders yet.</p>';
                    }
                    $stmt->close();
                } else {
                    echo '<p class="no-orders-message">Please log in to view your orders.</p>';
                }
                ?>
            </div>
        </div>

        <div popover id="editPopover" class="edit-popover-container">
            <div class="edit-modal-content">
                <button popovertarget="editPopover" popovertargetaction="hide" class="edit-close-btn" aria-label="Close">&times;</button>
                <h3>Edit Profile</h3>
                <form method="POST" action="index.php" enctype="multipart/form-data" onsubmit="return confirmProfileUpdate();">
                    <input type="hidden" name="action" value="edit">
                    <label for="editUsername">Username</label>
                    <input type="text" id="editUsername" name="username" value="<?php echo htmlspecialchars($userData['username']); ?>">

                    <label for="editEmail">Email</label>
                    <input type="email" id="editEmail" name="email" value="<?php echo htmlspecialchars($userData['email']); ?>">
                    
                    <label for="editPassword">New Password</label>
                    <input type="password" id="editPassword" name="password" placeholder="Leave blank to keep current">
                    
                    <label for="editAddress">Address</label>
                    <input type="text" id="editAddress" name="address" value="<?php echo htmlspecialchars($userData['address'] ?? ''); ?>">
                    
                    <label for="editBirthday">Birthday</label>
                    <input type="date" id="editBirthday" name="birthday" value="<?php echo htmlspecialchars($userData['birthday'] ?? ''); ?>">
                    
                    <label for="editImage">Profile Image</label>
                    <input type="file" id="editImage" name="profile_img" accept="image/*">
                    
                    <button type="submit" class="edit-btn">Save Changes</button>
                </form>
            </div>
        </div>

        <div popover id="cartPopover" class="cart-popover-container">
            <button popovertarget="cartPopover" popovertargetaction="hide" class="cart-close-btn" aria-label="Close">&times;</button>
            <h3>Shopping Cart</h3>
            <ul id="cartItems"></ul>
            <p id="cartTotal">Total: &#8369;0.00</p>
            <button onclick="clearCart()" class="cart-button">Clear Cart</button>
            <button onclick="purchaseCart()" class="cart-button">Purchase</button>
        </div>

        <!-- Main Content -->
        <div class="first-container">
            <div class="slideshow-container">
                <div class="mySlides fade">
                    <img src="img/product1.png">
                </div>
                <div class="mySlides fade">
                    <img src="img/product2.png">
                </div>
                <div class="mySlides fade">
                    <img src="img/product3.png">
                </div>
                <div class="mySlides fade">
                    <img src="img/product4.png">
                </div>
                <div class="mySlides fade">
                    <img src="img/product5.png">
                </div>
            </div>
            <br>
            <div class="dots-container">
                <span class="dot" onclick="currentSlide(1)"></span> 
                <span class="dot" onclick="currentSlide(2)"></span> 
                <span class="dot" onclick="currentSlide(3)"></span> 
                <span class="dot" onclick="currentSlide(4)"></span>
                <span class="dot" onclick="currentSlide(5)"></span> 
            </div>
        </div>

        <div class="second-container" id="products-section">
            <h2 class="section-title">Our Products</h2>
            <div class="product-grid">
                <?php
                if ($result->num_rows > 0) {
                    $i = 0;
                    while ($row = $result->fetch_assoc()):
                        $imageData = base64_encode($row['product_img']);
                        $imageSrc  = "data:product_img/jpeg;base64," . $imageData;
                        $stock     = (int)$row['quantity'];
                        $i++; // just for counting if you need it later
                ?>
                    <div class="product"
                        data-id="<?php echo $row['id']; ?>"
                        data-name="<?php echo $row['name']; ?>"
                        data-price="<?php echo $row['price']; ?>"
                        data-quantity="<?php echo $stock; ?>"
                        data-description="<?php echo htmlspecialchars($row['description']); ?>">

                        <img src="<?php echo $imageSrc; ?>" alt="<?php echo $row['name']; ?>" class="product-img">

                        <h3><?php echo $row['name']; ?></h3>
                        <p>&#8369;<?php echo number_format($row['price'], 2); ?></p>
                        <p class="stock">Stock: <strong><?php echo $stock; ?></strong></p>
                    </div>
                <?php
                    endwhile;
                } else {
                    echo '<p>No products available.</p>';
                }
                ?>
            </div>
        </div>

        <!-- Modal Product -->
        <div id="productModal" class="product-modal">
            <div class="product-modal-content" id="productModal">
                <span class="product-close-btn" onclick="closeModal()">&times;</span>

                <img id="modalImage" src="" alt="Product Image">
                <h3 id="modalTitle">Product Name</h3>
                <h5 id="modalDescription">Product Description</h5>
                <p class="stock">Stock: <strong id="modalStock">0</strong></p>
                <p id="modalPrice">&#8369;0.00</p>

                <label for="modalQty">Quantity to add:</label>
                <div class="quantity-controls" style="margin-top:8px;">
                    <button type="button" class="qty-minus">-</button>
                    <input type="number" id="modalQty"
                        name="qty"
                        min="1" value="1"
                        class="qty-input"
                        style="width:60px;">
                    <button type="button" class="qty-plus">+</button>
                </div>

                <button class="add-to-cart-btn"
                        onclick="addToCart()">Add to Cart</button>
            </div>
        </div>

        <div class="third-container" id="services-section">
            <h2 class="section-title">Our Services</h2>
            <div class="services-grid">
                <div class="service-card">
                    <i class="service-icon"></i>
                    <h3>Fresh Meat Selection</h3>
                    <p>We provide high-quality, fresh meat that is carefully prepared for your daily cooking needs.</p>
                </div>
                <div class="service-card">
                    <i class="service-icon"></i>
                    <h3>Fast Delivery</h3>
                    <p>We offer reliable delivery to make sure your meat arrives fresh and on time.</p>
                </div>
                <div class="service-card">
                    <i class="service-icon"></i>
                    <h3>Customer Support</h3>
                    <p>Our team is ready to assist you with orders, questions, or special requests.</p>
                </div>
                <div class="service-card">
                    <i class="service-icon"></i>
                    <h3>Custom Meat Cuts</h3>
                    <p>You may request preferred cuts, and we will prepare them based on your needs.</p>
                </div>
                <div class="service-card">
                    <i class="service-icon"></i>
                    <h3>Secure Ordering</h3>
                    <p>Our online ordering process is simple and safe for your convenience.</p>
                </div>
                <div class="service-card">
                    <i class="service-icon"></i>
                    <h3>Bulk Orders Available</h3>
                    <p>We accept bulk orders for households, events, or small businesses.</p>
                </div>
            </div>
        </div>

        <footer>
            <div class="footer-content">
                <div class="footer-section about" id="about-section">
                    <h2>About Us – Kimjay & Madel Meat Stall</h2>
                    <p>Welcome to Kimjay & Madel Meat Stall! We are committed to providing you with fresh, high-quality meats through a convenient and reliable online shopping experience.</p>
                    <p>Our passion is to deliver clean, safe, and premium cuts that you and your family can trust. We carefully select our products to ensure freshness, quality, and great taste — perfect for your everyday meals or special occasions.</p>
                    <p>At Kimjay & Madel Meat Stall, we believe buying meat should be easy and hassle-free. That’s why we offer a simple online ordering system, fair prices, and dependable service right to your doorstep.</p>
                    <p>Thank you for choosing Kimjay & Madel Meat Stall. We look forward to serving you and becoming your trusted source for fresh meat.</p>
                </div>
                <div class="footer-section faq" id="faq-section">
                    <h2>FAQ</h2>
                    <ul>
                        <li><a href="#faq1">Are your meats fresh?</a></li>
                        <li><a href="#faq2">Where do you source your meat?</a></li>
                        <li><a href="#faq3">Do you offer same-day delivery?</a></li>
                        <li><a href="#faq4">How can I place an order?</a></li>
                    </ul>
                </div>
                <div class="footer-section contact" id="contact-section">
                    <h2>Contact Us</h2>
                    <p>Email: support@kimjaymadel.com</p>
                    <p>Phone: +9673280015</p>
                    <p>Address: Bacnotan,La Union</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>© 2026 Kimjay&Madel. All rights reserved.</p>
            </div>
        </footer>

        <!-- Emma bot -->
        <div class="chatbot-button-container">
            <button popovertarget="chat-container" class="chatbot-button ani">AskEmma</button>
        </div>

        <div popover id="chat-container" class="chatbot-chat-container">
            <button popovertarget="chat-container" popovertargetaction="hide" class="chat-close-btn" aria-label="Close">&times;</button>
            <h2 class="emmatag">EmmaAI you're Assistant</h2>
            <div class="message-box" id="messagebox"></div>
            <input class="user-input" type="text" id="userinput" placeholder="Type your message here...">
            <button class="user-button" onclick="sendMessage()">Send</button>
         </div>

        <!-- Back to Top Button -->
        <button class="back-to-top" id="back-to-top" onclick="scrollToTop()">Back to Top</button>
    </div>

    <script>
        var isLoggedIn = <?php echo json_encode($isLoggedIn); ?>;
    </script>
    <script src="scripts/script.js"></script>
    <script src="scripts/navbar.js"></script>
    <script src="scripts/popover.js"></script>
    <script src="scripts/emmabot.js"></script>
    <script>
        // Function to update orders count
        function updateOrdersCount() {
            <?php if ($isLoggedIn): ?>
            fetch('get_orders_count.php')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('ordersCount').textContent = data.count;
                });
            <?php endif; ?>
        }

        // Update orders count on page load and periodically
        document.addEventListener('DOMContentLoaded', function() {
            updateOrdersCount();
            // Update every 30 seconds
            setInterval(updateOrdersCount, 30000);
        });
    </script>
</body>
</html>