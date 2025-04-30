<?php
include __DIR__ . '/connect.php';
session_start();

// Ensure supplierID is passed via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['supplierID']) && is_numeric($_POST['supplierID'])) {
    $supplierID = intval($_POST['supplierID']);

    // Fetch products by supplierID
    $query = "SELECT productName, category, costPrice, sellingPrice, description, isActive 
              FROM tblProduct 
              WHERE supplierID = ?";
    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, "i", $supplierID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $products = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }

    // Fetch supplier name
        $nameQuery = "SELECT u.firstname, u.lastname 
        FROM tblSupplier s 
        JOIN tblUser u ON s.userID = u.userID 
        WHERE s.supplierID = ?";
        $nameStmt = mysqli_prepare($connection, $nameQuery);
        mysqli_stmt_bind_param($nameStmt, "i", $supplierID);
        mysqli_stmt_execute($nameStmt);
        $nameResult = mysqli_stmt_get_result($nameStmt);
        $supplierName = "Unknown Supplier";

        if ($row = mysqli_fetch_assoc($nameResult)) {
        $supplierName = $row['firstname'] . ' ' . $row['lastname'];
        }

} else {
    die("Invalid access — supplier not specified.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Supplier Products</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4 bg-light">
  <div class="container">
  <h3 class="mb-4">Products from: <?= htmlspecialchars($supplierName) ?></h3>

    <?php if (count($products) > 0): ?>
      <table class="table table-bordered table-striped">
        <thead class="table-light">
          <tr>
            <th>Product Name</th>
            <th>Category</th>
            <th>Cost Price (₱)</th>
            <th>Selling Price (₱)</th>
            <th>Description</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($products as $product): ?>
            <tr>
              <td><?= htmlspecialchars($product['productName']) ?></td>
              <td><?= htmlspecialchars($product['category']) ?></td>
              <td><?= number_format($product['costPrice'], 2) ?></td>
              <td><?= number_format($product['sellingPrice'], 2) ?></td>
              <td><?= htmlspecialchars($product['description']) ?></td>
              <td>
                <?php if ($product['isActive'] == 1): ?>
                  <span class="badge bg-success">Active</span>
                <?php else: ?>
                  <span class="badge bg-secondary">Inactive</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p class="text-muted">No products found for this supplier.</p>
    <?php endif; ?>

    <a href="owner_supplier.php" class="btn btn-secondary mt-3">← Back to Suppliers</a>
  </div>
</body>
</html>
