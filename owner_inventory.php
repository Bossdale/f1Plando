<?php
    include __DIR__ . '/connect.php';
    session_start();

    if (!$connection) {
        die("Database connection failed: " . mysqli_connect_error());
    }
    // Example logic: fetch current logged-in owner's ID (this should be from session ideally)
    $ownerID = $_SESSION['userID'];; // hardcoded for demonstration

    // Fetch inventory records
    $inventoryQuery = $connection->prepare("SELECT p.productName, i.quantity, i.unit, i.lastUpdated FROM tblInventory i 
        JOIN tblProduct p ON i.productID = p.productID WHERE i.ownerID = ?");
    $inventoryQuery->bind_param("i", $ownerID);
    $inventoryQuery->execute();
    $inventoryResult = $inventoryQuery->get_result();

    // Store fetched data
    $inventoryData = [];
    while ($row = $inventoryResult->fetch_assoc()) {
        $inventoryData[] = $row;
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Inventory - StoreStock</title>
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
      <a href="owner_inventory.php" class="fw-bold">Inventory</a>
      <a href="owner_product.php">Products</a>
      <a href="owner_customer.php">Customers</a>
      <a href="owner_orders.php">Orders</a>
      <a href="owner_supplier.php">Suppliers</a>
      <a href="owner_settings.php">Settings</a>
      <a href="logout.php">Logout</a>
    </div>

    <!-- Inventory Table -->
    <div class="col-md-10 p-4">
      <h3 class="mb-4">Inventory</h3>
      <table class="table table-bordered table-striped">
        <thead class="table-light">
          <tr>
            <th>Product</th>
            <th>Quantity</th>
            <th>Unit</th>
            <th>Last Updated</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($inventoryData as $item): ?>
            <tr>
              <td><?php echo htmlspecialchars($item['productName']); ?></td>
              <td><?php echo $item['quantity']; ?></td>
              <td><?php echo htmlspecialchars($item['unit']); ?></td>
              <td><?php echo $item['lastUpdated']; ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</body>
</html>