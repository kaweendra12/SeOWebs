<?php
$pageTitle = 'Borrowing History';
require_once '../config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$filter = $_GET['filter'] ?? '';
$allowedFilters = ['Issued', 'Returned', 'Late'];
$sql = 'SELECT br.*, b.book_name, b.isbn, m.name AS member_name
        FROM borrowings br
        JOIN books b ON br.book_id = b.book_id
        JOIN members m ON br.member_id = m.member_id';

if (in_array($filter, $allowedFilters, true)) {
    $sql .= " WHERE br.status = '$filter'";
}

$history = $conn->query($sql . ' ORDER BY br.id DESC');
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
                <li class="nav-item"><a href="dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a href="books.php">Books</a></li>
                <li class="nav-item"><a href="members.php">Members</a></li>
                <li class="nav-item"><a href="issue_book.php">Issue Book</a></li>
                <li class="nav-item"><a href="return_book.php">Return Book</a></li>
                <li class="nav-item active"><a href="history.php">History</a></li>
                <li class="nav-item"><a href="search.php">Search</a></li>
                <li class="nav-item"><a href="reports.php">Reports</a></li>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <li class="nav-item"><a href="users.php">Users</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <div class="top-bar-right">
            <span> <?php echo e($_SESSION['name']); ?></span>
            <a class="btn-logout" href="../logout.php">Logout</a>
        </div>
    </header>
    <main class="content">

<h2 class="page-title">Borrowing History</h2>
<div class="card">
    <div class="table-toolbar">
        <div class="filter-buttons">
            <a href="history.php" class="btn <?php echo empty($filter)?'btn-primary':'btn-secondary'; ?>">All</a>
            <a href="history.php?filter=Issued" class="btn <?php echo $filter=='Issued'?'btn-warning':'btn-secondary'; ?>">Issued</a>
            <a href="history.php?filter=Returned" class="btn <?php echo $filter=='Returned'?'btn-success':'btn-secondary'; ?>">Returned</a>
            <a href="history.php?filter=Late" class="btn <?php echo $filter=='Late'?'btn-danger':'btn-secondary'; ?>">Late</a>
        </div>
    </div>
    <?php if ($history->num_rows > 0): ?>
    <table class="table">
        <thead><tr><th>ID</th><th>Book</th><th>ISBN</th><th>Member</th><th>Issued</th><th>Due</th><th>Returned</th><th>Status</th></tr></thead>
        <tbody>
            <?php while ($r = $history->fetch_assoc()): ?>
                <?php
                $bc = $r['status'] === 'Late' ? 'danger' : ($r['status'] === 'Returned' ? 'success' : 'warning');
                if ($r['status'] === 'Issued' && $r['due_date'] < date('Y-m-d')) {
                    $bc = 'danger';
                }
                ?>
            <tr>
                <td><?php echo $r['id']; ?></td>
                <td><?php echo htmlspecialchars($r['book_name']); ?></td>
                <td><?php echo htmlspecialchars($r['isbn']); ?></td>
                <td><?php echo htmlspecialchars($r['member_name']); ?></td>
                <td><?php echo $r['issue_date']; ?></td>
                <td><?php echo $r['due_date']; ?></td>
                <td><?php echo $r['return_date']?:'-'; ?></td>
                <td><span class="badge badge-<?php echo $bc; ?>"><?php echo $r['status']; ?></span></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p class="no-data">No history found.</p>
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
