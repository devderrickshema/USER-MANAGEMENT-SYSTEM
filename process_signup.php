<?php
session_start();

// Initialize variables for form fields
$fullname = $email = $username = "";
$errors = [];

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
            
            // If there are errors, redirect back to the signup page with error messages
            $_SESSION['signup_errors'] = $errors;
            $_SESSION['signup_form_data'] = [
                'fullname' => $fullname,
                'email' => $email,
                'username' => $username
            ];
            header("Location: signup.php");
            exit();
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
                $_SESSION['signup_error'] = "Error creating account: " . $insert_stmt->error;
                header("Location: signup.php");
                exit();
            }
            
            $insert_stmt->close();
        }
        
        $check_stmt->close();
        $conn->close();
    } else {
        // If there are validation errors, redirect back to the signup page with error messages
        $_SESSION['signup_errors'] = $errors;
        $_SESSION['signup_form_data'] = [
            'fullname' => $fullname,
            'email' => $email,
            'username' => $username
        ];
        header("Location: signup.php");
        exit();
    }
} else {
    // If not a POST request, redirect to the signup page
    header("Location: signup.php");
    exit();
}
?> 