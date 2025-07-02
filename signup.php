<?php include 'includes/header.php'; ?>

<h2 class="page-title">Create Your Account</h2>

<div class="form-container">
    <div class="form-image">
        <img src="img/user management logo.jpg" alt="Sign Up Illustration">
    </div>
    <div class="form-content">
        <h3>Sign Up</h3>
        <p>Please fill in the form below to create your account.</p>
        
        <?php
        // Initialize variables for form fields
        $fullname = $email = $username = "";
        $errors = [];

        // Get form data if it exists from session
        if (isset($_SESSION['signup_form_data'])) {
            $fullname = $_SESSION['signup_form_data']['fullname'];
            $email = $_SESSION['signup_form_data']['email'];
            $username = $_SESSION['signup_form_data']['username'];
            unset($_SESSION['signup_form_data']);
        }

        // Get errors if they exist
        if (isset($_SESSION['signup_errors'])) {
            $errors = $_SESSION['signup_errors'];
            unset($_SESSION['signup_errors']);
        }

        // Display general error message if it exists
        if (isset($_SESSION['signup_error'])) {
            echo "<p class='error-message'>" . $_SESSION['signup_error'] . "</p>";
            unset($_SESSION['signup_error']);
        }

        // Check if form was submitted
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Validate Full Name
            if (empty($_POST["fullname"])) {
                $errors["fullname"] = "Full Name is required";
            } else {
                $fullname = htmlspecialchars($_POST["fullname"]);
                if (!preg_match("/^[a-zA-Z ]*$/", $fullname)) {
                    $errors["fullname"] = "Only letters and white space allowed";
                }
            }
            
            // Validate Email
            if (empty($_POST["email"])) {
                $errors["email"] = "Email is required";
            } else {
                $email = htmlspecialchars($_POST["email"]);
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errors["email"] = "Invalid email format";
                }
            }
            
            // Validate Username
            if (empty($_POST["username"])) {
                $errors["username"] = "Username is required";
            } else {
                $username = htmlspecialchars($_POST["username"]);
                if (!preg_match("/^[a-zA-Z0-9_]*$/", $username)) {
                    $errors["username"] = "Only letters, numbers, and underscores allowed";
                }
            }
            
            // Validate Password
            if (empty($_POST["password"])) {
                $errors["password"] = "Password is required";
            } else {
                if (strlen($_POST["password"]) < 6) {
                    $errors["password"] = "Password must be at least 6 characters";
                }
            }
            
            // Validate Password Confirmation
            if (empty($_POST["confirm_password"])) {
                $errors["confirm_password"] = "Please confirm your password";
            } else {
                if ($_POST["password"] != $_POST["confirm_password"]) {
                    $errors["confirm_password"] = "Passwords do not match";
                }
            }
            
            // If no errors, process form submission
            if (empty($errors)) {
                // Include database connection
                include 'includes/db_connect.php';
                
                // Check if email or username already exists
                $check_query = "SELECT * FROM users WHERE email = ? OR username = ?";
                $check_stmt = $conn->prepare($check_query);
                $check_stmt->bind_param("ss", $email, $username);
                $check_stmt->execute();
                $result = $check_stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $user = $result->fetch_assoc();
                    if ($user['email'] == $email) {
                        $errors["email"] = "Email already exists";
                    }
                    if ($user['username'] == $username) {
                        $errors["username"] = "Username already exists";
                    }
                } else {
                    // Hash the password
                    $hashed_password = password_hash($_POST["password"], PASSWORD_DEFAULT);
                    
                    // Insert new user
                    $insert_query = "INSERT INTO users (fullname, email, username, password) VALUES (?, ?, ?, ?)";
                    $insert_stmt = $conn->prepare($insert_query);
                    $insert_stmt->bind_param("ssss", $fullname, $email, $username, $hashed_password);
                    
                    if ($insert_stmt->execute()) {
                        // Get the new user ID
                        $user_id = $conn->insert_id;
                        
                        // Set session variables
                        $_SESSION['user_id'] = $user_id;
                        $_SESSION['username'] = $username;
                        
                        // Redirect to profile page
                        header("Location: profile.php");
                        exit();
                    } else {
                        echo "<p class='error-message'>Error: " . $insert_stmt->error . "</p>";
                    }
                    
                    $insert_stmt->close();
                }
                
                $check_stmt->close();
                $conn->close();
            }
        }
        ?>
        
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-group">
                <label for="fullname">Full Name:</label>
                <input type="text" id="fullname" name="fullname" value="<?php echo $fullname; ?>">
                <?php if (isset($errors["fullname"])) echo "<p class='error-message'>" . $errors["fullname"] . "</p>"; ?>
            </div>
            
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo $email; ?>">
                <?php if (isset($errors["email"])) echo "<p class='error-message'>" . $errors["email"] . "</p>"; ?>
            </div>
            
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" value="<?php echo $username; ?>">
                <?php if (isset($errors["username"])) echo "<p class='error-message'>" . $errors["username"] . "</p>"; ?>
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password">
                <?php if (isset($errors["password"])) echo "<p class='error-message'>" . $errors["password"] . "</p>"; ?>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" id="confirm_password" name="confirm_password">
                <?php if (isset($errors["confirm_password"])) echo "<p class='error-message'>" . $errors["confirm_password"] . "</p>"; ?>
            </div>
            
            <button type="submit" class="btn">Sign Up</button>
        </form>
        <p>Already have an account? <a href="login.php">Login</a></p>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 