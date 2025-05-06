<?php
include __DIR__ . '/connect.php';
session_start();

if (!$connection) {
  die("Database connection failed: " . mysqli_connect_error());
}

$ownerID = $_SESSION['userID'];

// Fetch products
$productQuery = "SELECT p.productID, p.productName, p.category, p.costPrice, p.sellingPrice, p.isActive, p.description, s.companyName, s.supplierID 
                 FROM tblProduct p 
                 JOIN tblSupplier s ON p.supplierID = s.supplierID";
$productResult = mysqli_query($connection, $productQuery);

$products = [];
while ($row = mysqli_fetch_assoc($productResult)) {
  $products[] = $row;
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
    <div class="col-md-2 sidebar d-flex flex-column p-3">
      <img src="logo.png" alt="StoreStock Logo" class="logo">
      <h4>STORESTOCK</h4>
      <a href="owner_dashboard.php">Dashboard</a>
      <a href="owner_inventory.php">Inventory</a>
      <a href="owner_product.php" class="fw-bold">Products</a>
      <a href="owner_customer.php">Customers</a>
      <a href="owner_orders.php">Orders</a>
      <a href="owner_supplier.php">Suppliers</a>
      <a href="owner_settings.php">Settings</a>
      <a href="logout.php">Logout</a>
    </div>

    <!-- Main Content -->
    <div class="col-md-10 p-4">
      <h3 class="mb-4">Product Management</h3>

      <!-- Alert messages -->
      <?php if (isset($_GET['added'])): ?>
        <div class="alert alert-success">Product added successfully!</div>
      <?php elseif (isset($_GET['updated'])): ?>
        <div class="alert alert-info">Product updated successfully!</div>
      <?php elseif (isset($_GET['deleted'])): ?>
        <div class="alert alert-danger">Product deleted permanently!</div>
      <?php elseif (isset($_GET['bought'])): ?>
        <div class="alert alert-success">Inventory updated successfully!</div>
      <?php endif; ?>

      <!-- Product Table -->
      <table class="table table-bordered table-striped">
        <thead class="table-light">
          <tr>
            <th>Name</th>
            <th>Category</th>
            <th>Cost</th>
            <th>Price</th>
            <th>Supplier</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($products as $product): ?>
            <tr>
              <td><?= htmlspecialchars($product['productName']) ?></td>
              <td><?= htmlspecialchars($product['category']) ?></td>
              <td>₱<?= number_format($product['costPrice'], 2) ?></td>
              <td>₱<?= number_format($product['sellingPrice'], 2) ?></td>
              <td><?= htmlspecialchars($product['companyName']) ?></td>
              <td><?= $product['isActive'] ? 'Active' : 'Inactive' ?></td>
              <td>
                <button class="btn btn-sm btn-success buyProductBtn"
                  data-id="<?= $product['productID'] ?>"
                  data-name="<?= htmlspecialchars($product['productName'], ENT_QUOTES) ?>">
                  🛒 Buy
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Buy Product Modal -->
<div class="modal fade" id="buyModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="owner_product_action.php" class="modal-content">
      <input type="hidden" name="buyProductID" id="buyProductID">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">Buy Product</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Buying: <strong id="buyProductName"></strong></p>
        <div class="mb-3">
          <label for="quantity" class="form-label">Quantity</label>
          <input type="number" name="quantity" id="quantity" class="form-control" min="1" required>
        </div>
        <div class="mb-3">
          <label for="unit" class="form-label">Unit</label>
          <select name="unit" id="unit" class="form-select" required>
            <option value="">-- Select Unit --</option>
            <option value="Pcs">Pcs</option>
            <option value="Packs">Packs</option>
            <option value="Sachets">Sachets</option>
            <option value="Bottle">Bottle</option>
            <option value="Cans">Cans</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" name="buyProduct" class="btn btn-success">Confirm Purchase</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  document.querySelectorAll('.buyProductBtn').forEach(btn => {
    btn.addEventListener('click', function () {
      document.getElementById('buyProductID').value = this.dataset.id;
      document.getElementById('buyProductName').textContent = this.dataset.name;
      document.getElementById('quantity').value = '';
      document.getElementById('unit').value = '';

      const modal = new bootstrap.Modal(document.getElementById('buyModal'));
      modal.show();
    });
  });
</script>

</body>
</html>
