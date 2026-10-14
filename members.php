<?php
$pageTitle = 'Member Management';

require_once '../config.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

 $success = ""; $error = ""; $editMember = null; $showForm = false;

// Add or edit a member after the form is submitted.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $address = trim($_POST['address']);
    $telephone = trim($_POST['telephone']);
    $email = trim($_POST['email']);
    $reg_date = $_POST['registration_date'];

    if (empty($name) || empty($address) || empty($telephone) || empty($email) || empty($reg_date)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $error = "Invalid email.";
    } else {
        $name = $conn->real_escape_string($name);
        $address = $conn->real_escape_string($address);
        $telephone = $conn->real_escape_string($telephone);
        $email = $conn->real_escape_string($email);
        $reg_date = $conn->real_escape_string($reg_date);
        if (isset($_POST['add_member'])) {
            // Create a readable member ID such as M001, M002, and M003.
            $last = $conn->query("SELECT member_id FROM members ORDER BY id DESC LIMIT 1")->fetch_assoc();
            if ($last && preg_match('/M(\d+)/', $last['member_id'], $m)) {
                $nextNum = intval($m[1]) + 1;
                $newMid = 'M' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
            } else {
                $newMid = 'M001';
            }

            $sql = "INSERT INTO members (member_id, name, address, telephone, email, registration_date)
                    VALUES ('$newMid', '$name', '$address', '$telephone', '$email', '$reg_date')";
            $conn->query($sql);
            $success = "Member added! (ID: $newMid)";

        } elseif (isset($_POST['edit_member'])) {
            $mid = $conn->real_escape_string($_POST['member_id']);
            $sql = "UPDATE members SET name='$name', address='$address', telephone='$telephone',
                    email='$email', registration_date='$reg_date' WHERE member_id='$mid'";
            $conn->query($sql);
            $success = "Member updated!";
        }
    }
}

// Do not delete a member until every borrowed book is returned.
if (isset($_GET['delete'])) {
    $mid = $conn->real_escape_string($_GET['delete']);
    $chk = $conn->query("SELECT COUNT(*) as c FROM borrowings WHERE member_id='$mid'")->fetch_assoc();
    if ($chk['c'] > 0) { $error = "Cannot delete - member has borrowing history.";
    } else {
        $conn->query("DELETE FROM users WHERE member_id='$mid'");
        $conn->query("DELETE FROM members WHERE member_id='$mid'");
        $success = "Member deleted!";
    }
}
if (isset($_GET['edit'])) {
    $mid = $conn->real_escape_string($_GET['edit']);
    $editMember = $conn->query("SELECT * FROM members WHERE member_id='$mid'")->fetch_assoc();
    $showForm = true;
}
if (isset($_GET['action']) && $_GET['action'] === 'add') $showForm = true;
if (isset($_GET['cancel'])) { $showForm = false; $editMember = null; }

 $search = isset($_GET['search']) ? trim($_GET['search']) : '';
 // Start with every member, then add a search condition only when needed.
 $sql = "SELECT * FROM members WHERE 1=1";
if (!empty($search)) {
    $esc = $conn->real_escape_string($search);
    $sql .= " AND (name LIKE '%$esc%' OR email LIKE '%$esc%' OR telephone LIKE '%$esc%' OR member_id LIKE '%$esc%')";
}
 $sql .= " ORDER BY id DESC";
 $members = $conn->query($sql);
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
                    <li class="nav-item active"><a href="members.php">Members</a></li>
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
                <span><?php echo e($_SESSION['name']); ?></span>
                <a class="btn-logout" href="../logout.php">Logout</a>
            </div>
        </header>
        <main class="content">

<h2 class="page-title">Member Management</h2>
<?php if ($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>

<?php if ($showForm): ?>
<div class="card">
    <h3 class="card-title"><?php echo $editMember ? 'Edit Member ('.$editMember['member_id'].')' : 'Add New Member'; ?></h3>
    <form method="POST" class="book-form">
        <?php if ($editMember): ?><input type="hidden" name="member_id" value="<?php echo $editMember['member_id']; ?>"><?php endif; ?>
        <div class="form-row">
            <div class="form-group"><label>Full Name</label><input type="text" name="name" value="<?php echo $editMember ? htmlspecialchars($editMember['name']) : ''; ?>" required></div>
            <div class="form-group"><label>Telephone</label><input type="text" name="telephone" value="<?php echo $editMember ? htmlspecialchars($editMember['telephone']) : ''; ?>" required></div>
        </div>
        <div class="form-group"><label>Address</label><textarea name="address" rows="2" required><?php echo $editMember ? htmlspecialchars($editMember['address']) : ''; ?></textarea></div>
        <div class="form-row">
            <div class="form-group"><label>Email</label><input type="email" name="email" value="<?php echo $editMember ? htmlspecialchars($editMember['email']) : ''; ?>" required></div>
            <div class="form-group"><label>Registration Date</label><input type="date" name="registration_date" value="<?php echo $editMember ? $editMember['registration_date'] : date('Y-m-d'); ?>" required></div>
        </div>
        <div class="form-actions">
            <button type="submit" name="<?php echo $editMember ? 'edit_member' : 'add_member'; ?>" class="btn btn-primary"><?php echo $editMember ? 'Update' : 'Add Member'; ?></button>
            <a href="members.php?cancel=1" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
<?php endif; ?>

<div class="card">
    <div class="table-toolbar">
        <form method="GET" class="search-form-inline">
            <input type="text" name="search" placeholder="Search members..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn btn-primary">Search</button>
            <?php if (!empty($search)): ?><a href="members.php" class="btn btn-secondary">Clear</a><?php endif; ?>
        </form>
        <?php if (!$showForm): ?><a href="members.php?action=add" class="btn btn-success">+ Add New Member</a><?php endif; ?>
    </div>
    <?php if ($members->num_rows > 0): ?>
    <table class="table">
        <thead><tr><th>Member ID</th><th>Name</th><th>Address</th><th>Telephone</th><th>Email</th><th>Reg. Date</th><th>Actions</th></tr></thead>
        <tbody>
            <?php while ($m = $members->fetch_assoc()): ?>
            <tr>
                <td><strong><?php echo $m['member_id']; ?></strong></td>
                <td><?php echo htmlspecialchars($m['name']); ?></td>
                <td><?php echo htmlspecialchars($m['address']); ?></td>
                <td><?php echo htmlspecialchars($m['telephone']); ?></td>
                <td><?php echo htmlspecialchars($m['email']); ?></td>
                <td><?php echo $m['registration_date']; ?></td>
                <td>
                    <a href="members.php?edit=<?php echo urlencode($m['member_id']); ?>" class="btn btn-sm btn-warning">Edit</a>
                    <a href="members.php?delete=<?php echo urlencode($m['member_id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this member?')">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p class="no-data"><?php echo !empty($search) ? 'No members found.' : 'No members yet.'; ?></p>
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
