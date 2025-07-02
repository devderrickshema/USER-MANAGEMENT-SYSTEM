<?php include 'includes/header.php'; ?>

<?php
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Include database connection
include 'includes/db_connect.php';

// Fetch user data
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
} else {
    echo "Error: User not found";
    exit();
}

// Handle profile deletion
if (isset($_POST['delete_profile']) && $_POST['delete_profile'] === 'yes') {
    $delete_stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $delete_stmt->bind_param("i", $user_id);
    
    if ($delete_stmt->execute()) {
        // Logout user
        session_unset();
        session_destroy();
        
        // Redirect to home page
        header("Location: index.php");
        exit();
    } else {
        echo "<p class='error-message'>Error deleting profile: " . $delete_stmt->error . "</p>";
    }
    $delete_stmt->close();
}

$stmt->close();
$conn->close();
?>

<div class="profile-container">
    <article class="profile-content">
        <div class="profile-header">
            <img src="img/profile avatar.png" alt="Profile Avatar">
            <h2>Welcome, <?php echo htmlspecialchars($user['username']); ?>!</h2>
        </div>
        
        <section class="profile-details">
            <div class="detail-item">
                <h3>Full Name</h3>
                <p><?php echo htmlspecialchars($user['fullname']); ?></p>
            </div>
            
            <div class="detail-item">
                <h3>Email</h3>
                <p><?php echo htmlspecialchars($user['email']); ?></p>
            </div>
            
            <div class="detail-item">
                <h3>Username</h3>
                <p><?php echo htmlspecialchars($user['username']); ?></p>
            </div>
            
            <div class="detail-item">
                <h3>Member Since</h3>
                <p><?php echo date('F j, Y', strtotime($user['reg_date'])); ?></p>
            </div>
        </section>
        
        <section class="profile-actions">
            <a href="edit_profile.php" class="btn btn-edit">Edit Profile</a>
            <button id="delete-btn" class="btn btn-delete">Delete Profile</button>
        </section>
        
        <!-- Delete confirmation modal -->
        <div id="delete-modal" class="modal" style="display: none;">
            <div class="modal-content">
                <h3>Confirm Deletion</h3>
                <p>Are you sure you want to delete your profile? This action cannot be undone.</p>
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <input type="hidden" name="delete_profile" value="yes">
                    <div class="modal-buttons">
                        <button type="button" id="cancel-delete" class="btn">Cancel</button>
                        <button type="submit" class="btn btn-delete">Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </article>
</div>

<script>
    // Simple JavaScript for the delete modal
    document.getElementById('delete-btn').addEventListener('click', function() {
        document.getElementById('delete-modal').style.display = 'flex';
    });
    
    document.getElementById('cancel-delete').addEventListener('click', function() {
        document.getElementById('delete-modal').style.display = 'none';
    });
</script>

<style>
    .modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
    }
    
    .modal-content {
        background-color: white;
        padding: 2rem;
        border-radius: 10px;
        max-width: 500px;
        width: 90%;
    }
    
    .modal-buttons {
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
        margin-top: 2rem;
    }
</style>

<?php include 'includes/footer.php'; ?> 