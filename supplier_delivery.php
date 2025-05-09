<?php
include __DIR__ . '/connect.php';
session_start();

if (!$connection) {
  die("Database connection failed: " . mysqli_connect_error());
}

$supplierID = $_SESSION['userID'];

// Fetch deliveries for this supplier
$supplierID = $_SESSION['userID'];

$deliveryQuery = "
  SELECT 
    p.productName,
    oi.quantity,
    o.orderDate,
    ow.storeName
  FROM tblorderitems oi
  JOIN tblproduct p ON oi.productID = p.productID
  JOIN tblorder o ON oi.orderID = o.orderID
  JOIN tblcustomer c ON o.customerID = c.customerID
  JOIN tblowner ow ON c.ownerID = ow.ownerID
  WHERE p.supplierID = '$supplierID'
  ORDER BY o.orderDate DESC
";

$deliveryResult = mysqli_query($connection, $deliveryQuery);
$deliveries = [];
while ($row = mysqli_fetch_assoc($deliveryResult)) {
  $deliveries[] = $row;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Product Management - StoreStock</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #f8f9fa;
    }
    .sidebar {
      background-color: rgb(115, 32, 21);
      color: white;
      height: 100vh;
    }
    .sidebar a {
      color: white;
      display: block;
      padding: 10px 20px;
      text-decoration: none;
    }
    .sidebar a:hover {
      background-color: #af3222;
    }
    .logo {
      border-radius: 50%;
      width: 100px;
      height: 100px;
      object-fit: cover;
    }
  </style>
</head>
<body>

<div class="container-fluid">
  <div class="row">
    <!-- Sidebar -->
    <div class="col-md-2 sidebar p-3">
      <img src="logo.png" class="logo mb-3" alt="Logo">
      <a href="supplier_dashboard.php">🏠 Dashboard</a>
      <a href="supplier_products.php">📦 My Products</a>
      <a href="supplier_delivery.php">🚚 Deliveries</a>
      <a href="supplier_stores.php">🏬 Linked Stores</a>
      <a href="settings.php">⚙️ Settings</a>
      <a href="logout.php">🔓 Logout</a>
    </div>

    <!-- Main Content -->
    <div class="col-md-10 p-4">
      <h3 class="mb-4">Delivery Management</h3>

      <!-- Alert messages -->
      <?php if (isset($_GET['added'])): ?>
        <div class="alert alert-success">Product added successfully!</div>
      <?php elseif (isset($_GET['updated'])): ?>
        <div class="alert alert-info">Product updated successfully!</div>
      <?php elseif (isset($_GET['deleted'])): ?>
        <div class="alert alert-danger">Product deleted permanently!</div>
      <?php endif; ?>

      <!-- Add Product Button -->
      <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
          ➕ Add Product
        </button>
      </div>

<table class="table table-bordered table-striped">
  <thead class="table-light">
    <tr>
      <th>Product Name</th>
      <th>Quantity</th>
      <th>Date</th>
      <th>Store Name</th>
    </tr>
  </thead>
  <tbody>
    <?php if (count($deliveries) > 0): ?>
      <?php foreach ($deliveries as $delivery): ?>
        <tr>
          <td><?= htmlspecialchars($delivery['productName']) ?></td>
          <td><?= (int)$delivery['quantity'] ?></td>
          <td><?= date('F j, Y', strtotime($delivery['orderDate'])) ?></td>
          <td><?= htmlspecialchars($delivery['storeName']) ?></td>
        </tr>
      <?php endforeach; ?>
    <?php else: ?>
      <tr>
        <td colspan="4" class="text-center">No deliveries found.</td>
      </tr>
    <?php endif; ?>
  </tbody>
</table>


    </div>
  </div>
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="supplier_product_action.php" class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Add New Product</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Store Name -->
        <div class="mb-3">
          <label for="storeName" class="form-label">Store Name</label>
          <input type="text" name="storeName" id="storeName" class="form-control" required>
        </div>

        <!-- Product Name -->
        <div class="mb-3">
          <label for="productName" class="form-label">Product Name</label>
          <input type="text" name="productName" id="productName" class="form-control" required>
        </div>

        <!-- Category -->
        <div class="mb-3">
          <label for="category" class="form-label">Category</label>
          <input type="text" name="category" id="category" class="form-control" required>
        </div>

        <!-- Cost Price -->
        <div class="mb-3">
          <label for="costPrice" class="form-label">Cost Price</label>
          <input type="number" name="costPrice" id="costPrice" class="form-control" step="0.01" min="0" required>
        </div>

        <!-- Hidden Supplier ID -->
        <input type="hidden" name="supplierID" value="<?= $_SESSION['userID'] ?>">

      </div>
      <div class="modal-footer">
        <button type="submit" name="addProduct" class="btn btn-primary">Add Product</button>
      </div>
    </form>
  </div>
</div>


</body>
</html>
