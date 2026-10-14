<?php
 $pageTitle = "Issue Book";
require_once '../config.php';
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: ../login.php'); exit(); }

$success = '';
$error = '';
 // Only books with at least one available copy can be issued.
 $books = $conn->query("SELECT * FROM books WHERE available_copies > 0 ORDER BY book_name");
 $members = $conn->query("SELECT * FROM members ORDER BY name");

// Process the issue form when the librarian submits it.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $book_id = $_POST['book_id']; // VARCHAR
    $member_id = $_POST['member_id']; // VARCHAR
    $issue_date = $_POST['issue_date'];
    $due_date = $_POST['due_date'];

    if (empty($book_id) || empty($member_id)) { $error = "Select book and member.";
    } elseif (empty($issue_date) || empty($due_date)) { $error = "Set dates.";
    } elseif ($due_date <= $issue_date) { $error = "Due date must be after issue date.";
    } else {
        $escBid = $conn->real_escape_string($book_id);
        $escMid = $conn->real_escape_string($member_id);
        // Do not issue another book if the member has an overdue book.
        $od = $conn->query("SELECT COUNT(*) as c FROM borrowings WHERE member_id='$escMid' AND status='Issued' AND due_date < CURDATE()")->fetch_assoc();
        if ($od['c'] > 0) { $error = "Member has overdue books. Return them first.";
        } else {
            // Save the borrowing record, including the date by which it must be returned.
            $escIssueDate = $conn->real_escape_string($issue_date);
            $escDueDate = $conn->real_escape_string($due_date);
            $issuedByUserId = (int) $_SESSION['user_id'];
            $sql = "INSERT INTO borrowings (book_id, member_id, issue_date, due_date, status, issued_by_user_id)
                    VALUES ('$escBid', '$escMid', '$escIssueDate', '$escDueDate', 'Issued', $issuedByUserId)";
            $conn->query($sql);
            // One physical copy has left the library, so reduce availability by one.
            $conn->query("UPDATE books SET available_copies = available_copies - 1 WHERE book_id='$escBid'");
            header("Location: issue_book.php?success=1"); exit();
        }
    }
}
if (isset($_GET['success'])) $success = 'Book issued successfully!';
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
                <li class="nav-item active"><a href="issue_book.php">Issue Book</a></li>
                <li class="nav-item"><a href="return_book.php">Return Book</a></li>
                <li class="nav-item"><a href="history.php">History</a></li>
                <li class="nav-item"><a href="search.php">Search</a></li>
                <li class="nav-item"><a href="reports.php">Reports</a></li>
                <?php if ($_SESSION['role'] === 'admin'): ?><li class="nav-item"><a href="users.php">Users</a></li><?php endif; ?>
            </ul>
        </nav>
        <div class="top-bar-right">
            <span> <?php echo e($_SESSION['name']); ?></span>
            <a class="btn-logout" href="../logout.php">Logout</a>
        </div>
    </header>
    <main class="content">

<h2 class="page-title">Issue Book</h2>
<?php if ($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>

<div class="card">
    <h3 class="card-title">Issue a Book to a Member</h3>
    <form method="POST" class="book-form">
        <div class="form-row">
            <div class="form-group">
                <label>Select Member</label>
                <select name="member_id" required>
                    <option value="">-- Select Member --</option>
                    <?php $members->data_seek(0); while ($m = $members->fetch_assoc()): ?>
                    <option value="<?php echo $m['member_id']; ?>"><?php echo htmlspecialchars($m['name'])." (".$m['member_id'].")"; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Select Book</label>
                <select name="book_id" required>
                    <option value="">-- Select Book --</option>
                    <?php $books->data_seek(0); while ($b = $books->fetch_assoc()): ?>
                    <option value="<?php echo $b['book_id']; ?>"><?php echo $b['book_id']." - ".htmlspecialchars($b['book_name'])." [".$b['available_copies']." avail]"; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group"><label>Issue Date</label><input type="date" name="issue_date" value="<?php echo date('Y-m-d'); ?>" required></div>
            <div class="form-group"><label>Due Date</label><input type="date" name="due_date" value="<?php echo date('Y-m-d',strtotime('+14 days')); ?>" required></div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Issue Book</button>
            <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
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
