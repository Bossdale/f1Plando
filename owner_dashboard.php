<?php
    include __DIR__ . '/connect.php';
    include __DIR__ . '/readrecords.php';

    // Replace with actual user authentication logic to get the user's name
    $userName = "User"; // Example user name
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>STORESTOCK Dashboard</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #f8f9fa;
    }
    .sidebar {
      height: parent;
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
    .filter-form {
      margin-bottom: 50px;
      display: flex;
      gap: 10px;
      align-items: center;
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
    <div class="col-md-2 sidebar d-flex flex-column p-3">
      <img src="logo.png" alt="StoreStock Logo" class="logo">
      <h4 class="text-white">STORESTOCK</h4>
      <a href="owner_dashboard.php">Dashboard</a>
      <a href="inventory_owner.php">Inventory</a>
      <a href="#">Products</a>
      <a href="customer.php">Customers</a>
      <a href="#">Orders</a>
      <a href="#">Debts</a>
      <a href="#">Suppliers</a>
      <a href="#">Reports</a>
      <a href="#">Settings</a>
      <a href="#">Logout</a>
    </div>

    <div class="col-md-10 p-4">
      <div class="mb-3">
        <h3>Hello, <?php echo $firstName; ?>!</h3>
      </div>
      <form class="filter-form">
        <label for="fromDate">Date From:</label>
        <input type="date" class="form-control form-control-sm" id="fromDate" name="fromDate">
        <label for="toDate">Date To:</label>
        <input type="date" class="form-control form-control-sm" id="toDate" name="toDate">
        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
      </form>

      <div class="row mb-4">
        <div class="col-md-3">
          <div class="header-card text-center">
            <h5>Total Products</h5>
            <h3 id="totalProducts"><?php echo $totalProducts; ?></h3>
          </div>
        </div>
        <div class="col-md-3">
          <div class="header-card text-center">
            <h5>Total Sales</h5>
            <h3 id="totalSales">₱<?php echo number_format($totalSales, 2); ?></h3>
          </div>
        </div>
        <div class="col-md-3">
          <div class="header-card text-center">
            <h5>Outstanding Debts</h5>
            <h3 id="totalDebts">₱<?php echo number_format($totalDebts, 2); ?></h3>
          </div>
        </div>
        <div class="col-md-3">
          <div class="header-card text-center">
            <h5>Total Customers</h5>
            <h3 id="totalCustomers"><?php echo $totalCustomers; ?></h3>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6">
          <h6 class="section-title">Low Stock Products</h6>
          <table class="table table-bordered">
            <thead class="table-light">
              <tr><th>Product</th><th>Quantity</th></tr>
            </thead>
            <tbody id="lowStockTable">
              <?php foreach($lowStockProducts as $product): ?>
                <tr>
                  <td><?php echo htmlspecialchars($product['name']); ?></td>
                  <td><?php echo $product['quantity']; ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <div class="col-md-6">
          <h6 class="section-title">Recent Orders</h6>
          <table class="table table-bordered">
            <thead class="table-light">
              <tr><th>Customer</th><th>Total</th><th>Status</th></tr>
            </thead>
            <tbody id="recentOrdersTable">
              <?php foreach($recentOrders as $order): ?>
                <tr>
                  <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                  <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>
                  <td><?php echo $order['status']; ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div class="row mt-4">
        <div class="col-md-6">
          <h6 class="section-title">Sales Overview</h6>
          <canvas id="salesChart"></canvas>
        </div>
        <div class="col-md-6">
          <h6 class="section-title">Top Products</h6>
          <canvas id="productChart"></canvas>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  // Charts data from PHP
  const salesData = <?php echo json_encode($salesData); ?>;
  const topProductsData = <?php echo json_encode($topProductsData); ?>;

  // Sales Chart
  const salesCtx = document.getElementById('salesChart').getContext('2d');
  new Chart(salesCtx, {
    type: 'bar',
    data: {
      labels: salesData.labels,
      datasets: [{
        label: 'Sales (₱)',
        data: salesData.values,
        backgroundColor: '#e39363'
      }]
    },
    options: { responsive: true }
  });

  // Products Chart
  const productCtx = document.getElementById('productChart').getContext('2d');
  new Chart(productCtx, {
    type: 'pie',
    data: {
      labels: topProductsData.labels,
      datasets: [{
        data: topProductsData.values,
        backgroundColor: ['#f6e2be', '#e39363', '#af3222', '#732015']
      }]
    },
    options: { responsive: true }
  });
</script>
</body>
</html>