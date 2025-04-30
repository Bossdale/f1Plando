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

// Fetch suppliers for dropdown
$supplierQuery = "SELECT supplierID, companyName FROM tblSupplier";
$supplierResult = mysqli_query($connection, $supplierQuery);
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
      <?php endif; ?>

      <!-- Add Product Button -->
      <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#productModal" id="addProductBtn">➕ Add Product</button>

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
                <button class="btn btn-sm btn-warning editProductBtn"
                  data-id="<?= $product['productID'] ?>"
                  data-name="<?= htmlspecialchars($product['productName'], ENT_QUOTES) ?>"
                  data-category="<?= htmlspecialchars($product['category'], ENT_QUOTES) ?>"
                  data-cost="<?= $product['costPrice'] ?>"
                  data-price="<?= $product['sellingPrice'] ?>"
                  data-supplierid="<?= $product['supplierID'] ?>"
                  data-description="<?= htmlspecialchars($product['description'] ?? '', ENT_QUOTES) ?>">
                  ✏️
                </button>
                <button class="btn btn-sm btn-danger"
                    onclick="confirmDelete(<?= $product['productID'] ?>, '<?= htmlspecialchars($product['productName']) ?>')">
                    🗑️
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="productModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="productForm" method="POST" action="owner_product_action.php">
        <input type="hidden" name="productID" id="productID">
        <div class="modal-header">
          <h5 class="modal-title" id="modalTitle">Add Product</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-4">
              <label>Product Name</label>
              <input type="text" name="productName" id="productName" class="form-control" required>
            </div>
            <div class="col-md-3">
              <label>Category</label>
              <input type="text" name="category" id="category" class="form-control" required>
            </div>
            <div class="col-md-2">
              <label>Cost Price</label>
              <input type="number" name="costPrice" id="costPrice" step="0.01" class="form-control" required>
            </div>
            <div class="col-md-3">
              <label>Selling Price</label>
              <input type="number" name="sellingPrice" id="sellingPrice" step="0.01" class="form-control" required>
            </div>
          </div>
          <div class="row mt-2">
            <div class="col-md-6">
              <label>Supplier</label>
              <select name="supplierID" id="supplierID" class="form-control" required>
                <?php mysqli_data_seek($supplierResult, 0); while ($s = mysqli_fetch_assoc($supplierResult)): ?>
                  <option value="<?= $s['supplierID'] ?>"><?= htmlspecialchars($s['companyName']) ?></option>
                <?php endwhile; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label>Description</label>
              <textarea name="description" id="description" class="form-control" rows="2"></textarea>
            </div>
            <div class="col-md-3">
              <label>Status</label>
                <select name="isActive" id="editIsActive" class="form-control">
               <option value="1">Active</option>
               <option value="0">Inactive</option>
                </select>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          
          <button type="submit" id="submitBtn" class="btn btn-success">Save</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>

</div><!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form method="GET" action="owner_product_action.php" class="modal-content">
      <input type="hidden" name="delete" id="deleteProductID">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">Confirm Deletion</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Are you sure you want to permanently delete <strong id="deleteProductName"></strong>?
        This action <span class="text-danger fw-bold">cannot be undone.</span>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-danger">Yes, Delete</button>
      </div>
    </form>
  </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const addBtn = document.getElementById('addProductBtn');
const modal = new bootstrap.Modal(document.getElementById('productModal'));
const form = document.getElementById('productForm');

addBtn.addEventListener('click', function () {
  document.getElementById('modalTitle').innerText = "Add Product";
  document.getElementById('submitBtn').innerText = "Save";
  form.action = 'owner_product_action.php';
  form.reset();
  document.getElementById('productID').value = '';
});

document.querySelectorAll('.editProductBtn').forEach(button => {
  button.addEventListener('click', function () {
    document.getElementById('modalTitle').innerText = "Edit Product";
    document.getElementById('submitBtn').innerText = "Update";
    form.action = 'owner_product_action.php';

    document.getElementById('productID').value = this.dataset.id;
    document.getElementById('productName').value = this.dataset.name;
    document.getElementById('category').value = this.dataset.category;
    document.getElementById('costPrice').value = this.dataset.cost;
    document.getElementById('sellingPrice').value = this.dataset.price;
    document.getElementById('supplierID').value = this.dataset.supplierid;
    document.getElementById('description').value = this.dataset.description;

    modal.show();
  });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  function confirmDelete(productID, productName) {
    document.getElementById('deleteProductID').value = productID;
    document.getElementById('deleteProductName').textContent = productName;

    var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
  }
</script>


</body>
</html>
