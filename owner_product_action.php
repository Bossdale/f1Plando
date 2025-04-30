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

// ADD or EDIT
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
?>
