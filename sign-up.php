<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign Up</title>

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
            <a href="admin-login.php">
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

    <!-- right -->
    <div class="right">
        <div class="login-box" style="margin-top: 20px; padding: 30px;">
            <h1>Create an Account</h1>
            <p>Join PharmaTrack to monitor medicine</p>

           <div class="forma">
        <form>

            <label for="fullname">Full Name</label>
            <input type="text" id="fullname" placeholder="Enter your full name" required style="margin-bottom: 10px;">

            <label for="username">Username</label>
            <input type="text" id="username" placeholder="Choose a username" required style="margin-bottom: 10px;">

            <label for="password">Password</label>
            <input type="password" id="password" placeholder="Create a password" required style="margin-bottom: 10px;">

            <button type="submit" style="margin-top: 10px;">Register Account</button>

        </form>
</div>
 <p class="error" id="error"></p>

        <p style="margin-top:15px; font-size:13px;">
            Already have an account? <a style="color: rgb(21, 74, 98);" href="user-login.php">Login here</a>
        </p>

        </div>
    </div>

</div>


</body>
</html>
