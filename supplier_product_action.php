<?php
include __DIR__ . '/connect.php';
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!$connection) {
    die("Connection failed: " . mysqli_connect_error());
}

$supplierID = $_SESSION['userID'];

// Add Delivery (store product for selected store owner)
if (isset($_POST['addDelivery'])) {
    $storeName = $_POST['storeName'];
    $productName = $_POST['productName'];
    $quantity = $_POST['quantity'];

    // Fetch ownerID from store name
    $ownerQuery = "SELECT ownerID FROM tblowner WHERE storeName = ?";
    $stmtOwner = mysqli_prepare($connection, $ownerQuery);
    mysqli_stmt_bind_param($stmtOwner, "s", $storeName);
    mysqli_stmt_execute($stmtOwner);
    mysqli_stmt_bind_result($stmtOwner, $ownerID);
    mysqli_stmt_fetch($stmtOwner);
    mysqli_stmt_close($stmtOwner);

    if (!$ownerID) {
        die("Store owner not found.");
    }

    // Insert product to tblproduct if not already exists (check by supplierID + productName)
    $checkProductQuery = "SELECT productID FROM tblproduct WHERE supplierID = ? AND productName = ?";
    $stmtCheck = mysqli_prepare($connection, $checkProductQuery);
    mysqli_stmt_bind_param($stmtCheck, "is", $supplierID, $productName);
    mysqli_stmt_execute($stmtCheck);
    mysqli_stmt_store_result($stmtCheck);

    if (mysqli_stmt_num_rows($stmtCheck) == 0) {
        // Insert new product to tblproduct
        $insertProductQuery = "INSERT INTO tblproduct (supplierID, productName, category, costPrice, sellingPrice, description, isActive)
                               VALUES (?, ?, '', 0, 0, '', 1)";
        $stmtInsert = mysqli_prepare($connection, $insertProductQuery);
        mysqli_stmt_bind_param($stmtInsert, "is", $supplierID, $productName);
        mysqli_stmt_execute($stmtInsert);
        $productID = mysqli_insert_id($connection);
        mysqli_stmt_close($stmtInsert);
    } else {
        // Get existing productID
        mysqli_stmt_bind_result($stmtCheck, $productID);
        mysqli_stmt_fetch($stmtCheck);
    }
    mysqli_stmt_close($stmtCheck);

    // Insert into inventory for the owner
    $unit = "pcs"; // default
    $reorderLevel = 5;
    $insertInventoryQuery = "INSERT INTO tblinventory (productID, ownerID, quantity, unit, reorderLevel, lastUpdated)
                             VALUES (?, ?, ?, ?, ?, NOW())";
    $stmtInventory = mysqli_prepare($connection, $insertInventoryQuery);
    mysqli_stmt_bind_param($stmtInventory, "iiisi", $productID, $ownerID, $quantity, $unit, $reorderLevel);

    if (mysqli_stmt_execute($stmtInventory)) {
        header("Location: supplier_delivery.php?added=1");
        exit();
    } else {
        echo "Error inserting inventory: " . mysqli_stmt_error($stmtInventory);
    }
    mysqli_stmt_close($stmtInventory);
}
?>
