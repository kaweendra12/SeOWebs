<?php
require_once 'config.php';
session_start();

// A logged-in user does not need to register again.
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

 $error = "";
// Run this part only when the registration form is submitted.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];
    $name = trim($_POST['name']);
    $address = trim($_POST['address']);
    $telephone = trim($_POST['telephone']);
    $email = trim($_POST['email']);

    // Validate the form before saving anything to the database.
    if (empty($password) || empty($confirm) || empty($name) || empty($address) || empty($telephone) || empty($email)) {
        $error = "All fields are required.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        $name = $conn->real_escape_string($name);
        $address = $conn->real_escape_string($address);
        $telephone = $conn->real_escape_string($telephone);
        $email = $conn->real_escape_string($email);

        // The same email must not be used by two accounts.
        $userCheck = $conn->query("SELECT id FROM users WHERE username = '$email'");
        if ($userCheck->num_rows > 0) {
            $error = "This email is already registered.";
        } else {
            // Also check the members table because it stores contact details.
            $memberCheck = $conn->query("SELECT id FROM members WHERE email = '$email'");
            if ($memberCheck->num_rows > 0) {
                $error = "This email is already registered.";
            } else {
                // Create the next member ID: M001, M002, M003, and so on.
                $last = $conn->query("SELECT member_id FROM members ORDER BY id DESC LIMIT 1")->fetch_assoc();
                if ($last && preg_match('/M(\d+)/', $last['member_id'], $m)) {
                    $nextNum = intval($m[1]) + 1;
                    $member_id = 'M' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
                } else {
                    $member_id = 'M001';
                }

                $regDate = date('Y-m-d');

                // Save personal/member details first.
                $sql = "INSERT INTO members (member_id, name, address, telephone, email, registration_date)
                        VALUES ('$member_id', '$name', '$address', '$telephone', '$email', '$regDate')";
                $conn->query($sql);

                // password_hash protects the password; never save a plain-text password.
                // The email is also used as the login username.
                $hashedPass = password_hash($password, PASSWORD_DEFAULT);
                $role = 'reader';
                $hashedPass = $conn->real_escape_string($hashedPass);
                $sql = "INSERT INTO users (username, password, role, name, member_id)
                        VALUES ('$email', '$hashedPass', '$role', '$name', '$member_id')";
                $conn->query($sql);

                header("Location: login.php?registered=1");
                exit();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>City Library - Register</title>
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
            <a href="login.php">Login</a>
        </div>
    </div>
</nav>

<section class="auth-section">
    <div class="auth-box auth-box-wide">
        <h2>Create Your Account</h2>
        <p class="auth-subtitle">Register as a library member to borrow books</p>

        <?php if ($error): ?>
            <div class="auth-alert auth-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="auth-row">
                <div class="auth-field">
                    <label>Full Name</label>
                    <input type="text" name="name" placeholder="Your full name" required>
                </div>
                <div class="auth-field">
                    <label>Email Address</label>
                    <input type="email" name="email" placeholder="your@email.com" required>
                </div>
            </div>
            <div class="auth-row">
                <div class="auth-field">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="Min 6 characters" required minlength="6">
                </div>
                <div class="auth-field">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" placeholder="Re-enter password" required>
                </div>
            </div>
            <div class="auth-field">
                <label>Telephone</label>
                <input type="text" name="telephone" placeholder="e.g. 0771234567" required>
            </div>
            <div class="auth-field">
                <label>Address</label>
                <textarea name="address" rows="2" placeholder="Your full address" required></textarea>
            </div>
            <button type="submit" class="btn-auth btn-auth-primary">Create Account</button>
        </form>

        <p class="auth-footer-text">Already have an account? <a href="login.php">Login here</a></p>
    </div>
</section>

<footer class="footer">
    <div class="footer-container">
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> City Library.</p>
        </div>
    </div>
</footer>

</body>
</html>
