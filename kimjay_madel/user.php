<?php
// Initialize variables
$action = null;
$isLoggedIn = isset($_SESSION['user_id']);
$userData = null;

// Fetch user data if logged in
if ($isLoggedIn) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $userData = $stmt->get_result()->fetch_assoc();
}

// Handle login, signup, logout, and edit actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // Signup
    if ($action === 'signup' && isset($_POST['username'], $_POST['email'], $_POST['password'])) {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

        $checkUser = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $checkUser->bind_param("s", $email);
        $checkUser->execute();
        $result = $checkUser->get_result();

        if ($result->num_rows > 0) {
            echo "<script>alert('User already exists'); window.location.href='index.php';</script>";
        } else {
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')");
            $stmt->bind_param("sss", $username, $email, $password);

            if ($stmt->execute()) {
                echo "<script>alert('User registered successfully'); window.location.href='index.php';</script>";
            } else {
                echo "<script>alert('Failed to register user'); window.location.href='index.php';</script>";
            }
        }
    }
    // Login
    elseif ($action === 'login' && isset($_POST['username'], $_POST['password'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];

        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'] ?? 'user';
                $_SESSION['profile_img'] = $user['profile_img'] ?? null;

                // Redirect based on role
                if (strtolower($user['role']) === 'admin' || strtolower($user['username']) === 'admin') {
                    header("Location: admin-statistics.php");
                    exit();
                } else {
                    header("Location: index.php");
                    exit();
                }
            } else {
                echo "<script>alert('Invalid password'); window.location.href='index.php';</script>";
            }
        } else {
            echo "<script>alert('User not found'); window.location.href='index.php';</script>";
        }
    }
    // Logout
    elseif ($action === 'logout') {
        session_unset();
        session_destroy();
        header("Location: index.php");
        exit();
    }

    elseif ($action === 'edit' && $isLoggedIn) {
        $newUsername = $_POST['username'];
        $newEmail = $_POST['email'];
        $newPassword = $_POST['password'];
        $newAddress = $_POST['address'] ?? null;
        $newBirthday = $_POST['birthday'] ?? null;

        $profileImg = $userData['profile_img'] ?? null;
        if (isset($_FILES['profile_img']) && $_FILES['profile_img']['error'] === UPLOAD_ERR_OK) {
            $imgData = file_get_contents($_FILES['profile_img']['tmp_name']);
            $profileImg = $imgData;
        }

        if (!empty($newPassword)) {
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("UPDATE users SET username=?, email=?, password=?, address=?, birthday=?, profile_img=? WHERE id=?");
            $stmt->bind_param("ssssssi", $newUsername, $newEmail, $hashedPassword, $newAddress, $newBirthday, $profileImg, $_SESSION['user_id']);
        } else {
            $stmt = $conn->prepare("UPDATE users SET username=?, email=?, address=?, birthday=?, profile_img=? WHERE id=?");
            $stmt->bind_param("sssssi", $newUsername, $newEmail, $newAddress, $newBirthday, $profileImg, $_SESSION['user_id']);
        }

        if ($stmt->execute()) {
            $_SESSION['username'] = $newUsername;
            $_SESSION['profile_img'] = $profileImg;
            echo "<script>alert('Profile updated successfully'); window.location.href='index.php';</script>";
            exit();
        } else {
            echo "<script>alert('Failed to update profile'); window.location.href='index.php';</script>";
        }
    }
}
?>