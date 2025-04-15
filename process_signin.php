<?php
// Include the database connection
include __DIR__ . '/connect.php';

if (php_sapi_name() === "cli") {
    die("This script is meant to be run from a web browser.");
}
// Start session
session_start();

// Check if connection was successful
if (!$connection) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Get input values from POST
$usernameOrEmail = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$role = $_POST['role'] ?? '';

// Validate inputs
if (empty($usernameOrEmail) || empty($password) || empty($role)) {
    die("All fields are required.");
}

// Prepare and execute the query
$stmt = $connection->prepare("SELECT * FROM tblUser WHERE (username = ? OR email = ?)");
$stmt->bind_param("ss", $usernameOrEmail, $usernameOrEmail);
$stmt->execute();
$result = $stmt->get_result();

// Check if user exists
if ($result && $result->num_rows === 1) {
    $user = $result->fetch_assoc();

    // Verify password
    if ($password === $user['password']) {

        // Simulated role checking logic (optional: extend db to include a role field if needed)
        if ($role === 'owner' || $role === 'supplier') {
            // Set session variables
            $_SESSION['userID'] = $user['userID'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['firstname'] = $user['firstname'];
            $_SESSION['role'] = $role;

            // Redirect based on role
            if ($role === 'owner') {
                header("Location: owner_dashboard.php");
            } else {
                header("Location: supplier_dashboard.php");
            }
            exit;
        } else {
            echo "Invalid role selected.";
        }
    } else {
        echo "Incorrect password.";
    }
} else {
    echo "User not found.";
}

// Close statement and connection
$stmt->close();
$connection->close();
?>
