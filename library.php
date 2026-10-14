<?php
require_once 'config.php';
session_start();

// The search box is optional. An empty value shows every book.
$search = '';
if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
}

$sql = "SELECT * FROM books";
if ($search != '') {
    $searchSql = $conn->real_escape_string($search);
    $sql = "SELECT * FROM books WHERE book_name LIKE '%$searchSql%' OR author LIKE '%$searchSql%' OR book_id LIKE '%$searchSql%'
OR category LIKE '%$searchSql%'OR isbn LIKE '%$searchSql%'";
}
$books = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Books</title>
    <link rel="stylesheet" href="style.css?v=<?php echo filemtime('style.css'); ?>">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <a class="nav-logo" href="index.php">City Library</a>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a class="active" href="library.php">Books</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'librarian'): ?>
                        <li><a href="admin/dashboard.php">Admin</a></li>
                    <?php else: ?>
                        <li><a href="my-account.php">My Account</a></li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>
            <div class="nav-actions">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <span>Hi <?php echo e($_SESSION['name']); ?></span>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="register.php">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <section class="library-content">
        <div class="library-container">
            <h1>Library Books</h1>
            <form method="get" class="library-search-form">
                <label>Search by Book ID, Book Name, Author, Category or ISBN</label>
                <input type="text" name="search" value="<?php echo e($search); ?>">
                <button type="submit">Search</button>
                <a href="library.php">Show All</a>
            </form>

            <p>Books found: <?php echo $books->num_rows; ?></p>

            <?php if ($books->num_rows > 0): ?>
                <table class="account-table">
                    <tr>
                        <th>Book ID</th>
                        <th>Book Name</th>
                        <th>Author</th>
                        <th>Category</th>
                        <th>Available Copies</th>
                    </tr>
                    <?php while ($book = $books->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $book['book_id']; ?></td>
                            <td><?php echo e($book['book_name']); ?></td>
                            <td><?php echo e($book['author']); ?></td>
                            <td><?php echo e($book['category']); ?></td>
                            <td><?php echo $book['available_copies']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            <?php else: ?>
                <p>No books found.</p>
            <?php endif; ?>
        </div>
    </section>

    <footer class="footer"><div class="footer-container"><p>&copy; <?php echo date('Y'); ?>  City Library .</p></div></footer>
</body>
</html>
