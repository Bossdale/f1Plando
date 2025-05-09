<?php
include __DIR__ . '/connect.php';
session_start();

if (!$connection) {
  die("Database connection failed: " . mysqli_connect_error());
}

// Add Supplier
if (isset($_POST['add_supplier'])) {
  $supplierID = mysqli_real_escape_string($connection, $_POST['userID']);
  $contactPerson = mysqli_real_escape_string($connection, $_POST['contactPerson']);

  // Check if supplier already added (has at least one order)
  $checkQuery = "
    SELECT 1
    FROM tblSupplier s
    JOIN tblproduct p ON s.supplierID = p.supplierID
    JOIN tblorderitems oi ON p.productID = oi.productID
    JOIN tblorder o ON oi.orderID = o.orderID
    WHERE s.supplierID = '$supplierID'
    LIMIT 1
  ";
  $checkResult = mysqli_query($connection, $checkQuery);

  if (mysqli_num_rows($checkResult) > 0) {
    header("Location: owner_supplier.php?error=already_added");
    exit;
  }

  // Update contact person and last delivery date
  $updateQuery = "UPDATE tblSupplier 
                  SET contactPerson = '$contactPerson', lastDeliveryDate = CURRENT_DATE 
                  WHERE supplierID = '$supplierID'";

  if (mysqli_query($connection, $updateQuery)) {
    header("Location: owner_supplier.php?success=added");
    exit;
  } else {
    die("Error adding supplier: " . mysqli_error($connection));
  }
}

// Delete Supplier
if (isset($_POST['delete_supplier_id'])) {
  $supplierID = mysqli_real_escape_string($connection, $_POST['delete_supplier_id']);

  $deleteQuery = "DELETE FROM tblSupplier WHERE supplierID = '$supplierID'";
  if (mysqli_query($connection, $deleteQuery)) {
    header("Location: owner_supplier.php?success=deleted");
    exit;
  } else {
    die("Error deleting supplier: " . mysqli_error($connection));
  }
}

header("Location: owner_supplier.php");
exit;
