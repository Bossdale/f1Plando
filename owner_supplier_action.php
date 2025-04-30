<?php
include __DIR__ . '/connect.php';
session_start();

// DELETE
if (isset($_GET['delete'])) {
  $supplierID = intval($_GET['delete']);

  $stmt = $connection->prepare("DELETE FROM tblSupplier WHERE supplierID = ?");
  $stmt->bind_param("i", $supplierID);
  if ($stmt->execute()) {
    header("Location: owner_supplier.php?deleted=1");
    exit();
  } else {
    echo "Error deleting: " . $stmt->error;
  }
}

// ADD or EDIT
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $supplierID = isset($_POST['supplierID']) ? intval($_POST['supplierID']) : 0;
  $userID = $_POST['userID'];
  $companyName = $_POST['companyName'];
  $contactPerson = $_POST['contactPerson'];
  $lastDeliveryDate = $_POST['lastDeliveryDate'];

  if ($supplierID > 0) {
    // EDIT
    $stmt = $connection->prepare("UPDATE tblSupplier SET userID=?, companyName=?, contactPerson=?, lastDeliveryDate=? WHERE supplierID=?");
    $stmt->bind_param("isssi", $userID, $companyName, $contactPerson, $lastDeliveryDate, $supplierID);
    if ($stmt->execute()) {
      header("Location: owner_supplier.php?updated=1");
      exit();
    } else {
      echo "Update error: " . $stmt->error;
    }
  } else {
    // ADD
    $stmt = $connection->prepare("INSERT INTO tblSupplier (userID, companyName, contactPerson, lastDeliveryDate) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $userID, $companyName, $contactPerson, $lastDeliveryDate);
    if ($stmt->execute()) {
      header("Location: owner_supplier.php?added=1");
      exit();
    } else {
      echo "Insert error: " . $stmt->error;
    }
  }
}
?>
