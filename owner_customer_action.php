<?php
include __DIR__ . '/connect.php';
session_start();

if (!isset($_SESSION['userID'])) {
  header("Location: signin.php");
  exit();
}

$currentUserID = $_SESSION['userID'];

// DELETE
if (isset($_GET['delete'])) {
  $customerID = intval($_GET['delete']);

  $stmt = $connection->prepare("DELETE FROM tblcustomer WHERE customerID = ? AND ownerID = ?");
  $stmt->bind_param("ii", $customerID, $currentUserID);
  if ($stmt->execute()) {
    header("Location: owner_customer.php?deleted=1");
    exit();
  } else {
    echo "Error deleting: " . $stmt->error;
  }
}

// ADD or EDIT
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $customerID = isset($_POST['customerID']) ? intval($_POST['customerID']) : 0;
  $fullName = $_POST['fullName'] ?? '';
  $contactNumber = $_POST['contactNumber'] ?? '';
  $totalDebt = isset($_POST['totalDebt']) ? floatval($_POST['totalDebt']) : 0.0;

  if ($customerID > 0) {
    // EDIT
    $stmt = $connection->prepare("UPDATE tblCustomer SET fullName = ?, contactNumber = ?, totalDebt = ? WHERE customerID = ? AND ownerID = ?");
    $stmt->bind_param("ssdii", $fullName, $contactNumber, $totalDebt, $customerID, $currentUserID);
    if ($stmt->execute()) {
      header("Location: owner_customer.php?updated=1");
      exit();
    } else {
      echo "Update error: " . $stmt->error;
    }
  } else {
    // ADD
    $stmt = $connection->prepare("INSERT INTO tblcustomer (ownerID, fullName, contactNumber, totalDebt, lastPurchaseDate, createdAt) VALUES (?, ?, ?, ?, CURRENT_DATE, CURRENT_DATE)");
    $stmt->bind_param("issd", $currentUserID, $fullName, $contactNumber, $totalDebt);
    if ($stmt->execute()) {
      header("Location: owner_customer.php?added=1");
      exit();
    } else {
      echo "Insert error: " . $stmt->error;
    }
  }
}
?>
