<?php
include __DIR__ . '/connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addDelivery'])) {
    $storeName = mysqli_real_escape_string($connection, $_POST['storeName']);
    $productName = mysqli_real_escape_string($connection, $_POST['productName']);
    $quantity = (int)$_POST['quantity'];
    $supplierID = $_SESSION['userID'];

    // Get ownerID based on storeName
    $ownerQuery = "SELECT ownerID FROM tblowner WHERE storeName = '$storeName' LIMIT 1";
    $ownerResult = mysqli_query($connection, $ownerQuery);

    if ($ownerResult && mysqli_num_rows($ownerResult) > 0) {
        $ownerRow = mysqli_fetch_assoc($ownerResult);
        $ownerID = $ownerRow['ownerID'];

        // Insert product for this owner
        $insertQuery = "
            INSERT INTO tblproduct (
                supplierID, 
                productName, 
                category, 
                costPrice, 
                sellingPrice, 
                description, 
                isActive
            ) VALUES (
                '$supplierID', 
                '$productName', 
                'Uncategorized', 
                0, 
                0, 
                'Added via delivery', 
                1
            )
        ";

        $insertResult = mysqli_query($connection, $insertQuery);

        if ($insertResult) {
            header("Location: supplier_delivery.php?added=1");
            exit();
        } else {
            echo "Error inserting product: " . mysqli_error($connection);
        }
    } else {
        echo "Store not found.";
    }
} else {
    echo "Invalid request.";
}
?>
