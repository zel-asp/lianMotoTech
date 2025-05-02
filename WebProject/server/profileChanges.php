<?php
session_start();
include('connection.php');

if (isset($_SESSION['logged_in']) && $_SESSION['user_id'] === 1) {

    if (isset($_POST['save_changes'])) {
        $admin_name = $_POST['admin_name'];
        $admin_email = $_POST['admin_email'];
        $admin_newPass = $_POST['admin_newPass'];
        $admin_confirmPass = $_POST['admin_confirmPass'];
        $gcash_number = $_POST['gcash_number'];
        $gcash_name = $_POST['gcash_name'];
        $user_id = 1;

        // Validate email
        if (!filter_var($admin_email, FILTER_VALIDATE_EMAIL)) {
            die("Invalid email address");
        }

        if ($admin_newPass !== $admin_confirmPass) {
            die("Passwords do not match");
        }

        $hashedPassword = password_hash($admin_newPass, PASSWORD_DEFAULT);

        // Update users table
        $user_stmt = $conn->prepare("UPDATE users SET user_name = ?, user_email = ?, user_password = ? WHERE user_id = ?");
        $user_stmt->bind_param("sssi", $admin_name, $admin_email, $hashedPassword, $user_id);
        $user_stmt->execute();

        // Update GCash info (example assumes separate table 'gcash_info')
        $gcash_stmt = $conn->prepare("UPDATE shipping_fee SET gcash_name = ?, gcash_number = ?");
        $gcash_stmt->bind_param("ss", $gcash_name, $gcash_number);
        $gcash_stmt->execute();

        echo '<script>alert("Profile updated successfully."); window.location.href="../admin.php#profile";</script>';
    }
} else {
    echo '<script>alert("Unauthorized access."); window.location.href="../login.php#profile";</script>';
}
