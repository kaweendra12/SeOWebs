<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'reader') {
    header("Location: login.php");
    exit();
}

// The member ID in the session tells us whose account to display.
 $member_id = $_SESSION['member_id'];
 $member = $conn->query("SELECT * FROM members WHERE member_id = '$member_id'")->fetch_assoc();

 // Get books that this member still needs to return.
 $issued = $conn->query("
    SELECT b.*, br.issue_date, br.due_date
    FROM borrowings br
    JOIN books b ON br.book_id = b.book_id
    WHERE br.member_id = '$member_id' AND br.status = 'Issued'
    ORDER BY br.due_date
");

 // Get every borrowing record for the member's history table.
 $history = $conn->query("
    SELECT b.*, br.issue_date, br.due_date, br.return_date, br.status
    FROM borrowings br
    JOIN books b ON br.book_id = b.book_id
    WHERE br.member_id = '$member_id'
    ORDER BY br.id DESC
");

 $totalBorrowed = $history->num_rows;
 $currentlyHolding = $issued->num_rows;

 $profileSuccess = "";
 $profileError = "";
// Update profile details only when the Update Profile button was clicked.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $address = trim($_POST['address']);
    $telephone = trim($_POST['telephone']);
    $email = trim($_POST['email']);

    if (empty($address) || empty($telephone) || empty($email)) {
        $profileError = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $profileError = "Invalid email.";
    } else {
        $address = $conn->real_escape_string($address);
        $telephone = $conn->real_escape_string($telephone);
        $email = $conn->real_escape_string($email);
        $sql = "UPDATE members SET address='$address', telephone='$telephone', email='$email'
                WHERE member_id='$member_id'";
        $conn->query($sql);
        // Keep the login email in the users table the same as the member email.
        $name = $member['name'];
        $name = $conn->real_escape_string($name);
        $sql = "UPDATE users SET username='$email', name='$name' WHERE member_id='$member_id'";
        $conn->query($sql);
        $_SESSION['username'] = $email;
        $member = $conn->query("SELECT * FROM members WHERE member_id = '$member_id'")->fetch_assoc();
        $profileSuccess = "Profile updated!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>City Library - My Account</title>
    <link rel="stylesheet" href="style.css?v=<?php echo filemtime('style.css'); ?>">
</head>
<body>

<nav class="navbar">
    <div class="nav-container">
        <a href="index.php" class="nav-logo">City Library</a>
        <ul class="nav-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="library.php">Library</a></li>
            <li><a href="my-account.php" class="active">My Account</a></li>
        </ul>
        <div class="nav-actions">
            <span>Hi <?php echo htmlspecialchars($_SESSION['name']); ?></span>
            <a href="logout.php">Logout</a>
        </div>
    </div>
</nav>

<section class="account-section">
    <div class="account-container">
        <h2 class="account-title">My Account</h2>

        <div class="account-grid">
            <div class="account-card">
                <h3>Member Information</h3>
                <div class="info-row"><span>Member ID:</span> <strong><?php echo $member['member_id']; ?></strong></div>
                <div class="info-row"><span>Name:</span> <strong><?php echo htmlspecialchars($member['name']); ?></strong></div>
                <div class="info-row"><span>Email:</span> <?php echo htmlspecialchars($member['email']); ?></div>
                <div class="info-row"><span>Phone:</span> <?php echo htmlspecialchars($member['telephone']); ?></div>
                <div class="info-row"><span>Address:</span> <?php echo htmlspecialchars($member['address']); ?></div>
                <div class="info-row"><span>Registered:</span> <?php echo $member['registration_date']; ?></div>
            </div>
            <div class="account-card account-stats-card">
                <h3>My Statistics</h3>
                <div class="my-stat"><span class="my-stat-num"><?php echo $totalBorrowed; ?></span><span class="my-stat-label">Total Borrowed</span></div>
                <div class="my-stat"><span class="my-stat-num"><?php echo $currentlyHolding; ?></span><span class="my-stat-label">Currently Holding</span></div>
            </div>
        </div>

        <div class="account-card" style="margin-top:20px;">
            <h3>Update Profile</h3>
            <?php if ($profileSuccess): ?><div class="auth-alert auth-success"><?php echo $profileSuccess; ?></div><?php endif; ?>
            <?php if ($profileError): ?><div class="auth-alert auth-error"><?php echo $profileError; ?></div><?php endif; ?>
            <form method="POST" action="" class="profile-form">
                <input type="hidden" name="update_profile" value="1">
                <div class="profile-row">
                    <div class="profile-field"><label>Address</label><textarea name="address" rows="2" required><?php echo htmlspecialchars($member['address']); ?></textarea></div>
                    <div class="profile-field"><label>Telephone</label><input type="text" name="telephone" value="<?php echo htmlspecialchars($member['telephone']); ?>" required></div>
                </div>
                <div class="profile-field" style="max-width:400px;"><label>Email</label><input type="email" name="email" value="<?php echo htmlspecialchars($member['email']); ?>" required></div>
                <button type="submit" class="btn-auth btn-auth-primary" style="margin-top:10px;">Update Profile</button>
            </form>
        </div>

        <div class="account-card" style="margin-top:20px;">
            <h3>Books I Currently Have</h3>
            <?php if ($issued->num_rows > 0): ?>
            <div class="account-table-wrap">
                <table class="account-table">
                    <thead><tr><th>Book ID</th><th>Book</th><th>Author</th><th>Issue Date</th><th>Due Date</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php while ($r = $issued->fetch_assoc()):
                            $overdue = $r['due_date'] < date('Y-m-d');
                        ?>
                        <tr class="<?php echo $overdue ? 'row-overdue' : ''; ?>">
                            <td><?php echo $r['book_id']; ?></td>
                            <td><?php echo htmlspecialchars($r['book_name']); ?></td>
                            <td><?php echo htmlspecialchars($r['author']); ?></td>
                            <td><?php echo $r['issue_date']; ?></td>
                            <td><?php echo $r['due_date']; ?></td>
                            <td><?php echo $overdue ? '<span class="badge-sm badge-danger">Overdue</span>' : '<span class="badge-sm badge-warning">Issued</span>'; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <p class="no-data-text">You don't have any books currently issued.</p>
            <?php endif; ?>
        </div>

        <div class="account-card" style="margin-top:20px;">
            <h3>Borrowing History</h3>
            <?php if ($history->num_rows > 0): ?>
            <div class="account-table-wrap">
                <table class="account-table">
                    <thead><tr><th>Book ID</th><th>Book</th><th>Author</th><th>Issued</th><th>Due</th><th>Returned</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php while ($r = $history->fetch_assoc()):
                            $cls = $r['status'] === 'Late' ? 'badge-danger' : ($r['status'] === 'Returned' ? 'badge-success' : 'badge-warning');
                            if ($r['status'] === 'Issued' && $r['due_date'] < date('Y-m-d')) $cls = 'badge-danger';
                        ?>
                        <tr>
                            <td><?php echo $r['book_id']; ?></td>
                            <td><?php echo htmlspecialchars($r['book_name']); ?></td>
                            <td><?php echo htmlspecialchars($r['author']); ?></td>
                            <td><?php echo $r['issue_date']; ?></td>
                            <td><?php echo $r['due_date']; ?></td>
                            <td><?php echo $r['return_date'] ?: '-'; ?></td>
                            <td><span class="badge-sm <?php echo $cls; ?>"><?php echo $r['status']; ?></span></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <p class="no-data-text">No borrowing history yet.</p>
            <?php endif; ?>
        </div>
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
