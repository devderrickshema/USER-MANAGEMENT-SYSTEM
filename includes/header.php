<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <div class="logo-container">
            <img src="img/user management logo.jpg" alt="User Management System Logo" class="logo">
            <h1>User Management System</h1>
        </div>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="signup.php">Sign Up</a></li>
                <?php
                    session_start();
                    if (isset($_SESSION['user_id'])) {
                        echo '<li><a href="profile.php">Profile</a></li>';
                        echo '<li><a href="logout.php">Logout</a></li>';
                    } else {
                        echo '<li><a href="login.php">Login</a></li>';
                    }
                ?>
            </ul>
        </nav>
    </header>
    <main> 