<?php
include __DIR__ . '/connect.php';
session_start();

if (!$connection) {
  die("Database connection failed: " . mysqli_connect_error());
}

$supplierID = $_SESSION['userID'];

// Fetch products
$productQuery = "SELECT p.productID, p.productName, p.category, p.costPrice, p.sellingPrice, p.isActive, p.description, s.companyName, s.supplierID 
                 FROM tblProduct p 
                 JOIN tblSupplier s ON p.supplierID = s.supplierID
                 WHERE s.supplierID = '$supplierID'";
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
      <h3 class="mb-4">Product Management</h3>

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

      <!-- Product Table -->
      <table class="table table-bordered table-striped">
      <thead class="table-light">
  <tr>
    <th>Name</th>
    <th>Category</th>
    <th>Cost</th>
    <th>Price</th>
    <th>Status</th>
    <th>Action</th> <!-- Add this column -->
  </tr>
</thead>
<tbody>
  <?php foreach ($products as $product): ?>
    <tr>
      <td><?= htmlspecialchars($product['productName']) ?></td>
      <td><?= htmlspecialchars($product['category']) ?></td>
      <td>₱<?= number_format($product['costPrice'], 2) ?></td>
      <td>₱<?= number_format($product['sellingPrice'], 2) ?></td>
      <td><?= $product['isActive'] ? 'Active' : 'Inactive' ?></td>
<!-- Inside the Product Table (in the action column) -->
<td>
    <!-- Edit Button -->
    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editProductModal"
            data-id="<?= $product['productID'] ?>"
            data-name="<?= htmlspecialchars($product['productName']) ?>"
            data-category="<?= htmlspecialchars($product['category']) ?>"
            data-cost="<?= $product['costPrice'] ?>"
            data-price="<?= $product['sellingPrice'] ?>"
            data-description="<?= htmlspecialchars($product['description']) ?>">
        ✏️ Edit
    </button>
    <!-- Delete Button -->
    <a href="supplier_product_action.php?deleteProduct=1&productID=<?= $product['productID'] ?>" 
       class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this product?');">
        🗑️ Delete
    </a>
</td>

    </tr>
  <?php endforeach; ?>
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
        <div class="mb-3">
          <label for="productName" class="form-label">Product Name</label>
          <input type="text" name="productName" id="productName" class="form-control" required>
        </div>
        <div class="mb-3">
          <label for="category" class="form-label">Category</label>
          <input type="text" name="category" id="category" class="form-control" required>
        </div>
        <div class="mb-3">
          <label for="costPrice" class="form-label">Cost Price</label>
          <input type="number" name="costPrice" id="costPrice" class="form-control" step="0.01" min="0" required>
        </div>
        <div class="mb-3">
          <label for="sellingPrice" class="form-label">Selling Price</label>
          <input type="number" name="sellingPrice" id="sellingPrice" class="form-control" step="0.01" min="0" required>
        </div>
        <div class="mb-3">
          <label for="description" class="form-label">Description</label>
          <textarea name="description" id="description" class="form-control" rows="3" placeholder="Enter product description"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" name="addProduct" class="btn btn-primary">Add Product</button>
      </div>
    </form>
  </div>
</div>

<!-- Inside your Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="supplier_product_action.php" class="modal-content">
      <div class="modal-header bg-warning text-white">
        <h5 class="modal-title">Edit Product</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="productID" id="editProductID"> <!-- Hidden field for product ID -->
        
        <div class="mb-3">
          <label for="editProductName" class="form-label">Product Name</label>
          <input type="text" name="productName" id="editProductName" class="form-control" required>
        </div>
        <div class="mb-3">
          <label for="editCategory" class="form-label">Category</label>
          <input type="text" name="category" id="editCategory" class="form-control" required>
        </div>
        <div class="mb-3">
          <label for="editCostPrice" class="form-label">Cost Price</label>
          <input type="number" name="costPrice" id="editCostPrice" class="form-control" step="0.01" min="0" required>
        </div>
        <div class="mb-3">
          <label for="editSellingPrice" class="form-label">Selling Price</label>
          <input type="number" name="sellingPrice" id="editSellingPrice" class="form-control" step="0.01" min="0" required>
        </div>
        <div class="mb-3">
          <label for="editDescription" class="form-label">Description</label>
          <textarea name="description" id="editDescription" class="form-control" rows="3"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" name="editProduct" class="btn btn-warning">Update Product</button>
      </div>
    </form>
  </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- JavaScript to populate the edit modal with the current product's data -->
<script>
  const editProductModal = document.getElementById('editProductModal');
  editProductModal.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget; // Button that triggered the modal
    const productID = button.getAttribute('data-id');
    const productName = button.getAttribute('data-name');
    const category = button.getAttribute('data-category');
    const costPrice = button.getAttribute('data-cost');
    const sellingPrice = button.getAttribute('data-price');
    const description = button.getAttribute('data-description');

    // Populate the fields in the modal with the product data
    document.getElementById('editProductID').value = productID;
    document.getElementById('editProductName').value = productName;
    document.getElementById('editCategory').value = category;
    document.getElementById('editCostPrice').value = costPrice;
    document.getElementById('editSellingPrice').value = sellingPrice;
    document.getElementById('editDescription').value = description;
  });
</script>

</body>
</html>
