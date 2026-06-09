<?php
require_once 'config.php';

$error   = '';
$success = '';
$mode    = isset($_GET['mode']) && $_GET['mode'] === 'register' ? 'register' : 'login';

// Already logged in?
if (isset($_SESSION['user_id'])) {
    $dest = $_SESSION['role'] === 'admin' ? 'dashboard.php' : 'user-dashboard.php';
    header("Location: $dest");
    exit;
}

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action   = $_POST['action']   ?? '';
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $mode     = $action === 'register' ? 'register' : 'login';

    // ── LOGIN ──
    if ($action === 'login') {
        if (empty($username) || empty($password)) {
            $error = 'Please fill in all fields.';
        } else {
            $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ? AND role = 'user'");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                $error = 'No user account found with that username.';
            } else {
                $user = $result->fetch_assoc();
                if (!password_verify($password, $user['password'])) {
                    $error = 'Incorrect password. Please try again.';
                } else {
                    $_SESSION['user_id']  = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role']     = 'user';
                    header("Location: user-dashboard.php");
                    exit;
                }
            }
            $stmt->close();
        }
    }

    // ── REGISTER ──
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
            $chk = $conn->prepare("SELECT id FROM users WHERE username = ? AND role = 'user'");
            $chk->bind_param("s", $username);
            $chk->execute();
            $chk->store_result();

            if ($chk->num_rows > 0) {
                $error = 'That username is already taken.';
            } else {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $ins  = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'user')");
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
<title>User <?= $mode === 'register' ? 'Register' : 'Login' ?> — PharmaTrack</title>

<link rel="stylesheet" href="user-admin-login.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="container">

    <!-- LEFT -->
    <div class="left">

        <div class="logo">
            <div class="icon-plus"></div>
            <h2>PharmaTrack</h2>
            <p>Pharmacy Tracker System</p>
        </div>

        <div class="menu">
            <a href="index.php">
                <i class="fa-solid fa-shield-halved"></i>
                <div>
                    <div class="title">Admin</div>
                    <div class="desc">Full access and control</div>
                </div>
            </a>

            <a href="user-login.php" class="active">
                <i class="fa-solid fa-user"></i>
                <div>
                    <div class="title">User</div>
                    <div class="desc">Medicine availability</div>
                </div>
            </a>
        </div>

    </div>

    <!-- RIGHT -->
    <div class="right">
        <div class="login-box">

            <?php if ($mode === 'login'): ?>
            <!-- LOGIN FORM -->
            <h1>Hello There!</h1>
            <p>Sign in to check medicine availability</p>

            <div class="forma">
                <form method="POST">
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
                        <i class="fa-solid fa-right-to-bracket"></i>&nbsp; Sign in as User
                    </button>
                </form>

                <?php if ($error):   ?><p class="error"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?></p><?php endif; ?>
                <?php if ($success): ?><p class="error" style="color:#c6ffee;"><i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($success) ?></p><?php endif; ?>

                <p style="margin-top:15px; font-size:13px;">
                    No account yet? <a href="?mode=register">Register here</a>
                </p>
            </div>

            <?php else: ?>
            <!-- REGISTER FORM -->
            <h1>Create Account</h1>
            <p>Register to check medicine availability</p>

            <div class="forma">
                <form method="POST">
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
                        <i class="fa-solid fa-user-plus"></i>&nbsp; Create Account
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