<?php
// Include the database connection
include __DIR__ . '/connect.php';
session_start();

// Check if supplierID is set in the session
if (!isset($_SESSION['supplierID'])) {
    die("Access denied: Supplier not logged in.");
}

// Get the supplierID from session
$supplierID = $_SESSION['supplierID'];

// Fetch linked stores and their supplier delivery data
$storeQuery = "
  SELECT 
    o.storeName,
    u.firstname,
    u.lastname,
    u.contactNum,
    u.address,
    MAX(s.lastDeliveryDate) AS lastDeliveryDate
  FROM tblowner o
  JOIN tbluser u ON o.userID = u.userID
  LEFT JOIN tblinventory i ON o.ownerID = i.ownerID
  LEFT JOIN tblproduct p ON i.productID = p.productID
  LEFT JOIN tblsupplier s ON p.supplierID = s.supplierID
  WHERE s.supplierID = ?  -- Filter by supplierID
  GROUP BY o.storeName, u.firstname, u.lastname, u.contactNum, u.address
";

$stmt = $connection->prepare($storeQuery);
$stmt->bind_param("i", $supplierID);
$stmt->execute();
$storeResult = $stmt->get_result();
$stores = [];
while ($row = mysqli_fetch_assoc($storeResult)) {
    $stores[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Linked Stores - StoreStock</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #f8f9fa;
    }
    .sidebar {
      height: 100vh;
      background-color: rgb(115, 32, 21);
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
      <a href="supplier_stores.php">🏬 Linked Stores</a>
      <a href="supplier_settings.php">⚙️ Settings</a>
      <a href="logout.php">🔓 Logout</a>
    </div>

    <!-- Main Content -->
    <div class="col-md-10 p-4">
      <h3 class="mb-4">Linked Stores</h3>

      <!-- Stores Table -->
      <table class="table table-bordered table-striped">
        <thead class="table-light">
          <tr>
            <th>Store Name</th>
            <th>Store Owner</th>
            <th>Contact Number</th>
            <th>Address</th>
            <th>Last Delivery</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($stores as $store): ?>
            <tr>
              <td><?= htmlspecialchars($store['storeName']) ?></td>
              <td><?= htmlspecialchars($store['firstname'] . ' ' . $store['lastname']) ?></td>
              <td><?= htmlspecialchars($store['contactNum']) ?></td>
              <td><?= htmlspecialchars($store['address']) ?></td>
              <td><?= $store['lastDeliveryDate'] ? htmlspecialchars($store['lastDeliveryDate']) : 'N/A' ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
