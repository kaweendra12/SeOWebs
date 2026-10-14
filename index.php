<?php
require_once 'config.php';
session_start();

// Read the small set of values needed by the home page.
$totalBooks = $conn->query("SELECT COUNT(*) AS total FROM books")->fetch_assoc()['total'];
$totalMembers = $conn->query("SELECT COUNT(*) AS total FROM members")->fetch_assoc()['total'];
$totalAvailable = $conn->query("SELECT SUM(available_copies) AS total FROM books")->fetch_assoc()['total'];
$totalCategories = $conn->query("SELECT COUNT(DISTINCT category) AS total FROM books")->fetch_assoc()['total'];
$featuredBooks = $conn->query("SELECT * FROM books ORDER BY id DESC LIMIT 6");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>City Library</title>
    <link rel="stylesheet" href="style.css?v=<?php echo filemtime('style.css'); ?>">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <a class="nav-logo" href="index.php">City Library</a>
            <ul class="nav-links">
                <li><a class="active" href="index.php">Home</a></li>
                <li><a href="library.php">Books</a></li>
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
                    <span> Hi <?php echo e($_SESSION['name']); ?></span>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="register.php">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <main>
        <section class="hero">
            <div class="hero-content">
                <h1>Welcome to City Library</h1>
                <p>This website helps members find library books.</p>
                <p><a href="library.php">View Books</a> | <a href="register.php">Register</a></p>
            </div>
        </section>

        <section class="stats-section">
            <div class="stats-container">
                <div class="stat-item"><strong>Total books:</strong> <?php echo $totalBooks; ?></div>
                <div class="stat-item"><strong>Total members:</strong> <?php echo $totalMembers; ?></div>
                <div class="stat-item"><strong>Available books:</strong> <?php echo $totalAvailable ?: 0; ?></div>
                <div class="stat-item"><strong>Categories:</strong> <?php echo $totalCategories; ?></div>
            </div>
        </section>

        <section class="books-section">
            <div class="section-container">
                <h2>Recently Added Books</h2>
                <?php while ($book = $featuredBooks->fetch_assoc()): ?>
                    <div class="book-card">
                        <!-- e() safely displays database text inside HTML. -->
                        <h3><?php echo e($book['book_name']); ?></h3>
                        <p>Author: <?php echo e($book['author']); ?></p>
                        <p>Category: <?php echo e($book['category']); ?></p>
                        <p>Available copies: <?php echo $book['available_copies']; ?></p>
                    </div>
                <?php endwhile; ?>
                <p><a href="library.php">View all books</a></p>
            </div>
        </section>

        <section class="about-section">
            <div class="section-container">
                <h2>About this project</h2>
                <p>This is a simple library management website made with PHP and MySQL.</p>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="footer-container">&copy; <?php echo date('Y'); ?> City Library</div>
    </footer>
</body>
</html>
