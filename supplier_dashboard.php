<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Session check
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'supplier') {

    header("Location: signin.php");
    exit();
}

include __DIR__ . '/connect.php';
include __DIR__ . '/supplier_dashboard_data.php';

$firstName = $_SESSION['firstname'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Supplier Dashboard</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
      text-decoration: none;
      padding: 10px 20px;
      display: block;
    }
    .sidebar a:hover {
      background-color: #af3222;
    }
    .header-card {
      background-color: #e39363;
      color: white;
      padding: 20px;
      border-radius: 10px;
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
    <div class="col-md-2 sidebar p-3">
      <img src="logo.png" class="logo mb-3" alt="Logo">
      <h5><?php echo htmlspecialchars($firstName); ?></h5>
      <a href="supplier_dashboard.php">🏠 Dashboard</a>
      <a href="supplier_products.php">📦 My Products</a>
      <a href="supplier_delivery.php">🚚 Deliveries</a>
      <a href="linked_stores.php">🏬 Linked Stores</a>
      <a href="supplier_reports.php">📊 Reports</a>
      <a href="settings.php">⚙️ Settings</a>
      <a href="logout.php">🔓 Logout</a>
    </div>

    <!-- Content -->
    <div class="col-md-10 p-4">
      <h3>Welcome, <?php echo htmlspecialchars($firstName); ?>!</h3>

      <!-- Header Cards -->
      <div class="row mb-4">
        <div class="col-md-4">
          <div class="header-card text-center">
            <h5>Total Products Supplied</h5>
            <h3><?php echo $totalSuppliedProducts; ?></h3>
          </div>
        </div>
        <div class="col-md-4">
          <div class="header-card text-center">
            <h5>Total Stores Served</h5>
            <h3><?php echo $totalStores; ?></h3>
          </div>
        </div>
        <div class="col-md-4">
          <div class="header-card text-center">
            <h5>Last Delivery</h5>
            <h3><?php echo $lastDeliveryDate; ?></h3>
          </div>
        </div>
      </div>

      <!-- Tables -->
      <div class="row">
        <div class="col-md-6">
          <h6 class="section-title">Recent Deliveries</h6>
          <table class="table table-bordered">
            <thead class="table-light">
              <tr><th>Date</th><th>Store</th><th>Items</th></tr>
            </thead>
            <tbody>
              <?php foreach ($recentDeliveries as $delivery): ?>
              <tr>
                <td><?php echo htmlspecialchars($delivery['delivery_date']); ?></td>
                <td><?php echo htmlspecialchars($delivery['store_name']); ?></td>
                <td><?php echo htmlspecialchars($delivery['items']); ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <div class="col-md-6">
          <h6 class="section-title">Most Delivered Products</h6>
          <table class="table table-bordered">
            <thead class="table-light">
              <tr><th>Product</th><th>Quantity</th></tr>
            </thead>
            <tbody>
              <?php foreach ($topDeliveredProducts as $product): ?>
              <tr>
                <td><?php echo htmlspecialchars($product['name']); ?></td>
                <td><?php echo htmlspecialchars($product['quantity']); ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Charts -->
      <div class="row mt-4">
        <div class="col-md-6">
          <h6 class="section-title">Delivery Volume Over Time</h6>
          <canvas id="deliveryChart"></canvas>
        </div>
        <div class="col-md-6">
          <h6 class="section-title">Top Stores by Quantity</h6>
          <canvas id="storeChart"></canvas>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  const deliveryData = <?php echo json_encode($deliveryChartData); ?>;
  const storeData = <?php echo json_encode($topStoresData); ?>;

  new Chart(document.getElementById('deliveryChart'), {
    type: 'line',
    data: {
      labels: deliveryData.labels,
      datasets: [{
        label: 'Delivered Items',
        data: deliveryData.values,
        backgroundColor: '#e39363',
        borderColor: '#af3222',
        fill: false,
        tension: 0.4
      }]
    },
    options: { responsive: true }
  });

  new Chart(document.getElementById('storeChart'), {
    type: 'bar',
    data: {
      labels: storeData.labels,
      datasets: [{
        label: 'Total Quantity',
        data: storeData.values,
        backgroundColor: '#732015'
      }]
    },
    options: { responsive: true }
  });
</script>
</body>
</html>
