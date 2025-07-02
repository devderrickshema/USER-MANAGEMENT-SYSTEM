<?php include 'includes/header.php'; ?>

<h2 class="page-title">Login to Your Account</h2>

<div class="form-container">
    <div class="form-image">
        <img src="img/user management logo.jpg" alt="Login Illustration">
    </div>
    <div class="form-content">
        <h3>Login</h3>
        <p>Please enter your credentials to access your account.</p>
        
        <?php
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
                $error = "Username and password are required";
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
                        $error = "Invalid password";
                    }
                } else {
                    $error = "Username not found";
                }
                
                $stmt->close();
                $conn->close();
            }
        }
        ?>
        
        <?php if (!empty($error)) : ?>
            <p class="error-message"><?php echo $error; ?></p>
        <?php endif; ?>
        
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-group">
                <label for="username">Username or Email:</label>
                <input type="text" id="username" name="username" value="<?php echo $username; ?>">
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password">
            </div>
            
            <button type="submit" class="btn">Login</button>
        </form>
        <p>Don't have an account? <a href="signup.php">Sign Up</a></p>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 