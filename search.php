<?php
$pageTitle = 'Search';

require_once '../config.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$labels = [
    'book' => 'Book Name',
    'isbn' => 'ISBN',
    'author' => 'Author',
    'book_id' => 'Book ID',
    'member' => 'Member Name',
    'member_id' => 'Member ID',
];
$searchType = $_GET['search_type'] ?? 'book';
$keyword = trim($_GET['keyword'] ?? '');
$results = null;
$count = 0;
$isBookSearch = in_array($searchType, ['book', 'isbn', 'author', 'book_id'], true);

if (!array_key_exists($searchType, $labels)) {
    $searchType = 'book';
    $isBookSearch = true;
}

if ($keyword !== '') {
    $escapedKeyword = $conn->real_escape_string($keyword);
    $queries = [
        'book' => "SELECT * FROM books WHERE book_name LIKE '%$escapedKeyword%' ORDER BY book_name",
        'isbn' => "SELECT * FROM books WHERE isbn LIKE '%$escapedKeyword%' ORDER BY isbn",
        'author' => "SELECT * FROM books WHERE author LIKE '%$escapedKeyword%' ORDER BY author",
        'book_id' => "SELECT * FROM books WHERE book_id LIKE '%$escapedKeyword%' ORDER BY book_id",
        'member' => "SELECT * FROM members WHERE name LIKE '%$escapedKeyword%' OR email LIKE '%$escapedKeyword%' ORDER BY name",
        'member_id' => "SELECT * FROM members WHERE member_id LIKE '%$escapedKeyword%' ORDER BY member_id",
    ];

    $results = $conn->query($queries[$searchType]);
    $count = $results ? $results->num_rows : 0;
}
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
                    <li class="nav-item active"><a href="search.php">Search</a></li>
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
            <h2 class="page-title">Search</h2>
            <section class="card">
                <h3 class="card-title">Search Library Records</h3>
                <form method="get" class="book-form" style="max-width: 800px;">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="search_type">Search Type</label>
                            <select id="search_type" name="search_type">
                                <?php foreach ($labels as $type => $label): ?>
                                    <option value="<?php echo e($type); ?>" <?php echo $searchType === $type ? 'selected' : ''; ?>>
                                        <?php echo e($label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group" style="flex: 2;">
                            <label for="keyword">Keyword</label>
                            <input id="keyword" type="text" name="keyword" value="<?php echo e($keyword); ?>" required>
                        </div>
                        <div class="form-group" style="align-self: flex-end;">
                            <button type="submit" class="btn btn-primary">Search</button>
                        </div>
                    </div>
                </form>
            </section>

            <?php if ($keyword !== ''): ?>
                <section class="card">
                    <p class="report-summary">
                        Results for "<strong><?php echo e($keyword); ?></strong>" in
                        <strong><?php echo e($labels[$searchType]); ?></strong> &mdash;
                        <strong><?php echo $count; ?></strong> found
                    </p>

                    <?php if ($count > 0 && $isBookSearch): ?>
                        <table class="table">
                            <thead><tr><th>Book ID</th><th>ISBN</th><th>Name</th><th>Author</th><th>Category</th><th>Publisher</th><th>Qty</th><th>Avail</th></tr></thead>
                            <tbody>
                                <?php while ($book = $results->fetch_assoc()): ?>
                                    <tr><td><?php echo e($book['book_id']); ?></td><td><?php echo e($book['isbn']); ?></td><td><?php echo e($book['book_name']); ?></td><td><?php echo e($book['author']); ?></td><td><?php echo e($book['category']); ?></td><td><?php echo e($book['publisher']); ?></td><td><?php echo $book['quantity']; ?></td><td><?php echo $book['available_copies']; ?></td></tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php elseif ($count > 0): ?>
                        <table class="table">
                            <thead><tr><th>Member ID</th><th>Name</th><th>Address</th><th>Telephone</th><th>Email</th><th>Reg. Date</th></tr></thead>
                            <tbody>
                                <?php while ($member = $results->fetch_assoc()): ?>
                                    <tr><td><?php echo e($member['member_id']); ?></td><td><?php echo e($member['name']); ?></td><td><?php echo e($member['address']); ?></td><td><?php echo e($member['telephone']); ?></td><td><?php echo e($member['email']); ?></td><td><?php echo $member['registration_date']; ?></td></tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="no-data">No results found.</p>
                    <?php endif; ?>
                </section>
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
