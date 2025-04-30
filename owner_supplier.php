<?php
  include __DIR__ . '/connect.php';
  session_start();

  if (!$connection) {
    die("Database connection failed: " . mysqli_connect_error());
  }

  // Fetch suppliers
  $supplierQuery = "SELECT s.supplierID, u.firstname, u.lastname, s.companyName, s.lastDeliveryDate
                    FROM tblSupplier s
                    JOIN tblUser u ON s.userID = u.userID";
  $supplierResult = mysqli_query($connection, $supplierQuery);
  $suppliers = [];
  while ($row = mysqli_fetch_assoc($supplierResult)) {
    $suppliers[] = $row;
  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Suppliers - StoreStock</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #f8f9fa;
    }
    .sidebar {
      height: 100vh;
      background-color:rgb(115, 32, 21);
      color: white;
    }
    .sidebar a {
      color: white;
      text-decoration: none;
      display: block;
      padding: 10px 20px;
    }
    .sidebar a:hover {
      background-color: #af3222;
    }
    .section-title {
      margin: 20px 0 10px;
      font-weight: bold;
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
    <div class="col-md-2 sidebar d-flex flex-column p-3">
      <img src="logo.png" alt="StoreStock Logo" class="logo">
      <h4 class="text-white">STORESTOCK</h4>
      <a href="owner_dashboard.php">Dashboard</a>
      <a href="owner_inventory.php">Inventory</a>
      <a href="owner_product.php">Products</a>
      <a href="owner_customer.php">Customers</a>
      <a href="owner_order.php">Orders</a>
      <a href="owner_supplier.php" class="fw-bold">Suppliers</a>
      <a href="owner_settings.php">Settings</a>
      <a href="logout.php">Logout</a>
    </div>
    <!-- Main Content -->
    <div class="col-md-10 p-4">
      <h3 class="mb-4">Suppliers</h3>

      <!-- Add Supplier Button -->
      <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addModal">➕ Add Supplier</button>

      <!-- Supplier Table -->
      <table class="table table-bordered table-striped">
        <thead class="table-light">
          <tr>
            <th>Name</th>
            <th>Company</th>
            <th>Last Delivery</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($suppliers as $supplier): ?>
            <tr>
              <td><?php echo htmlspecialchars($supplier['firstname'] . ' ' . $supplier['lastname']); ?></td>
              <td><?php echo htmlspecialchars($supplier['companyName']); ?></td>
              <td><?php echo $supplier['lastDeliveryDate']; ?></td>
              <td>
                <a href="supplier_edit.php?id=<?php echo $supplier['supplierID']; ?>" class="btn btn-sm btn-warning">✏️</a>
                <a href="supplier_delete.php?id=<?php echo $supplier['supplierID']; ?>" class="btn btn-sm btn-danger">🗑️</a>
                <a href="supplier_products.php?id=<?php echo $supplier['supplierID']; ?>" class="btn btn-sm btn-info">📦 View Products</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Add Supplier Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="post" action="owner_supplier_action.php" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add Supplier</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label>User ID</label>
          <input type="number" class="form-control" name="userID" required>
        </div>
        <div class="mb-3">
          <label>Company Name</label>
          <input type="text" class="form-control" name="companyName" required>
        </div>
        <div class="mb-3">
          <label>Contact Person</label>
          <input type="text" class="form-control" name="contactPerson" required>
        </div>
        <div class="mb-3">
          <label>Last Delivery Date</label>
          <input type="date" class="form-control" name="lastDeliveryDate" required>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-success" type="submit">Add Supplier</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

