<?php
/**
 * auth/login.php
 * Handles both showing the login form (GET) and checking the
 * submitted email/password (POST).
 */
require_once '../includes/functions.php';
require_once '../config/database.php';

// If already logged in, send the person straight to their dashboard.
if (is_logged_in()) {
    redirect('../' . $_SESSION['role'] . '/dashboard.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $errors[] = 'Please enter both your email and password.';
    } else {
        // Look up the user by email.
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            $errors[] = 'Incorrect email or password.';
        } elseif ($user['status'] === 'suspended') {
            $errors[] = 'Your account has been suspended. Please contact the admin.';
        } else {
            // Success - store details in the session and redirect by role.
            $_SESSION['user_id']   = $user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role']      = $user['role'];
            $_SESSION['email']     = $user['email'];

            set_flash('Welcome back, ' . $user['full_name'] . '!');
            redirect('../' . $user['role'] . '/dashboard.php');
        }
    }
}

$page_title = 'Sign In';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign In - Bee's Events & Decor</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="auth-wrap">
    <div class="auth-card">
        <div class="auth-logo">
            <img src="../assets/img/logo.png" alt="Bee's Events & Decor" class="logo-img">
        </div>

        <div class="tab-switch">
            <a href="login.php" class="active">Sign In</a>
            <a href="register.php">Register</a>
        </div>

        <?php foreach ($errors as $error): ?>
            <div class="alert alert-error"><?php echo h($error); ?></div>
        <?php endforeach; ?>

        <form method="POST" action="login.php">
            <div class="field">
                <label>Email address</label>
                <input type="email" name="email" id="email" placeholder="you@example.com" required value="<?php echo h($_POST['email'] ?? ''); ?>">
            </div>
            <div class="field">
                <label>Password</label>
                <input type="password" name="password" id="password" placeholder="••••••••" required>
                <div style="text-align:right;margin-top:6px;">
                    <a href="forgot-password.php" style="font-size:13px;color:var(--gold);font-weight:600;">Forgot password?</a>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Sign In</button>
        </form>
    </div>
</div>

</body>
</html>
