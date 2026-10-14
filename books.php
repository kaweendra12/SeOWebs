<?php
$pageTitle = 'Books';
$activePage = 'books';
require_once '../config.php';
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: ../login.php'); exit(); }

$message = '';
$error = '';
$editBook = false;

if (isset($_POST['save'])) {
    $isbn = $conn->real_escape_string($_POST['isbn']);
    $name = $conn->real_escape_string($_POST['book_name']);
    $author = $conn->real_escape_string($_POST['author']);
    $category = $conn->real_escape_string($_POST['category']);
    $publisher = $conn->real_escape_string($_POST['publisher']);
    $quantity = (int) $_POST['quantity'];

    if ($isbn == '' || $name == '' || $author == '' || $category == '' || $publisher == '') {
        $error = 'Please fill in all fields.';
    } elseif (isset($_POST['book_id']) && $_POST['book_id'] != '') {
        $id = $conn->real_escape_string($_POST['book_id']);
        $conn->query("UPDATE books SET isbn='$isbn', book_name='$name', author='$author', category='$category', publisher='$publisher', quantity=$quantity, available_copies=$quantity WHERE book_id='$id'");
        $message = 'Book updated.';
    } else {
        $last = $conn->query("SELECT book_id FROM books ORDER BY id DESC LIMIT 1")->fetch_assoc();
        $number = 1;
        if ($last) {
            $number = (int) substr($last['book_id'], 1) + 1;
        }
        $id = 'B' . str_pad($number, 3, '0', STR_PAD_LEFT);
        $conn->query("INSERT INTO books (book_id, isbn, book_name, author, category, publisher, quantity, available_copies) VALUES ('$id', '$isbn', '$name', '$author', '$category', '$publisher', $quantity, $quantity)");
        $message = 'Book added.';
    }
}

if (isset($_GET['delete'])) {
    $id = $conn->real_escape_string($_GET['delete']);
    $conn->query("DELETE FROM books WHERE book_id='$id'");
    $message = 'Book deleted.';
}

if (isset($_GET['edit'])) {
    $id = $conn->real_escape_string($_GET['edit']);
    $editBook = $conn->query("SELECT * FROM books WHERE book_id='$id'")->fetch_assoc();
}

$books = $conn->query('SELECT * FROM books ORDER BY id DESC');
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
                <li class="nav-item active"><a href="books.php">Books</a></li>
                <li class="nav-item"><a href="members.php">Members</a></li>
                <li class="nav-item"><a href="issue_book.php">Issue Book</a></li>
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

<h2 class="page-title">Book Management</h2>

<?php if ($message != ''): ?><p class="alert alert-success"><?php echo $message; ?></p><?php endif; ?>
<?php if ($error != ''): ?><p class="alert alert-error"><?php echo $error; ?></p><?php endif; ?>

<div class="card">
    <h3><?php echo $editBook ? 'Edit Book' : 'Add Book'; ?></h3>
    <form method="post">
        <input type="hidden" name="book_id" value="<?php echo $editBook ? $editBook['book_id'] : ''; ?>">
        <div class="form-group"><label>ISBN</label><input type="text" name="isbn" value="<?php echo $editBook ? htmlspecialchars($editBook['isbn']) : ''; ?>"></div>
        <div class="form-group"><label>Book Name</label><input type="text" name="book_name" value="<?php echo $editBook ? htmlspecialchars($editBook['book_name']) : ''; ?>"></div>
        <div class="form-group"><label>Author</label><input type="text" name="author" value="<?php echo $editBook ? htmlspecialchars($editBook['author']) : ''; ?>"></div>
        <div class="form-group"><label>Category</label><input type="text" name="category" value="<?php echo $editBook ? htmlspecialchars($editBook['category']) : ''; ?>"></div>
        <div class="form-group"><label>Publisher</label><input type="text" name="publisher" value="<?php echo $editBook ? htmlspecialchars($editBook['publisher']) : ''; ?>"></div>
        <div class="form-group"><label>Quantity</label><input type="number" name="quantity" min="1" value="<?php echo $editBook ? $editBook['quantity'] : 1; ?>"></div>
        <button class="btn" type="submit" name="save">Save</button>
        <a href="books.php">Clear</a>
    </form>
</div>

<div class="card">
    <h3>All Books</h3>
    <table class="table">
        <tr><th>ID</th><th>ISBN</th><th>Name</th><th>Author</th><th>Category</th><th>Quantity</th><th>Available</th><th>Action</th></tr>
        <?php while ($book = $books->fetch_assoc()): ?>
            <tr>
                <td><?php echo $book['book_id']; ?></td>
                <td><?php echo htmlspecialchars($book['isbn']); ?></td>
                <td><?php echo htmlspecialchars($book['book_name']); ?></td>
                <td><?php echo htmlspecialchars($book['author']); ?></td>
                <td><?php echo htmlspecialchars($book['category']); ?></td>
                <td><?php echo $book['quantity']; ?></td>
                <td><?php echo $book['available_copies']; ?></td>
                <td><a href="books.php?edit=<?php echo $book['book_id']; ?>">Edit</a> | <a href="books.php?delete=<?php echo $book['book_id']; ?>" onclick="return confirm('Delete this book?')">Delete</a></td>
            </tr>
        <?php endwhile; ?>
    </table>
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
