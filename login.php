<?php
require_once 'config.php';
session_start();

// Send users who are already logged in to the correct page for their role.
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'librarian') {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: my-account.php");
    }
    exit();
}

 $error = "";
// Check the login details after the user submits the form.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        $username = $conn->real_escape_string($username);
        $sql = "SELECT * FROM users WHERE username = '$username'";
        $result = $conn->query($sql);

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            if ($row['status'] === 'inactive') {
                $error = "Your account has been deactivated. Contact admin.";
            // Compare the entered password with the hashed password in the database.
            } elseif (password_verify($password, $row['password'])) {
                // Store small pieces of user information in the session for other pages.
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['role'] = $row['role'];
                $_SESSION['name'] = $row['name'];
                $_SESSION['member_id'] = $row['member_id'];

                if ($row['role'] === 'admin' || $row['role'] === 'librarian') {
                    header("Location: admin/dashboard.php");
                } else {
                    header("Location: my-account.php");
                }
                exit();
            } else {
                $error = "Incorrect password.";
            }
        } else {
            $error = "Email not found.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>City Library - Login</title>
    <link rel="stylesheet" href="style.css?v=<?php echo filemtime('style.css'); ?>">
</head>
<body>

<nav class="navbar">
    <div class="nav-container">
        <a href="index.php" class="nav-logo">City Library</a>
        <ul class="nav-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="library.php">Library</a></li>
        </ul>
        <div class="nav-actions">
            <a href="register.php">Register</a>
        </div>
    </div>
</nav>

<section class="auth-section">
    <div class="auth-box">
        <h2>Login to Your Account</h2>
        <p class="auth-subtitle">Enter your email and password</p>

        <?php if ($error): ?>
            <div class="auth-alert auth-error"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['registered'])): ?>
            <div class="auth-alert auth-success">Registration successful! Please login.</div>
        <?php endif; ?>
        <?php if (isset($_GET['logout'])): ?>
            <div class="auth-alert auth-success">You have been logged out.</div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="auth-field">
                <label for="username">Email Address</label>
                <input type="email" name="username" id="username" placeholder="Enter your email" required>
            </div>
            <div class="auth-field">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" placeholder="Enter your password" required>
            </div>
            <button type="submit" class="btn-auth btn-auth-primary">Login</button>
        </form>

        <p class="auth-footer-text">Don't have an account? <a href="register.php">Register here</a></p>
    </div>
</section>

<footer class="footer">
    <div class="footer-container">
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?>  City Library .</p>
        </div>
    </div>
</footer>

</body>
</html>
