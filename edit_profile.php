<?php include 'includes/header.php'; ?>

<?php
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Include database connection
include 'includes/db_connect.php';

// Initialize variables
$user_id = $_SESSION['user_id'];
$fullname = $email = $username = "";
$errors = [];
$success_message = "";

// Fetch current user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    $fullname = $user['fullname'];
    $email = $user['email'];
    $username = $user['username'];
} else {
    echo "Error: User not found";
    exit();
}

// Process form submission
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
        } else if ($email != $user['email']) {
            // Check if email already exists for another user
            $check_email = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $check_email->bind_param("si", $email, $user_id);
            $check_email->execute();
            $email_result = $check_email->get_result();
            
            if ($email_result->num_rows > 0) {
                $errors["email"] = "Email already exists";
            }
            $check_email->close();
        }
    }
    
    // Validate Username
    if (empty($_POST["username"])) {
        $errors["username"] = "Username is required";
    } else {
        $username = htmlspecialchars($_POST["username"]);
        if (!preg_match("/^[a-zA-Z0-9_]*$/", $username)) {
            $errors["username"] = "Only letters, numbers, and underscores allowed";
        } else if ($username != $user['username']) {
            // Check if username already exists for another user
            $check_username = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
            $check_username->bind_param("si", $username, $user_id);
            $check_username->execute();
            $username_result = $check_username->get_result();
            
            if ($username_result->num_rows > 0) {
                $errors["username"] = "Username already exists";
            }
            $check_username->close();
        }
    }
    
    // Validate Password (optional)
    $update_password = false;
    if (!empty($_POST["new_password"])) {
        if (strlen($_POST["new_password"]) < 6) {
            $errors["new_password"] = "Password must be at least 6 characters";
        } else if (empty($_POST["current_password"])) {
            $errors["current_password"] = "Current password is required to set a new password";
        } else if (empty($_POST["confirm_password"])) {
            $errors["confirm_password"] = "Please confirm your new password";
        } else if ($_POST["new_password"] != $_POST["confirm_password"]) {
            $errors["confirm_password"] = "Passwords do not match";
        } else {
            // Verify current password
            if (!password_verify($_POST["current_password"], $user["password"])) {
                $errors["current_password"] = "Current password is incorrect";
            } else {
                $update_password = true;
            }
        }
    }
    
    // If no errors, update profile
    if (empty($errors)) {
        if ($update_password) {
            $hashed_password = password_hash($_POST["new_password"], PASSWORD_DEFAULT);
            $update_stmt = $conn->prepare("UPDATE users SET fullname = ?, email = ?, username = ?, password = ? WHERE id = ?");
            $update_stmt->bind_param("ssssi", $fullname, $email, $username, $hashed_password, $user_id);
        } else {
            $update_stmt = $conn->prepare("UPDATE users SET fullname = ?, email = ?, username = ? WHERE id = ?");
            $update_stmt->bind_param("sssi", $fullname, $email, $username, $user_id);
        }
        
        if ($update_stmt->execute()) {
            // Update session if username changed
            if ($username != $user['username']) {
                $_SESSION['username'] = $username;
            }
            
            $success_message = "Profile updated successfully";
        } else {
            $errors["general"] = "Error updating profile: " . $update_stmt->error;
        }
        
        $update_stmt->close();
    }
}

$stmt->close();
$conn->close();
?>

<h2 class="page-title">Edit Profile</h2>

<div class="form-container">
    <div class="form-content" style="width: 100%; max-width: 600px; margin: 0 auto;">
        <?php if (!empty($success_message)) : ?>
            <p class="success-message"><?php echo $success_message; ?></p>
        <?php endif; ?>
        
        <?php if (isset($errors["general"])) : ?>
            <p class="error-message"><?php echo $errors["general"]; ?></p>
        <?php endif; ?>
        
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
            
            <h3 style="margin-top: 2rem; margin-bottom: 1rem;">Change Password (Optional)</h3>
            
            <div class="form-group">
                <label for="current_password">Current Password:</label>
                <input type="password" id="current_password" name="current_password">
                <?php if (isset($errors["current_password"])) echo "<p class='error-message'>" . $errors["current_password"] . "</p>"; ?>
            </div>
            
            <div class="form-group">
                <label for="new_password">New Password:</label>
                <input type="password" id="new_password" name="new_password">
                <?php if (isset($errors["new_password"])) echo "<p class='error-message'>" . $errors["new_password"] . "</p>"; ?>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm New Password:</label>
                <input type="password" id="confirm_password" name="confirm_password">
                <?php if (isset($errors["confirm_password"])) echo "<p class='error-message'>" . $errors["confirm_password"] . "</p>"; ?>
            </div>
            
            <div class="profile-actions" style="justify-content: flex-start;">
                <button type="submit" class="btn">Save Changes</button>
                <a href="profile.php" class="btn" style="background-color: #95a5a6;">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 