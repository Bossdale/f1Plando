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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #f8f9fa; }
        .sidebar { height: 100vh; background-color: rgb(115, 32, 21); color: white; }
        .sidebar a { color: white; text-decoration: none; padding: 10px 20px; display: block; }
        .sidebar a:hover { background-color: #af3222; }
        .logo { border-radius: 50%; width: 100px; height: 100px; object-fit: cover; }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 sidebar d-flex flex-column p-3">
            <img src="logo.png" alt="StoreStock Logo" class="logo">
            <h4>STORESTOCK</h4>
            <a href="owner_dashboard.php">Dashboard</a>
            <a href="owner_inventory.php">Inventory</a>
            <a href="owner_product.php">Products</a>
            <a href="owner_customer.php" class="fw-bold">Customers</a>
            <a href="owner_order.php">Orders</a>
            <a href="owner_supplier.php">Suppliers</a>
            <a href="owner_settings.php">Settings</a>
            <a href="logout.php">Logout</a>
        </div>
        <div class="col-md-10 p-4">
            <h3 class="mb-4">Customer Management</h3>

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

</body>
</html>
