<?php
// auth_guard.php
// Include this at the TOP of any page that requires login.
//
// Usage:
//   require_once 'auth_guard.php';
//   require_login();           // any logged-in user
//   require_role('admin');     // admins only

require_once 'config.php';

function require_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: index.php");
        exit;
    }
}

function require_role(string $role) {
    require_login();
    if ($_SESSION['role'] !== $role) {
        // Wrong role — send back to login
        if ($_SESSION['role'] === 'user') {
            header("Location: user-dashboard.php");
            } else {
                header("Location: index.php");
                }
                exit;
            }
}
?>
