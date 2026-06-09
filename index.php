<?php
require_once 'config.php';

$error   = '';
$success = '';
$mode    = isset($_GET['mode']) && $_GET['mode'] === 'register' ? 'register' : 'login';
$role    = 'admin'; // this page is admin-only

if (isset($_SESSION['user_id'])) {
    $dest = $_SESSION['role'] === 'admin' ? 'dashboard.php' : 'user-dashboard.php';
    header("Location: $dest");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action   = $_POST['action']   ?? '';
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $mode     = $action === 'register' ? 'register' : 'login';

    if ($action === 'login') {
        if (empty($username) || empty($password)) {
            $error = 'Please fill in all fields.';
        } else {
            $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ? AND role = 'admin'");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                $error = 'No admin account found with that username.';
            } else {
                $user = $result->fetch_assoc();
                if (!password_verify($password, $user['password'])) {
                    $error = 'Incorrect password. Please try again.';
                } else {
                    $_SESSION['user_id']  = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role']     = 'admin';
                    header("Location: dashboard.php");
                    exit;
                }
            }
            $stmt->close();
        }
    }

    if ($action === 'register') {
        $confirm = $_POST['confirm'] ?? '';

        if (empty($username) || empty($password) || empty($confirm)) {
            $error = 'Please fill in all fields.';
        } elseif (strlen($username) < 3) {
            $error = 'Username must be at least 3 characters.';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters.';
        } elseif ($password !== $confirm) {
            $error = 'Passwords do not match.';
        } else {
            $chk = $conn->prepare("SELECT id FROM users WHERE username = ? AND role = 'admin'");
            $chk->bind_param("s", $username);
            $chk->execute();
            $chk->store_result();

            if ($chk->num_rows > 0) {
                $error = 'An admin account with that username already exists.';
            } else {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $ins  = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'admin')");
                $ins->bind_param("ss", $username, $hash);
                if ($ins->execute()) {
                    $success = 'Account created! You can now sign in.';
                    $mode    = 'login';
                } else {
                    $error = 'Registration failed. Please try again.';
                }
                $ins->close();
            }
            $chk->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PharmaTrack — Admin <?= $mode === 'register' ? 'Register' : 'Login' ?></title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="login.css">
</head>
<body>

<div class="container">

    <div class="left">
        <div class="logo">
            <div class="icon-plus"></div>
            <h2>PharmaTrack</h2>
            <p>Pharmacy Tracker System</p>
        </div>

        <div class="menu">
            <a href="index.php" class="active">
                <i class="fa-solid fa-shield-halved"></i>
                <div>
                    <div class="m-title">Admin</div>
                    <div class="m-desc">Full access and control</div>
                </div>
            </a>
            <a href="user-login.php">
                <i class="fa-solid fa-user"></i>
                <div>
                    <div class="m-title">User</div>
                    <div class="m-desc">Medicine availability</div>
                </div>
            </a>
        </div>
    </div>

    <div class="right">
        <div class="login-box">

            <?php if ($mode === 'login'): ?>
        
            <h1>Welcome back!</h1>
            <p>Sign in as Admin to continue</p>

            <div class="forma">
                <form method="POST" action="index.php">
                    <input type="hidden" name="action" value="login">

                    <label for="username">Username</label>
                    <input type="text" id="username" name="username"
                           placeholder="Enter your username"
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                           required autocomplete="username">

                    <label for="password">Password</label>
                    <input type="password" id="password" name="password"
                           placeholder="Enter your password"
                           required autocomplete="current-password">

                    <button type="submit">
                        <i class="fa-solid fa-right-to-bracket"></i>&nbsp; Sign in as Admin
                    </button>
                </form>

                <?php if ($error):   ?><p class="error"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?></p><?php endif; ?>
                <?php if ($success): ?><p class="error" style="color:#c6ffee;"><i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($success) ?></p><?php endif; ?>

                <p style="margin-top:15px; font-size:13px;">
                    No account yet? <a href="?mode=register">Register here</a>
                </p>
            </div>

            <?php else: ?>
           
            <h1>Create Account</h1>
            <p>Register a new Admin account</p>

            <div class="forma">
                <form method="POST" action="index.php?mode=register">
                    <input type="hidden" name="action" value="register">

                    <label for="username">Username</label>
                    <input type="text" id="username" name="username"
                           placeholder="Choose a username"
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                           required autocomplete="username">

                    <label for="password">Password</label>
                    <input type="password" id="password" name="password"
                           placeholder="Choose a password (min 6 chars)"
                           required autocomplete="new-password">

                    <label for="confirm">Confirm Password</label>
                    <input type="password" id="confirm" name="confirm"
                           placeholder="Re-enter your password"
                           required autocomplete="new-password">

                    <button type="submit">
                        <i class="fa-solid fa-user-plus"></i>&nbsp; Create Admin Account
                    </button>
                </form>

                <?php if ($error):   ?><p class="error"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?></p><?php endif; ?>
                <?php if ($success): ?><p class="error" style="color:#c6ffee;"><i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($success) ?></p><?php endif; ?>

                <p style="margin-top:15px; font-size:13px;">
                    Already have an account? <a href="?mode=login">Sign in here</a>
                </p>
            </div>
            <?php endif; ?>

        </div>
    </div>

</div>
</body>
</html>
