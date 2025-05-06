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
$stmt = $connection->prepare("SELECT * FROM tbluser WHERE (username = ? OR email = ?) LIMIT 1");
$stmt->bind_param("ss", $usernameOrEmail, $usernameOrEmail);
$stmt->execute();
$result = $stmt->get_result();

// Check if user exists
if ($result && $result->num_rows === 1) {
    $user = $result->fetch_assoc();

    // Verify password
    if ($password === $user['password']) {

        // Check the role of the user in the database
        // First, check if the user is an owner or supplier based on their userID
        $role_query = "SELECT * FROM tblowner WHERE userID = ?";
        $role_stmt = $connection->prepare($role_query);
        $role_stmt->bind_param("i", $user['userID']);
        $role_stmt->execute();
        $owner_result = $role_stmt->get_result();
        
        $role_query_supplier = "SELECT * FROM tblsupplier WHERE userID = ?";
        $role_stmt_supplier = $connection->prepare($role_query_supplier);
        $role_stmt_supplier->bind_param("i", $user['userID']);
        $role_stmt_supplier->execute();
        $supplier_result = $role_stmt_supplier->get_result();

        // Determine actual role of the user
        $actualRole = '';
        if ($owner_result->num_rows > 0) {
            $actualRole = 'owner';
        } elseif ($supplier_result->num_rows > 0) {
            $actualRole = 'supplier';
        }

        // If the role doesn't match the selected role, show an error
        if ($role !== $actualRole) {
            $_SESSION['error'] = "You are an $actualRole, not a $role. Please select the correct role.";
            header("Location: signin.php");
            exit();
        }

        // Set session variables if the role matches
        $_SESSION['userID'] = $user['userID'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['firstname'] = $user['firstname'];
        $_SESSION['role'] = $role;

        // Redirect based on the role
        if ($role === 'owner') {
            header("Location: owner_dashboard.php");
        } elseif ($role === 'supplier') {
            header("Location: supplier_dashboard.php");
        }
        exit();
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