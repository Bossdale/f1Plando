<?php
include __DIR__ . '/connect.php';
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!$connection) {
    die("Connection failed: " . mysqli_connect_error());
}

// Add product
if (isset($_POST['addProduct'])) {
    $supplierID = $_SESSION['userID'];
    $productName = $_POST['productName'];
    $category = $_POST['category'];
    $costPrice = $_POST['costPrice'];
    $sellingPrice = $_POST['sellingPrice'];
    $description = $_POST['description'];

    $insertQuery = "INSERT INTO tblproduct (supplierID, productName, category, costPrice, sellingPrice, description, isActive)
                    VALUES (?, ?, ?, ?, ?, ?, 1)";
    $stmt = mysqli_prepare($connection, $insertQuery);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "issdds", $supplierID, $productName, $category, $costPrice, $sellingPrice, $description);
        if (mysqli_stmt_execute($stmt)) {
            header("Location: supplier_products.php?added=1");
            exit();
        } else {
            echo "Error executing insert query: " . mysqli_stmt_error($stmt);
        }
    } else {
        echo "Error preparing insert statement: " . mysqli_error($connection);
    }
}

// Edit product
if (isset($_POST['editProduct'])) {
    $productID = $_POST['productID'];
    $productName = $_POST['productName'];
    $category = $_POST['category'];
    $costPrice = $_POST['costPrice'];
    $sellingPrice = $_POST['sellingPrice'];
    $description = $_POST['description'];

    $updateQuery = "UPDATE tblproduct 
                    SET productName = ?, category = ?, costPrice = ?, sellingPrice = ?, description = ?
                    WHERE productID = ?";
    $stmt = mysqli_prepare($connection, $updateQuery);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssddsi", $productName, $category, $costPrice, $sellingPrice, $description, $productID);
        if (mysqli_stmt_execute($stmt)) {
            header("Location: supplier_products.php?updated=1");
            exit();
        } else {
            echo "Error executing update query: " . mysqli_stmt_error($stmt);
        }
    } else {
        echo "Error preparing update statement: " . mysqli_error($connection);
    }
}

// Delete product
if (isset($_GET['deleteProduct'])) {
    $productID = $_GET['productID'];

    // SQL query to delete product
    $deleteQuery = "DELETE FROM tblproduct WHERE productID = ?";
    $stmt = mysqli_prepare($connection, $deleteQuery);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $productID);
        if (mysqli_stmt_execute($stmt)) {
            header("Location: supplier_products.php?deleted=1");
            exit();
        } else {
            echo "Error executing delete query: " . mysqli_stmt_error($stmt);
        }
    } else {
        echo "Error preparing delete statement: " . mysqli_error($connection);
    }
}
?>
