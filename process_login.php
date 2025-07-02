<?php
session_start();

// Initialize variables
$username = "";
$error = "";

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get username and password from form
    $username = htmlspecialchars($_POST["username"]);
    $password = $_POST["password"];
    
    // Validate input
    if (empty($username) || empty($password)) {
        $_SESSION['login_error'] = "Username and password are required";
        header("Location: login.php");
        exit();
    } else {
        // Include database connection
        include 'includes/db_connect.php';
        
        // Prepare SQL statement to prevent SQL injection
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user["password"])) {
                // Password is correct, set session variables
                $_SESSION["user_id"] = $user["id"];
                $_SESSION["username"] = $user["username"];
                
                // Redirect to profile page
                header("Location: profile.php");
                exit();
            } else {
                $_SESSION['login_error'] = "Invalid password";
                $_SESSION['login_username'] = $username;
                header("Location: login.php");
                exit();
            }
        } else {
            $_SESSION['login_error'] = "Username not found";
            header("Location: login.php");
            exit();
        }
        
        $stmt->close();
        $conn->close();
    }
} else {
    // If not a POST request, redirect to the login page
    header("Location: login.php");
    exit();
}
?> 