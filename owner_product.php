<?php
  include __DIR__ . '/connect.php';
  session_start();

  if (!$connection) {
    die("Database connection failed: " . mysqli_connect_error());
  }

  $ownerID = $_SESSION['userID'];

  // Fetch products
  $productQuery = "SELECT p.productID, p.productName, p.category, p.costPrice, p.sellingPrice, p.isActive, s.companyName 
                  FROM tblProduct p 
                  JOIN tblSupplier s ON p.supplierID = s.supplierID";
  $productResult = mysqli_query($connection, $productQuery);

  $products = [];
  while ($row = mysqli_fetch_assoc($productResult)) {
    $products[] = $row;
  }

  // Fetch suppliers for dropdown
  $supplierQuery = "SELECT supplierID, companyName FROM tblSupplier";
  $supplierResult = mysqli_query($connection, $supplierQuery);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Product Management - StoreStock</title>
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
      <a href="inventory_owner.php">Inventory</a>
      <a href="owner_product.php" class="fw-bold">Products</a>
      <a href="#">Customers</a>
      <a href="#">Orders</a>
      <a href="#">Debts</a>
      <a href="owner_supplier.php">Suppliers</a>
      <a href="#">Reports</a>
      <a href="#">Settings</a>
      <a href="#">Logout</a>
    </div>

    <!-- Main Content -->
    <div class="col-md-10 p-4">
      <h3 class="mb-4">Product Management</h3>

      <!-- Add Product Form -->
      <form action="product_add.php" method="POST" class="mb-5">
        <div class="row">
          <div class="col-md-3">
            <label>Product Name</label>
            <input type="text" name="productName" class="form-control" required>
          </div>
          <div class="col-md-2">
            <label>Category</label>
            <input type="text" name="category" class="form-control" required>
          </div>
          <div class="col-md-2">
            <label>Cost Price</label>
            <input type="number" name="costPrice" class="form-control" step="0.01" required>
          </div>
          <div class="col-md-2">
            <label>Selling Price</label>
            <input type="number" name="sellingPrice" class="form-control" step="0.01" required>
          </div>
          <div class="col-md-3">
            <label>Supplier</label>
            <select name="supplierID" class="form-control" required>
              <?php while ($s = mysqli_fetch_assoc($supplierResult)): ?>
                <option value="<?php echo $s['supplierID']; ?>"><?php echo htmlspecialchars($s['companyName']); ?></option>
              <?php endwhile; ?>
            </select>
          </div>
        </div>
        <div class="row mt-2">
          <div class="col-md-12">
            <label>Description</label>
            <textarea name="description" class="form-control" rows="2"></textarea>
          </div>
        </div>
        <div class="mt-3">
          <button type="submit" class="btn btn-success">➕ Add Product</button>
        </div>
      </form>

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
              <td><?php echo htmlspecialchars($product['productName']); ?></td>
              <td><?php echo htmlspecialchars($product['category']); ?></td>
              <td>₱<?php echo number_format($product['costPrice'], 2); ?></td>
              <td>₱<?php echo number_format($product['sellingPrice'], 2); ?></td>
              <td><?php echo htmlspecialchars($product['companyName']); ?></td>
              <td><?php echo $product['isActive'] ? 'Active' : 'Inactive'; ?></td>
              <td>
                <a href="product_edit.php?id=<?php echo $product['productID']; ?>" class="btn btn-sm btn-warning">✏️</a>
                <a href="product_soft_delete.php?id=<?php echo $product['productID']; ?>" class="btn btn-sm btn-danger">🗑️</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</body>
</html>