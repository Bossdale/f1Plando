<?php
include __DIR__ . '/connect.php';
session_start();

if (!isset($_GET['id'])) {
  echo "Supplier ID not specified.";
  exit();
}

$supplierID = intval($_GET['id']);

// Fetch supplier info
$supplierQuery = "SELECT s.companyName, u.firstname, u.lastname
                  FROM tblSupplier s
                  JOIN tblUser u ON s.userID = u.userID
                  WHERE s.supplierID = ?";
$stmtSupplier = $connection->prepare($supplierQuery);
$stmtSupplier->bind_param("i", $supplierID);
$stmtSupplier->execute();
$supplierResult = $stmtSupplier->get_result();
$supplier = $supplierResult->fetch_assoc();

if (!$supplier) {
  echo "Supplier not found.";
  exit();
}

// Fetch supplier products
$productQuery = "SELECT productName, price, quantity FROM tblProduct WHERE supplierID = ?";
$stmtProduct = $connection->prepare($productQuery);
$stmtProduct->bind_param("i", $supplierID);
$stmtProduct->execute();
$products = $stmtProduct->get_result();
?>

<!DOCTYPE html>
<html>
<head>
  <title>Products from <?php echo htmlspecialchars($supplier['companyName']); ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="p-4">
  <h2>Products Supplied by <?php echo htmlspecialchars($supplier['firstname'] . ' ' . $supplier['lastname']); ?></h2>
  <h5>Company: <?php echo htmlspecialchars($supplier['companyName']); ?></h5>
  <table class="table table-bordered mt-4">
    <thead class="table-light">
      <tr>
        <th>Product Name</th>
        <th>Price</th>
        <th>Quantity in Stock</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($product = $products->fetch_assoc()): ?>
        <tr>
          <td><?php echo htmlspecialchars($product['productName']); ?></td>
          <td>₱<?php echo number_format($product['price'], 2); ?></td>
          <td><?php echo $product['quantity']; ?></td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
  <a href="owner_supplier.php" class="btn btn-secondary">⬅️ Back to Suppliers</a>
</body>
</html>
