<?php
$pageTitle = 'Return Book';

require_once '../config.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$success = '';
$error = '';

// Return a book when staff select an issued borrowing record.
if (isset($_GET['return_id'])) {
    $borrowingId = (int) $_GET['return_id'];
    $returnDate = date('Y-m-d');
    $borrowing = $conn->query(
        "SELECT * FROM borrowings WHERE id = $borrowingId AND status = 'Issued'"
    );

    if ($borrowing->num_rows === 1) {
        $borrowingRecord = $borrowing->fetch_assoc();
        $status = $returnDate > $borrowingRecord['due_date'] ? 'Late' : 'Returned';
        $bookId = $conn->real_escape_string($borrowingRecord['book_id']);
        $returnedByUserId = (int) $_SESSION['user_id'];

        $conn->query(
            "UPDATE borrowings
             SET return_date = '$returnDate', status = '$status', returned_by_user_id = $returnedByUserId
             WHERE id = $borrowingId"
        );
        $conn->query(
            "UPDATE books
             SET available_copies = available_copies + 1
             WHERE book_id = '$bookId'"
        );

        $message = $status === 'Late' ? 'Book returned LATE' : 'Book returned on time';
        header('Location: return_book.php?msg=' . urlencode($message));
        exit();
    }

    $error = 'This borrowing record is no longer available for return.';
}

if (isset($_GET['msg'])) {
    $success = $_GET['msg'];
}

// List only the books that still need to be returned.
$issuedBooks = $conn->query(
    "SELECT br.*, b.book_name, b.isbn, m.name AS member_name
     FROM borrowings br
     JOIN books b ON br.book_id = b.book_id
     JOIN members m ON br.member_id = m.member_id
     WHERE br.status = 'Issued'
     ORDER BY br.due_date ASC"
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
                    <li class="nav-item"><a href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a href="books.php">Books</a></li>
                    <li class="nav-item"><a href="members.php">Members</a></li>
                    <li class="nav-item"><a href="issue_book.php">Issue Book</a></li>
                    <li class="nav-item active"><a href="return_book.php">Return Book</a></li>
                    <li class="nav-item"><a href="history.php">History</a></li>
                    <li class="nav-item"><a href="search.php">Search</a></li>
                    <li class="nav-item"><a href="reports.php">Reports</a></li>
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
            <h2 class="page-title">Return Book</h2>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo e($success); ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo e($error); ?></div>
            <?php endif; ?>

            <section class="card">
                <h3 class="card-title">Currently Issued Books</h3>

                <?php if ($issuedBooks->num_rows > 0): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Book</th>
                                <th>ISBN</th>
                                <th>Member</th>
                                <th>Issued</th>
                                <th>Due</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($issuedBook = $issuedBooks->fetch_assoc()): ?>
                                <?php $isOverdue = $issuedBook['due_date'] < date('Y-m-d'); ?>
                                <tr class="<?php echo $isOverdue ? 'row-overdue' : ''; ?>">
                                    <td><?php echo $issuedBook['id']; ?></td>
                                    <td><?php echo e($issuedBook['book_name']); ?></td>
                                    <td><?php echo e($issuedBook['isbn']); ?></td>
                                    <td><?php echo e($issuedBook['member_name']); ?></td>
                                    <td><?php echo $issuedBook['issue_date']; ?></td>
                                    <td><?php echo $issuedBook['due_date']; ?></td>
                                    <td>
                                        <?php if ($isOverdue): ?>
                                            <span class="badge badge-danger">Overdue</span>
                                        <?php else: ?>
                                            <span class="badge badge-warning">Issued</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a
                                            href="return_book.php?return_id=<?php echo $issuedBook['id']; ?>"
                                            class="btn btn-sm btn-success"
                                            onclick="return confirm('Return this book today?')"
                                        >
                                            Return
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="no-data">No books are currently issued.</p>
                <?php endif; ?>
            </section>
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
