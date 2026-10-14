<?php
$pageTitle = 'Reports';

require_once '../config.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

 $type = isset($_GET['type']) ? $_GET['type'] : 'all_books';
 // Use one timestamp so the selected report shows when it was generated.
 $now = date('Y-m-d H:i:s');
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
                    <li class="nav-item"><a href="history.php">History</a></li>
                    <li class="nav-item"><a href="search.php">Search</a></li>
                    <li class="nav-item active"><a href="reports.php">Reports</a></li>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <li class="nav-item"><a href="users.php">Users</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
            <div class="top-bar-right">
                <span><?php echo e($_SESSION['name']); ?></span>
                <a class="btn-logout" href="../logout.php">Logout</a>
            </div>
        </header>
        <main class="content">

<h2 class="page-title">Reports</h2>

<div class="card">
    <div class="report-nav">
        <a href="reports.php?type=all_books" class="btn <?php echo $type=='all_books'?'btn-primary':'btn-secondary'; ?>">All Books</a>
        <a href="reports.php?type=all_members" class="btn <?php echo $type=='all_members'?'btn-primary':'btn-secondary'; ?>">All Members</a>
        <a href="reports.php?type=currently_issued" class="btn <?php echo $type=='currently_issued'?'btn-primary':'btn-secondary'; ?>">Currently Issued</a>
        <a href="reports.php?type=currently_received" class="btn <?php echo $type=='currently_received'?'btn-primary':'btn-secondary'; ?>">Currently Received</a>
        <a href="reports.php?type=late_received" class="btn <?php echo $type=='late_received'?'btn-primary':'btn-secondary'; ?>">Late Received</a>
        <a href="reports.php?type=final_report" class="btn <?php echo $type=='final_report'?'btn-primary':'btn-secondary'; ?>">Final Report</a>
    </div>
</div>

<?php if ($type === 'all_books'):
    $data = $conn->query("SELECT * FROM books ORDER BY id");
    $tc = $conn->query("SELECT SUM(quantity) as t FROM books")->fetch_assoc()['t'];
    $ta = $conn->query("SELECT SUM(available_copies) as t FROM books")->fetch_assoc()['t'];
?>
<div class="card">
    <div class="report-header"><h3 class="card-title">Report: All Books</h3><span class="report-date"><?php echo $now; ?></span></div>
    <p class="report-summary">Titles: <strong><?php echo $data->num_rows; ?></strong> | Total Copies: <strong><?php echo $tc; ?></strong> | Available: <strong><?php echo $ta; ?></strong></p>
    <table class="table"><thead><tr><th>Book ID</th><th>ISBN</th><th>Name</th><th>Author</th><th>Category</th><th>Publisher</th><th>Qty</th><th>Avail</th></tr></thead>
    <tbody><?php while ($r = $data->fetch_assoc()): ?><tr><td><?php echo $r['book_id']; ?></td><td><?php echo htmlspecialchars($r['isbn']); ?></td><td><?php echo htmlspecialchars($r['book_name']); ?></td><td><?php echo htmlspecialchars($r['author']); ?></td><td><?php echo htmlspecialchars($r['category']); ?></td><td><?php echo htmlspecialchars($r['publisher']); ?></td><td><?php echo $r['quantity']; ?></td><td><?php echo $r['available_copies']; ?></td></tr><?php endwhile; ?></tbody></table>
</div>

<?php elseif ($type === 'all_members'):
    $data = $conn->query("SELECT * FROM members ORDER BY id");
?>
<div class="card">
    <div class="report-header"><h3 class="card-title">Report: All Members</h3><span class="report-date"><?php echo $now; ?></span></div>
    <p class="report-summary">Total Members: <strong><?php echo $data->num_rows; ?></strong></p>
    <table class="table"><thead><tr><th>Member ID</th><th>Name</th><th>Address</th><th>Telephone</th><th>Email</th><th>Reg. Date</th></tr></thead>
    <tbody><?php while ($r = $data->fetch_assoc()): ?><tr><td><?php echo $r['member_id']; ?></td><td><?php echo htmlspecialchars($r['name']); ?></td><td><?php echo htmlspecialchars($r['address']); ?></td><td><?php echo htmlspecialchars($r['telephone']); ?></td><td><?php echo htmlspecialchars($r['email']); ?></td><td><?php echo $r['registration_date']; ?></td></tr><?php endwhile; ?></tbody></table>
</div>

<?php elseif ($type === 'currently_issued'):
    $data = $conn->query("SELECT br.*,b.book_name,b.isbn,m.name AS member_name FROM borrowings br JOIN books b ON br.book_id=b.book_id JOIN members m ON br.member_id=m.member_id WHERE br.status='Issued' ORDER BY br.issue_date DESC");
?>
<div class="card">
    <div class="report-header"><h3 class="card-title">Report: Books Currently Issued</h3><span class="report-date"><?php echo $now; ?></span></div>
    <p class="report-summary">Total Issued: <strong><?php echo $data->num_rows; ?></strong></p>
    <table class="table"><thead><tr><th>ID</th><th>Book</th><th>ISBN</th><th>Member</th><th>Issued</th><th>Due</th><th>Overdue?</th></tr></thead>
    <tbody><?php while ($r = $data->fetch_assoc()): $od=$r['due_date']<date('Y-m-d'); ?>
    <tr class="<?php echo $od?'row-overdue':''; ?>"><td><?php echo $r['id']; ?></td><td><?php echo htmlspecialchars($r['book_name']); ?></td><td><?php echo htmlspecialchars($r['isbn']); ?></td><td><?php echo htmlspecialchars($r['member_name']); ?></td><td><?php echo $r['issue_date']; ?></td><td><?php echo $r['due_date']; ?></td><td><?php echo $od?'<span class="badge badge-danger">Yes</span>':'<span class="badge badge-success">No</span>'; ?></td></tr>
    <?php endwhile; ?></tbody></table>
</div>

<?php elseif ($type === 'currently_received'):
    $data = $conn->query("SELECT br.*,b.book_name,b.isbn,m.name AS member_name FROM borrowings br JOIN books b ON br.book_id=b.book_id JOIN members m ON br.member_id=m.member_id WHERE br.status='Returned' ORDER BY br.return_date DESC");
?>
<div class="card">
    <div class="report-header"><h3 class="card-title">Report: Books Currently Received (Returned on Time)</h3><span class="report-date"><?php echo $now; ?></span></div>
    <p class="report-summary">Total Returned on Time: <strong><?php echo $data->num_rows; ?></strong></p>
    <table class="table"><thead><tr><th>ID</th><th>Book</th><th>ISBN</th><th>Member</th><th>Issued</th><th>Due</th><th>Returned</th></tr></thead>
    <tbody><?php while ($r = $data->fetch_assoc()): ?><tr><td><?php echo $r['id']; ?></td><td><?php echo htmlspecialchars($r['book_name']); ?></td><td><?php echo htmlspecialchars($r['isbn']); ?></td><td><?php echo htmlspecialchars($r['member_name']); ?></td><td><?php echo $r['issue_date']; ?></td><td><?php echo $r['due_date']; ?></td><td><?php echo $r['return_date']; ?></td></tr><?php endwhile; ?></tbody></table>
</div>

<?php elseif ($type === 'late_received'):
    $data = $conn->query("SELECT br.*,b.book_name,b.isbn,m.name AS member_name, DATEDIFF(br.return_date,br.due_date) AS days_late FROM borrowings br JOIN books b ON br.book_id=b.book_id JOIN members m ON br.member_id=m.member_id WHERE br.status='Late' ORDER BY br.return_date DESC");
?>
<div class="card">
    <div class="report-header"><h3 class="card-title">Report: Late Received Books</h3><span class="report-date"><?php echo $now; ?></span></div>
    <p class="report-summary">Total Late Returns: <strong><?php echo $data->num_rows; ?></strong></p>
    <table class="table"><thead><tr><th>ID</th><th>Book</th><th>ISBN</th><th>Member</th><th>Issued</th><th>Due</th><th>Returned</th><th>Days Late</th></tr></thead>
    <tbody><?php while ($r = $data->fetch_assoc()): ?>
    <tr class="row-overdue"><td><?php echo $r['id']; ?></td><td><?php echo htmlspecialchars($r['book_name']); ?></td><td><?php echo htmlspecialchars($r['isbn']); ?></td><td><?php echo htmlspecialchars($r['member_name']); ?></td><td><?php echo $r['issue_date']; ?></td><td><?php echo $r['due_date']; ?></td><td><?php echo $r['return_date']; ?></td><td><span class="badge badge-danger"><?php echo $r['days_late']; ?> days</span></td></tr>
    <?php endwhile; ?></tbody></table>
</div>

<?php elseif ($type === 'final_report'):
    $tb = $conn->query("SELECT COUNT(*) as c FROM books")->fetch_assoc()['c'];
    $tc = $conn->query("SELECT SUM(quantity) as t FROM books")->fetch_assoc()['t'];
    $ta = $conn->query("SELECT SUM(available_copies) as t FROM books")->fetch_assoc()['t'];
    $tm = $conn->query("SELECT COUNT(*) as c FROM members")->fetch_assoc()['c'];
    $ti = $conn->query("SELECT COUNT(*) as c FROM borrowings WHERE status='Issued'")->fetch_assoc()['c'];
    $tr = $conn->query("SELECT COUNT(*) as c FROM borrowings WHERE status='Returned'")->fetch_assoc()['c'];
    $tl = $conn->query("SELECT COUNT(*) as c FROM borrowings WHERE status='Late'")->fetch_assoc()['c'];
    $tt = $conn->query("SELECT COUNT(*) as c FROM borrowings")->fetch_assoc()['c'];
    $to = $conn->query("SELECT COUNT(*) as c FROM borrowings WHERE status='Issued' AND due_date < CURDATE()")->fetch_assoc()['c'];
    $cats = $conn->query("SELECT category,COUNT(*) as titles,SUM(quantity) as copies,SUM(available_copies) as avail FROM books GROUP BY category ORDER BY category");
    $all = $conn->query("SELECT br.*,b.book_name,m.name AS member_name FROM borrowings br JOIN books b ON br.book_id=b.book_id JOIN members m ON br.member_id=m.member_id ORDER BY br.id DESC");
?>
<div class="card">
    <div class="report-header"><h3 class="card-title">Final Report - Library Management System</h3><span class="report-date"><?php echo $now; ?></span></div>
    <h4 style="margin:15px 0 10px;">Summary Statistics</h4>
    <table class="table report-summary-table">
        <tr><td class="summary-label">Total Book Titles</td><td class="summary-value"><?php echo $tb; ?></td></tr>
        <tr><td class="summary-label">Total Book Copies</td><td class="summary-value"><?php echo $tc; ?></td></tr>
        <tr><td class="summary-label">Available Copies</td><td class="summary-value"><?php echo $ta; ?></td></tr>
        <tr><td class="summary-label">Copies Currently Out</td><td class="summary-value"><?php echo $tc-$ta; ?></td></tr>
        <tr><td class="summary-label">Total Members</td><td class="summary-value"><?php echo $tm; ?></td></tr>
        <tr><td class="summary-label">Total Transactions</td><td class="summary-value"><?php echo $tt; ?></td></tr>
        <tr><td class="summary-label">Currently Issued</td><td class="summary-value"><?php echo $ti; ?></td></tr>
        <tr><td class="summary-label">Returned on Time</td><td class="summary-value"><?php echo $tr; ?></td></tr>
        <tr><td class="summary-label">Returned Late</td><td class="summary-value"><?php echo $tl; ?></td></tr>
        <tr><td class="summary-label">Overdue (Not Returned)</td><td class="summary-value" style="color:red;font-weight:bold;"><?php echo $to; ?></td></tr>
    </table>
    <h4 style="margin:20px 0 10px;">Category-wise Summary</h4>
    <table class="table"><thead><tr><th>Category</th><th>Titles</th><th>Copies</th><th>Available</th><th>Issued</th></tr></thead>
    <tbody><?php while ($c = $cats->fetch_assoc()): ?><tr><td><?php echo htmlspecialchars($c['category']); ?></td><td><?php echo $c['titles']; ?></td><td><?php echo $c['copies']; ?></td><td><?php echo $c['avail']; ?></td><td><?php echo $c['copies']-$c['avail']; ?></td></tr><?php endwhile; ?></tbody></table>
    <h4 style="margin:20px 0 10px;">All Transactions</h4>
    <table class="table"><thead><tr><th>ID</th><th>Book</th><th>Member</th><th>Issued</th><th>Due</th><th>Returned</th><th>Status</th></tr></thead>
    <tbody><?php while ($t = $all->fetch_assoc()): $cls=$t['status']=='Late'?'danger':($t['status']=='Returned'?'success':'warning'); ?>
    <tr><td><?php echo $t['id']; ?></td><td><?php echo htmlspecialchars($t['book_name']); ?></td><td><?php echo htmlspecialchars($t['member_name']); ?></td><td><?php echo $t['issue_date']; ?></td><td><?php echo $t['due_date']; ?></td><td><?php echo $t['return_date']?:'-'; ?></td><td><span class="badge badge-<?php echo $cls; ?>"><?php echo $t['status']; ?></span></td></tr>
    <?php endwhile; ?></tbody></table>
</div>
<?php endif; ?>

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
