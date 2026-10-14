<?php
$pageTitle = 'User Management';

require_once '../config.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

if ($_SESSION['role'] !== 'admin') {
    header('Location: dashboard.php');
    exit();
}

$success = '';
$error = '';

// Create a user after the administrator submits the form.
if (isset($_POST['add_user'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');

    if ($username === '' || $password === '' || $name === '') {
        $error = 'All fields are required.';
    } elseif (!filter_var($username, FILTER_VALIDATE_EMAIL)) {
        $error = 'Username must be a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif (!in_array($role, ['admin', 'librarian', 'reader'], true)) {
        $error = 'Please select a valid role.';
    } else {
        $userCheck = $conn->prepare('SELECT id FROM users WHERE username = ?');
        $userCheck->bind_param('s', $username);
        $userCheck->execute();
        $userCheck->store_result();

        if ($userCheck->num_rows > 0) {
            $error = 'This email is already registered.';
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $memberId = null;

            // A reader must have a member record because borrowings belong to members.
            if ($role === 'reader') {
                $memberCheck = $conn->prepare('SELECT member_id FROM members WHERE email = ?');
                $memberCheck->bind_param('s', $username);
                $memberCheck->execute();
                $memberResult = $memberCheck->get_result();

                if ($memberResult->num_rows === 1) {
                    $memberId = $memberResult->fetch_assoc()['member_id'];
                } elseif ($address === '' || $telephone === '') {
                    $error = 'Address and telephone are required when creating a new reader member.';
                }
            }

            if ($error === '') {
                $conn->begin_transaction();

                try {
                    if ($role === 'reader' && $memberId === null) {
                        $lastMember = $conn->query('SELECT member_id FROM members ORDER BY id DESC LIMIT 1')->fetch_assoc();
                        $nextNumber = $lastMember && preg_match('/M(\\d+)/', $lastMember['member_id'], $matches)
                            ? (int) $matches[1] + 1
                            : 1;
                        $memberId = 'M' . str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);

                        $insertMember = $conn->prepare(
                            'INSERT INTO members (member_id, name, address, telephone, email, registration_date)
                             VALUES (?, ?, ?, ?, ?, CURDATE())'
                        );
                        $insertMember->bind_param('sssss', $memberId, $name, $address, $telephone, $username);
                        if (!$insertMember->execute()) {
                            throw new RuntimeException($insertMember->error);
                        }
                    }

                    $insertUser = $conn->prepare(
                        "INSERT INTO users (username, password, role, name, status, member_id)
                         VALUES (?, ?, ?, ?, 'active', ?)"
                    );
                    $insertUser->bind_param('sssss', $username, $passwordHash, $role, $name, $memberId);
                    if (!$insertUser->execute()) {
                        throw new RuntimeException($insertUser->error);
                    }

                    $conn->commit();
                    $success = $role === 'reader'
                        ? "Reader and member account created successfully! (Member ID: $memberId)"
                        : 'User created successfully!';
                } catch (Throwable $exception) {
                    $conn->rollback();
                    error_log('Unable to create user: ' . $exception->getMessage());
                    $error = 'Unable to create the user. Please try again.';
                }
            }
        }
    }
}

// An administrator cannot delete the account currently in use.
if (isset($_GET['delete'])) {
    $userId = (int) $_GET['delete'];

    if ($userId === (int) $_SESSION['user_id']) {
        $error = 'Cannot delete your own account.';
    } else {
        $conn->query("DELETE FROM users WHERE id = $userId");
        $success = 'User deleted.';
    }
}

$users = $conn->query('SELECT * FROM users ORDER BY id');
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
                    <li class="nav-item"><a href="reports.php">Reports</a></li>
                    <li class="nav-item active"><a href="users.php">Users</a></li>
                </ul>
            </nav>

            <div class="top-bar-right">
                <span><?php echo e($_SESSION['name']); ?></span>
                <a class="btn-logout" href="../logout.php">Logout</a>
            </div>
        </header>

        <main class="content">
            <h2 class="page-title">User Management</h2>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo e($success); ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo e($error); ?></div>
            <?php endif; ?>

            <section class="card">
                <h3 class="card-title">Create New User</h3>

                <form method="post" class="book-form" style="max-width: 600px;">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="username">Email (Username)</label>
                            <input id="username" type="email" name="username" required>
                        </div>

                        <div class="form-group">
                            <label for="password">Password</label>
                            <input id="password" type="password" name="password" minlength="6" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="address">Address <small>(required for a new reader)</small></label>
                            <input id="address" type="text" name="address">
                        </div>

                        <div class="form-group">
                            <label for="telephone">Telephone <small>(required for a new reader)</small></label>
                            <input id="telephone" type="text" name="telephone">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input id="name" type="text" name="name" required>
                        </div>

                        <div class="form-group">
                            <label for="role">Role</label>
                            <select id="role" name="role">
                                <option value="admin">Admin</option>
                                <option value="librarian">Librarian</option>
                                <option value="reader" selected>Reader</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" name="add_user" class="btn btn-primary">Create User</button>
                    </div>
                </form>
            </section>

            <section class="card">
                <h3 class="card-title">All System Users</h3>

                <?php if ($users->num_rows > 0): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Email (Username)</th>
                                <th>Name</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Member ID</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($user = $users->fetch_assoc()): ?>
                                <?php
                                $roleBadge = $user['role'] === 'admin'
                                    ? 'danger'
                                    : ($user['role'] === 'librarian' ? 'warning' : 'success');
                                $statusBadge = $user['status'] === 'active' ? 'success' : 'danger';
                                ?>
                                <tr>
                                    <td><?php echo $user['id']; ?></td>
                                    <td><?php echo e($user['username']); ?></td>
                                    <td><?php echo e($user['name'] ?: '-'); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $roleBadge; ?>">
                                            <?php echo e(ucfirst($user['role'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php echo $statusBadge; ?>">
                                            <?php echo e(ucfirst($user['status'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo e($user['member_id'] ?: '-'); ?></td>
                                    <td>
                                        <?php if ((int) $user['id'] !== (int) $_SESSION['user_id']): ?>
                                            <a
                                                href="users.php?delete=<?php echo $user['id']; ?>"
                                                class="btn btn-sm btn-danger"
                                                onclick="return confirm('Delete this user?')"
                                            >
                                                Delete
                                            </a>
                                        <?php else: ?>
                                            <span style="color: #95a5a6; font-size: 12px;">Current user</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="no-data">No users found.</p>
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
