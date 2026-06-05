<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login</title>

<link rel="stylesheet" href="user-admin-login.css">

<!-- Google Font -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<!-- Icons -->
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
            <a href="admin-login.php" class="active">
                <i class="fa-solid fa-shield-halved"></i>
                <div>
                    <div class="title">Admin</div>
                    <div class="desc">Full access and control</div>
                </div>
            </a>

            <a href="user-login.php">
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

        <h1>Welcome back!</h1>
        <p>Sign in as Admin to continue</p>
<div class="forma">
        <form>

            <label for="username">Username</label>
            <input type="text" id="username" placeholder="Enter your username" required>

            <label for="password">Password</label>
            <input type="password" id="password" placeholder="Enter your password" required>

            <button type="submit">Sign in as Admin</button>

        </form>
</div>
        <p class="error" id="error"></p>

        <p style="margin-top:15px; font-size:13px;">
            No account yet? <a style="color: rgb(21, 74, 98);" href="sign-up.php">Register here</a>
        </p>

    </div>
</div>


</body>
</html>