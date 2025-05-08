<?php
include __DIR__ . '/connect.php';
session_start();

if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}
$currentUserID = $_SESSION['userID'];

$customers = [];
$sql_customers = "SELECT customerID, fullName, contactNumber, totalDebt FROM tblcustomer WHERE ownerID = ?";
$stmt_customers = $connection->prepare($sql_customers);
if ($stmt_customers) {
    $stmt_customers->bind_param("i", $currentUserID);
    $stmt_customers->execute();
    $result = $stmt_customers->get_result();
    while ($row = $result->fetch_assoc()) {
        $customers[] = $row;
    }
    $stmt_customers->close();
}

$query = "SELECT storeName FROM tblOwner";
    $result = mysqli_query($connection, $query);
    if ($result) {
    $row = mysqli_fetch_assoc($result);
    $storeName= $row['storeName'];
    }

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>STORESTOCK Inventory</title>
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
    padding: 1rem;
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

  .inventory-table-wrapper {
    background: rgba(255, 255, 255, 0.4);
    backdrop-filter: blur(10px);
    border-radius: 15px;
    padding: 20px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    overflow-x: auto;
  }

  .inventory-table {
    border-collapse: separate;
    border-spacing: 0;
    width: 100%;
    font-size: 15px;
    border-radius: 15px;
    overflow: hidden;
    background-color: rgba(255, 255, 255, 0.8);
  }

  .inventory-table th {
    background: linear-gradient(to right, #af3222, #732015);
    color: white;
    padding: 12px;
    text-align: center;
  }

  .inventory-table td {
    background-color: rgba(255, 255, 255, 0.5);
    text-align: center;
    padding: 12px;
    font-weight: 500;
  }

  .inventory-table tbody tr:hover {
    background-color: rgba(255, 230, 230, 0.7);
    transition: background-color 0.3s ease;
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
      <span class="fs-4 fw-bold text-white">CUSTOMER MANAGEMENT</span>
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
      <a href="owner_inventory.php"><i class="fas fa-users"></i> Inventory</a>
      <a href="owner_product.php"><i class="fas fa-tags"></i> Products</a>
      <a href="owner_customer.php" class="fw-bold"><i class="fas fa-chart-line"></i>  Customers</a>
      <a href="owner_orders.php"><i class="fas fa-receipt"></i> Orders</a>
      <a href="owner_supplier.php"><i class="fas fa-truck"></i> Suppliers</a>
      <a href="owner_settings.php"><i class="fas fa-cog"></i> Settings</a>
      <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
    <!-- Main Content -->
    <div class="main-content p-4 flex-grow-1" id="mainContent">
           
            <!-- Add Button -->
            <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addModal">➕ Add Customer</button>

            <!-- Table -->
            <table class="table table-bordered table-striped">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>Total Debt</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($customers)): ?>
                        <?php foreach ($customers as $cust): ?>
                            <tr>
                                <td><?= htmlspecialchars($cust['fullName']) ?></td>
                                <td><?= htmlspecialchars($cust['contactNumber']) ?></td>
                                <td>₱<?= number_format($cust['totalDebt'], 2) ?></td>
                                <td>
                                    <button class="btn btn-sm btn-warning editBtn"
                                            data-id="<?= $cust['customerID'] ?>"
                                            data-name="<?= htmlspecialchars($cust['fullName']) ?>"
                                            data-contact="<?= htmlspecialchars($cust['contactNumber']) ?>"
                                            data-debt="<?= $cust['totalDebt'] ?>"
                                            data-bs-toggle="modal" data-bs-target="#editModal">✏️</button>
                                            <button class="btn btn-sm btn-danger"
                                                onclick="confirmDelete(<?= $cust['customerID'] ?>, '<?= htmlspecialchars($cust['fullName']) ?>')"
                                                data-bs-toggle="modal" data-bs-target="#deleteModal">
                                                🗑️
                                            </button>

                                </td>
                            </tr>
                        <?php endforeach ?>
                    <?php else: ?>
                        <tr><td colspan="4">No customers yet.</td></tr>
                    <?php endif ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Customer Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="post" action="owner_customer_action.php" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add Customer</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label>Full Name</label>
          <input type="text" class="form-control" name="fullName" required>
        </div>
        <div class="mb-3">
          <label>Contact Number</label>
          <input type="text" class="form-control" name="contactNumber" required>
        </div>
        <div class="mb-3">
          <label>Total Debt</label>
          <input type="number" class="form-control" name="totalDebt" min="0" step="0.01" required>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-success" type="submit">Add</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Customer Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="post" action="owner_customer_action.php" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Customer</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="customerID" id="editCustomerID">
        <div class="mb-3">
            <label>Full Name</label>
            <input type="text" class="form-control" name="fullName" id="editFullName" required>
        </div>
        <div class="mb-3">
            <label>Contact Number</label>
            <input type="text" class="form-control" name="contactNumber" id="editContactNumber" required>
        </div>
        <div class="mb-3">
            <label>Total Debt</label>
            <input type="number" step="0.01" class="form-control" name="totalDebt" id="editTotalDebt" required>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-primary" type="submit">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form method="GET" action="owner_customer_action.php" class="modal-content">
      <input type="hidden" name="delete" id="deleteCustomerID">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">Confirm Deletion</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Are you sure you want to permanently delete <strong id="deleteCustomerName"></strong>?
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
document.querySelectorAll('.editBtn').forEach(button => {
    button.addEventListener('click', function () {
        document.getElementById('editCustomerID').value = this.dataset.id;
        document.getElementById('editFullName').value = this.dataset.name;
        document.getElementById('editContactNumber').value = this.dataset.contact;
        document.getElementById('editTotalDebt').value = parseFloat(this.dataset.debt).toFixed(2);
    });
});
</script>

<script>
function confirmDelete(customerID, fullName) {
    document.getElementById('deleteCustomerID').value = customerID;
    document.getElementById('deleteCustomerName').textContent = fullName;
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
