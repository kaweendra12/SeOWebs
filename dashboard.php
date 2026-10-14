<?php
$pageTitle = 'Dashboard';
$activePage = 'dashboard';
require_once '../config.php';
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: ../login.php'); exit(); }
if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'librarian') { header('Location: ../my-account.php'); exit(); }

// Count records to display the dashboard summary cards.
$totalBooks = $conn->query("SELECT COUNT(*) AS total FROM books")->fetch_assoc()['total'];
$totalMembers = $conn->query("SELECT COUNT(*) AS total FROM members")->fetch_assoc()['total'];
$issued = $conn->query("SELECT COUNT(*) AS total FROM borrowings WHERE status = 'Issued'")->fetch_assoc()['total'];
$overdue = $conn->query("SELECT COUNT(*) AS total FROM borrowings WHERE status = 'Issued' AND due_date < CURDATE()")->fetch_assoc()['total'];
$returned = $conn->query("SELECT COUNT(*) AS total FROM borrowings WHERE status IN ('Returned', 'Late')")->fetch_assoc()['total'];
$totalUsers = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'];

// Join borrowing, book, and member data for the latest activity table.
$recent = $conn->query(
    "SELECT br.*, b.book_name, m.name AS member_name
     FROM borrowings br
     JOIN books b ON br.book_id = b.book_id
     JOIN members m ON br.member_id = m.member_id
     ORDER BY br.id DESC
     LIMIT 6"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($pageTitle); ?> | Library Admin</title>
    <link rel="stylesheet" href="admin-style.css?v=<?php echo filemtime('admin-style.css'); ?>">
</head>
<body>
    <div class="wrapper">
        <header class="top-bar">
            <a class="admin-brand" href="../index.php">City Library</a>
            <strong>Admin Panel</strong>

            <nav class="sidebar" aria-label="Admin navigation">
                <ul class="nav-menu">
                    <li class="nav-item active"><a href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a href="books.php">Books</a></li>
                    <li class="nav-item"><a href="members.php">Members</a></li>
                    <li class="nav-item"><a href="issue_book.php">Issue Book</a></li>
                    <li class="nav-item"><a href="return_book.php">Return Book</a></li>
                    <li class="nav-item"><a href="history.php">History</a></li>
                    <li class="nav-item"><a href="search.php">Search</a></li>
                    <li class="nav-item"><a href="reports.php">Reports</a></li>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <li class="nav-item"><a href="users.php">Users</a></li>
                    <?php endif; ?>
                </ul>
            </nav>

            <div class="top-bar-right">
                <span>Hi <?php echo e($_SESSION['name']); ?></span>
                <a class="btn-logout" href="../logout.php">Logout</a>
            </div>
        </header>

        <main class="content">


<h2 class="page-title">Dashboard</h2>

<div class="stats-grid">
    <div class="stat-card stat-blue"><div class="stat-number"><?php echo $totalBooks; ?></div><div class="stat-label">Total Books</div></div>
    <div class="stat-card stat-green"><div class="stat-number"><?php echo $totalMembers; ?></div><div class="stat-label">Total Members</div></div>
    <div class="stat-card stat-orange"><div class="stat-number"><?php echo $issued; ?></div><div class="stat-label">Currently Issued</div></div>
    <div class="stat-card stat-red"><div class="stat-number"><?php echo $overdue; ?></div><div class="stat-label">Overdue</div></div>
    <div class="stat-card stat-purple"><div class="stat-number"><?php echo $returned; ?></div><div class="stat-label">Returned</div></div>
    <div class="stat-card stat-teal"><div class="stat-number"><?php echo $totalUsers; ?></div><div class="stat-label">System Users</div></div>
</div>

<div class="card">
    <h3 class="card-title">Quick Actions</h3>
    <div class="quick-actions">
        <a href="books.php?action=add" class="btn btn-primary">Add New Book</a>
        <a href="members.php?action=add" class="btn btn-success">Add New Member</a>
        <a href="issue_book.php" class="btn btn-warning">Issue Book</a>
        <a href="return_book.php" class="btn btn-info">Return Book</a>
        <a href="reports.php?type=final_report" class="btn btn-danger">Final Report</a>
    </div>
</div>

<div class="card">
    <h3 class="card-title">Recent Borrowing Activity</h3>
    <?php if ($recent->num_rows > 0): ?>
    <table class="table">
        <thead><tr><th>Book</th><th>Member</th><th>Issue Date</th><th>Due Date</th><th>Status</th></tr></thead>
        <tbody>
            <?php while ($r = $recent->fetch_assoc()):
                $bc = $r['status'] == 'Issued' ? ($r['due_date'] < date('Y-m-d') ? 'danger' : 'warning') : ($r['status'] == 'Late' ? 'danger' : 'success');
            ?>
            <tr>
                <td><?php echo htmlspecialchars($r['book_name']); ?></td>
                <td><?php echo htmlspecialchars($r['member_name']); ?></td>
                <td><?php echo $r['issue_date']; ?></td>
                <td><?php echo $r['due_date']; ?></td>
                <td><span class="badge badge-<?php echo $bc; ?>"><?php echo $r['status']; ?></span></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p class="no-data">No activity yet.</p>
    <?php endif; ?>
</div>

        </main>

        <footer class="admin-footer">
            <div class="admin-footer-inner">
                <div>&copy; <?php echo date('Y'); ?> City Library</div>
            </div>
        </footer>
    </div>

    <script src="../script.js"></script>
</body>
</html>
