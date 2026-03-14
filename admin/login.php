<?php
require_once 'db.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $pwd = $_POST['password'] ?? '';

    if (!empty($username) && !empty($pwd)) {
        $stmt = $pdo->prepare('SELECT id, password FROM admins WHERE username = ?');
        $stmt->execute([$username]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && password_verify($pwd, $admin['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $username;
            header('Location: index.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    } else {
        $error = 'Please enter username and password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - KOPELADAR</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>

<body class="login-page">

    <div class="login-card">
        <div class="login-header">
            <i class="fas fa-shield-alt" style="font-size: 3rem; color: var(--accent); margin-bottom: 1rem;"></i>
            <h2>Admin Portal</h2>
            <p style="color: #94a3b8; font-size: 0.9rem; margin-top: 5px;">Secure Access Only</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="post" action="login.php">
            <div class="form-group">
                <label>Username</label>
                <div style="position: relative;">
                    <i class="fas fa-user" style="position: absolute; left: 15px; top: 15px; color: #64748b;"></i>
                    <input type="text" name="username" required value="admin" style="padding-left: 45px;">
                </div>
            </div>
            <div class="form-group">
                <label>Password</label>
                <div style="position: relative;">
                    <i class="fas fa-lock" style="position: absolute; left: 15px; top: 15px; color: #64748b;"></i>
                    <input type="password" name="password" required value="password" style="padding-left: 45px;">
                </div>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">
                Log In <i class="fas fa-arrow-right" style="margin-left:auto"></i>
            </button>
        </form>
    </div>

</body>

</html>