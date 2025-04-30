<?php
include __DIR__ . '/connect.php';
session_start();

if (!$connection) {
  die("Database connection failed: " . mysqli_connect_error());
}

// Fetch suppliers
$supplierQuery = "SELECT s.supplierID, u.firstname, u.lastname, s.companyName, s.contactPerson, s.lastDeliveryDate
                  FROM tblSupplier s
                  JOIN tblUser u ON s.userID = u.userID";
$supplierResult = mysqli_query($connection, $supplierQuery);
$suppliers = [];
while ($row = mysqli_fetch_assoc($supplierResult)) {
  $suppliers[] = $row;
}

// Fetch supplier dropdown list
$dropdownQuery = "SELECT s.supplierID, u.firstname, u.lastname, s.companyName
                  FROM tblSupplier s
                  JOIN tblUser u ON s.userID = u.userID";
$dropdownResult = mysqli_query($connection, $dropdownQuery);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Suppliers - StoreStock</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
      <img src="logo.png" alt="StoreStock Logo" class="logo mb-2">
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
      <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addSupplierModal">➕ Add Supplier</button>

      <!-- Supplier Table -->
      <table class="table table-bordered table-striped">
        <thead class="table-light">
          <tr>
            <th>Name</th>
            <th>Company</th>
            <th>Contact Person</th>
            <th>Last Delivery</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($suppliers as $supplier): ?>
            <tr>
              <td><?= htmlspecialchars($supplier['firstname'] . ' ' . $supplier['lastname']) ?></td>
              <td><?= htmlspecialchars($supplier['companyName']) ?></td>
              <td><?= htmlspecialchars($supplier['contactPerson']) ?></td>
              <td><?= htmlspecialchars($supplier['lastDeliveryDate']) ?></td>
              <td>
                <form action="owner_supplier_action.php" method="POST" class="d-inline">
                  <input type="hidden" name="delete_supplier_id" value="<?= $supplier['supplierID'] ?>">
                  <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this supplier?')">🗑️</button>
                </form>
          
                <!-- Inside the Actions column -->
                <form action="owner_supplier_product_view.php" method="POST" style="display:inline;">
                  <input type="hidden" name="supplierID" value="<?php echo $supplier['supplierID']; ?>">
                  <button type="submit" class="btn btn-sm btn-info">📦 View Products</button>
                </form>

              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Add Supplier Modal -->
<div class="modal fade" id="addSupplierModal" tabindex="-1" aria-labelledby="addSupplierModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form action="owner_supplier_action.php" method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addSupplierModalLabel">Add Supplier</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="add_supplier" value="1">
        <div class="mb-3">
          <label for="userID" class="form-label">Select Supplier</label>
          <select name="userID" id="userID" class="form-select" required>
            <option value="">-- Select --</option>
            <?php while ($row = mysqli_fetch_assoc($dropdownResult)): ?>
              <option value="<?= $row['supplierID'] ?>">
                <?= htmlspecialchars($row['firstname'] . ' ' . $row['lastname']) ?> - <?= htmlspecialchars($row['companyName']) ?>
              </option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="mb-3">
          <label for="contactPerson" class="form-label">Contact Person</label>
          <input type="text" name="contactPerson" id="contactPerson" class="form-control" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">➕ Add</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
