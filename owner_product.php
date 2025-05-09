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


$query = "SELECT storeName FROM tblOwner";
    $result = mysqli_query($connection, $query);
    if ($result) {
    $row = mysqli_fetch_assoc($result);
    $storeName= $row['storeName'];
    }
    
// Fetch suppliers for dropdown
$supplierQuery = "SELECT supplierID, companyName FROM tblSupplier";
$supplierResult = mysqli_query($connection, $supplierQuery);

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>STORESTOCK Product</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
  body {
    font-family: 'Segoe UI', sans-serif;
    background: linear-gradient(to right, #fdf0ec, #fce3db);
    backdrop-filter: blur(5px);
    min-height: 100vh;
    margin: 0;
  }

  html, body {
    height: 100%;
    margin: 0;
    overflow: hidden; /* Prevents double scrollbars */
  }

  .header-bar {
    background: linear-gradient(to right, #732015, #af3222);
    color: white;
    padding: 20px 40px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: sticky;
    top: 0;
    z-index: 1000;
    box-shadow: 0 2px 10px rgba(0,0,0,0.2);
    opacity: 1;
  }

  .sidebar {
    /* background: linear-gradient(to right, rgba(115, 32, 21, 0.4), rgba(175, 50, 34, 0.4)); */
    backdrop-filter: blur(50px);
    -webkit-backdrop-filter: blur(20px); /* for Safari */
    color: maroon;
    min-height: 100vh;
    border-top-right-radius: 0px;
    transition: all 0.3s ease;
    padding-top: 10px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.2);
    position: fixed;
    top: 70px;
    bottom: 0;
    left: 0;
    width: 260px;
    overflow-y: hidden !important;
    overscroll-behavior: none;
}


  .sidebar a {
    color: maroon;
    text-decoration: none;
    display: block;
    padding: 12px 20px;
    border-radius: 10px;
    margin-bottom: 5px;
    transition: background 0.3s;
  }

  .sidebar a:hover, .sidebar a.fw-bold {
    background-color: rgba(139, 0, 0, 0.52);
    color: white;
  }

  .header-card {
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    color: #732015;
    padding: 20px;
    border-radius: 20px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
  }

  .section-title {
    margin: 20px 0 10px;
    font-weight: bold;
    color: #732015;
  }

  .filter-form {
    margin-bottom: 30px;
    display: flex;
    gap: 10px;
    align-items: center;
  }

  .filter-form input,
  .filter-form button {
    border-radius: 10px;
  }

  .filter-form button {
    background: linear-gradient(to right, #e39363, #af3222);
    border: none;
    color: white;
    padding: 5px 15px;
    font-weight: bold;
    box-shadow: 0 2px 6px rgba(0,0,0,0.15);
    transition: background 0.3s ease;
  }

  .filter-form button:hover {
    background: linear-gradient(to right, #af3222, #732015);
  }

  .logo {
    border-radius: 50%;
    width: 80px;
    height: 80px;
    object-fit: cover;
    margin-bottom: 15px;
    align-self: center;
  }

  table {
    border-radius: 10px;
    overflow: hidden;
  }

  canvas {
    background: rgba(255, 255, 255, 0.6);
    border-radius: 10px;
    padding: 10px;
  }

  .top-header {
    background: linear-gradient(to right, #af3222, #732015);
    color: white;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    position: sticky;
    top: 0;
    z-index: 1100;
    border-bottom: 1px solid rgba(255,255,255,0.2);
  }

  .navbar-nav .nav-link {
    color: white !important;
    font-weight: 500;
    transition: opacity 0.3s;
  }

  .navbar-nav .nav-link:hover {
    opacity: 0.8;
  }

  .container-fluid {
    height: 100%;
  }

  .d-flex.flex-row.min-vh-100 {
    height: 100%;
    overflow: hidden;
  }


  #toggleSidebar {
    background: rgba(255,255,255,0.3);
    color: #fff;
    border: none;
    padding: 6px 10px;
    border-radius: 8px;
    transition: background 0.3s;
  }

  #toggleSidebar:hover {
    background: rgba(255,255,255,0.5);
  }

  /* Sidebar hide/show */
  .sidebar-hidden {
    transform: translateX(-100%);
    transition: transform 0.3s ease;
  }

  .sidebar.collapsed {
    width: 0;
    padding: 0;
  }

  .main-content {
    transition: all 0.3s ease;
    min-height: 100vh;
    overflow-x: hidden;
    overflow-y: auto;
    margin-left: 260px;
    width: calc(100% - 260px);
    flex-grow: 1;
    height: 100%;
    padding-bottom: 100px;
  }

  .main-content.expanded {
    margin-left: 0;
    width: 100%;
  }

  

  .container-fluid{
    margin: 0;
    padding: 0;
  }

  .store-name {
    text-align: center;
    width: 100%;
  }

  .glassy {
    background: rgba(255, 255, 255, 0.2);  /* translucent white */
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
    backdrop-filter: blur(10px);           /* main part of the frosted glass */
    -webkit-backdrop-filter: blur(10px);   /* for Safari */
    border: 1px solid rgba(255, 255, 255, 0.3);
    color: #000; /* or white depending on background */
    text-align: center;
  }

  .table {
  background-color: rgba(255, 255, 255, 0.2); /* Translucent white */
  backdrop-filter: blur(10px); /* Frosted glass effect */
  border-radius: 12px;
  box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
}

.table thead {
  background: linear-gradient(to right, #af3222, #732015); /* Gradient header */
  color: white;
  font-weight: bold;
}

.table thead th {
  padding: 14px;
  text-align: center;
}

.table tbody tr {
  transition: background-color 0.3s ease;
}

.table tbody tr:hover {
  background-color: rgba(175, 50, 34, 0.1); /* Light hover effect */
}

.table td {
  text-align: center;
  padding: 12px;
  vertical-align: middle;
}

.table td:last-child {
  text-align: center;
}

.btn-action {
  border-radius: 8px;
  padding: 6px 12px;
  font-size: 0.9rem;
  margin-right: 5px;
}

.btn-edit {
  background-color: #e39363;
  color: white;
  border: none;
  transition: background-color 0.3s;
}

.btn-edit:hover {
  background-color: #af3222;
}

.btn-delete {
  background-color: #af3222;
  color: white;
  border: none;
  transition: background-color 0.3s;
}

.btn-delete:hover {
  background-color: #732015;
}

.table-light {
  background-color: rgba(255, 255, 255, 0.3);
  backdrop-filter: blur(10px);
  border-radius: 8px;
  padding: 10px;
}


    </style>
</head>
<body>

<div class="container-fluid">
  <!-- Top Navigation Header -->
  <nav class="top-header navbar navbar-expand-lg" id="topNavbar">
    <div class="container-fluid d-flex justify-content-between align-items-center px-4 py-2">
      <button class="btn btn-light me-3" id="toggleSidebar">
        <i class="fas fa-bars"></i>
      </button>
      <span class="fs-4 fw-bold text-white">PRODUCT MANAGEMENT</span>
      <ul class="navbar-nav flex-row gap-3 align-items-center">
        <li class="nav-item"><a class="nav-link text-white" href="#"><i class="fas fa-user"></i> Profile</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="#"><i class="fas fa-info-circle"></i> About</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="owner_settings.php"><i class="fas fa-cog"></i> Settings</a></li>
      </ul>
    </div>
  </nav>

  <div class="d-flex flex-row min-vh-100">
    <!-- Sidebar -->
    <div class="sidebar d-flex flex-column" id="sidebar">
      <img src="logo.png" alt="StoreStock Logo" class="logo">
        <div class = "store-name">
          <h4><?php echo $storeName; ?></h4>
        </div>
      <a href="owner_dashboard.php"><i class="fas fa-boxes"></i> Dashboard</a>
      <a href="owner_inventory.php"><i class="fas fa-chart-line"></i> Inventory</a>
      <a href="owner_product.php" class="fw-bold"><i class="fas fa-tags"></i> Products</a>
      <a href="owner_customer.php"><i class="fas fa-users"></i> Customers</a>
      <a href="owner_order.php"><i class="fas fa-receipt"></i> Orders</a>
      <a href="owner_supplier.php"><i class="fas fa-truck"></i> Suppliers</a>
      <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main-content p-4 flex-grow-1" id="mainContent">
    
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
      <table class="table table-bordered table-striped" style="margin-bottom: 100px;">
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
                <button class="btn btn-sm btn-edit btn-action"
                        data-id="<?= $product['productID'] ?>"
                        data-name="<?= htmlspecialchars($product['productName'], ENT_QUOTES) ?>"
                        data-category="<?= htmlspecialchars($product['category'], ENT_QUOTES) ?>"
                        data-cost="<?= $product['costPrice'] ?>"
                        data-price="<?= $product['sellingPrice'] ?>"
                        data-supplierid="<?= $product['supplierID'] ?>"
                        data-description="<?= htmlspecialchars($product['description'] ?? '', ENT_QUOTES) ?>">
                  ✏️
                </button>
                <button class="btn btn-sm btn-delete btn-action"
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
  <div class="modal-dialog modal-lg modal-dialog-centered">
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

</div>

  <!-- Delete Confirmation Modal -->
  <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  function confirmDelete(productID, productName) {
    document.getElementById('deleteProductID').value = productID;
    document.getElementById('deleteProductName').textContent = productName;

    var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
  }
</script>

<!-- SIDE BAR -->
<script>
  document.getElementById("toggleSidebar").addEventListener("click", function () {
    const sidebar = document.getElementById("sidebar");
    const mainContent = document.getElementById("mainContent");

    sidebar.classList.toggle("collapsed");
    mainContent.classList.toggle("expanded");
  });
</script>

</body>
</html>
