<?php
include __DIR__ . '/connect.php';
session_start();

if (!$connection) {
  die("Database connection failed: " . mysqli_connect_error());
}

$supplierID = $_SESSION['userID'];

// Fetch deliveries for this supplier
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

// Fetch all store names
$storeQuery = "SELECT storeName FROM tblowner ORDER BY storeName ASC";
$storeResult = mysqli_query($connection, $storeQuery);
$storeNames = [];
while ($row = mysqli_fetch_assoc($storeResult)) {
  $storeNames[] = $row['storeName'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Delivery Management - StoreStock</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
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
      <a href="linked_stores.php">🏬 Linked Stores</a>
      <a href="supplier_reports.php">📊 Reports</a>
      <a href="settings.php">⚙️ Settings</a>
      <a href="logout.php">🔓 Logout</a>
    </div>

    <!-- Main Content -->
    <div class="col-md-10 p-4">
      <h3 class="mb-4">Delivery Management</h3>

      <!-- Alert messages -->
      <?php if (isset($_GET['added'])): ?>
        <div class="alert alert-success">Product added successfully!</div>
      <?php endif; ?>

      <!-- Add Delivery Button -->
      <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDeliveryModal">
          ➕ Add Delivery
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

<!-- Add Delivery Modal -->
<div class="modal fade" id="addDeliveryModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="supplier_delivery_action.php" class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Add Delivery</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label for="storeName" class="form-label">Store Name</label>
          <select name="storeName" id="storeName" class="form-select" required>
            <option value="" disabled selected>Select a store</option>
            <?php foreach ($storeNames as $storeName): ?>
              <option value="<?= htmlspecialchars($storeName) ?>"><?= htmlspecialchars($storeName) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-3">
          <label for="productName" class="form-label">Product Name</label>
          <input type="text" name="productName" id="productName" class="form-control" required>
        </div>
        <div class="mb-3">
          <label for="quantity" class="form-label">Quantity</label>
          <input type="number" name="quantity" id="quantity" class="form-control" min="1" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" name="addDelivery" class="btn btn-primary">Add Delivery</button>
      </div>
    </form>
  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>

</body>
</html>
