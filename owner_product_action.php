<?php
include __DIR__ . '/connect.php';
session_start();

// DELETE
if (isset($_GET['delete'])) {
  $productID = intval($_GET['delete']);

  $stmt = $connection->prepare("DELETE FROM tblProduct WHERE productID = ?");
  $stmt->bind_param("i", $productID);
  if ($stmt->execute()) {
    header("Location: owner_product.php?deleted=1");
    exit();
  } else {
    echo "Error deleting: " . $stmt->error;
  }
}

// ADD or EDIT PRODUCT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['productName'])) {
  $productID = isset($_POST['productID']) ? intval($_POST['productID']) : 0;
  $productName = $_POST['productName'];
  $category = $_POST['category'];
  $costPrice = $_POST['costPrice'];
  $sellingPrice = $_POST['sellingPrice'];
  $supplierID = $_POST['supplierID'];
  $description = $_POST['description'] ?? '';

  if ($productID > 0) {
    // EDIT
    $stmt = $connection->prepare("UPDATE tblProduct SET productName=?, category=?, costPrice=?, sellingPrice=?, supplierID=?, description=? WHERE productID=?");
    $stmt->bind_param("ssddisi", $productName, $category, $costPrice, $sellingPrice, $supplierID, $description, $productID);
    if ($stmt->execute()) {
      header("Location: owner_product.php?updated=1");
      exit();
    } else {
      echo "Update error: " . $stmt->error;
    }
  } else {
    // ADD
    $stmt = $connection->prepare("INSERT INTO tblProduct (productName, category, costPrice, sellingPrice, supplierID, description, isActive) VALUES (?, ?, ?, ?, ?, ?, 1)");
    $stmt->bind_param("ssddis", $productName, $category, $costPrice, $sellingPrice, $supplierID, $description);
    if ($stmt->execute()) {
      header("Location: owner_product.php?added=1");
      exit();
    } else {
      echo "Insert error: " . $stmt->error;
    }
  }
}

// BUY PRODUCT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buyProductID'])) {
  $productID = intval($_POST['buyProductID']);
  $quantity = intval($_POST['quantity']);
  $unit = $_POST['unit'];
  $ownerID = $_SESSION['userID']; // Assuming owner is logged in as user

  // Check if inventory record exists
  $stmt = $connection->prepare("SELECT inventoryID, quantity, reorderlevel FROM tblInventory WHERE productID = ? AND ownerID = ?");
  $stmt->bind_param("ii", $productID, $ownerID);
  $stmt->execute();
  $result = $stmt->get_result();

  $currentDateTime = date("Y-m-d H:i:s");

  if ($result->num_rows > 0) {
    // Update existing inventory
    $row = $result->fetch_assoc();
    $newQuantity = $row['quantity'] + $quantity;
    $newReorderLevel = $row['reorderlevel'] + 1; // Increment reorderLevel by 1

    $updateStmt = $connection->prepare("UPDATE tblInventory SET quantity = ?, unit = ?, reorderlevel = ?, lastUpdated = ? WHERE inventoryID = ?");
    $updateStmt->bind_param("isssi", $newQuantity, $unit, $newReorderLevel, $currentDateTime, $row['inventoryID']);
    $updateStmt->execute();
  } else {
    // Insert new inventory
    $reorderLevel = 1; // First purchase, so reorderLevel starts at 1
    $insertStmt = $connection->prepare("INSERT INTO tblInventory (productID, ownerID, quantity, unit, reorderlevel, lastUpdated) VALUES (?, ?, ?, ?, ?, ?)");
    $insertStmt->bind_param("iiisis", $productID, $ownerID, $quantity, $unit, $reorderLevel, $currentDateTime);
    $insertStmt->execute();
  }

  header("Location: owner_product.php?bought=1");
  exit();
}
?>