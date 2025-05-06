<!-- Keep the PHP part as-is -->
<?php
    include __DIR__ . '/connect.php';
    include __DIR__ . '/readrecords.php';
    $userName = "User"; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>STORESTOCK Dashboard</title>
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
      <span class="fs-4 fw-bold text-white">DASHBOARD</span>
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
      <a href="owner_dashboard.php" class="fw-bold"><i class="fas fa-chart-line"></i> Dashboard</a>
      <a href="owner_inventory.php"><i class="fas fa-boxes"></i> Inventory</a>
      <a href="owner_product.php"><i class="fas fa-tags"></i> Products</a>
      <a href="owner_customer.php"><i class="fas fa-users"></i> Customers</a>
      <a href="owner_orders.php"><i class="fas fa-receipt"></i> Orders</a>
      <a href="owner_supplier.php"><i class="fas fa-truck"></i> Suppliers</a>
      <a href="owner_settings.php"><i class="fas fa-cog"></i> Settings</a>
      <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main-content p-4 flex-grow-1" id="mainContent">
      <div class="mb-3">
        <h3 class="fw-bold text-dark">Welcome, <?php echo $firstName; ?>!</h3>
      </div>

      <form class="filter-form">
        <label for="fromDate">Date From:</label>
        <input type="date" class="form-control form-control-sm" id="fromDate" name="fromDate">
        <label for="toDate">Date To:</label>
        <input type="date" class="form-control form-control-sm" id="toDate" name="toDate">
        <button type="submit" class="btn btn-sm btn-outline-primary">Filter</button>
      </form>

      <div class="row g-3 mb-4">
        <div class="col-md-3">
          <div class="header-card text-center">
            <h5>Total Products</h5>
            <h3><?php echo $totalProducts; ?></h3>
          </div>
        </div>
        <div class="col-md-3">
          <div class="header-card text-center">
            <h5>Total Sales</h5>
            <h3>₱<?php echo number_format($totalSales, 2); ?></h3>
          </div>
        </div>
        <div class="col-md-3">
          <div class="header-card text-center">
            <h5>Outstanding Debts</h5>
            <h3>₱<?php echo number_format($totalDebts, 2); ?></h3>
          </div>
        </div>
        <div class="col-md-3">
          <div class="header-card text-center">
            <h5>Total Customers</h5>
            <h3><?php echo $totalCustomers; ?></h3>
          </div>
        </div>
      </div>

      <!-- Low Stock Table -->
      <div class="row g-4">
        <div class="col-md-6">
          <h6 class="section-title">Low Stock Products</h6>
          <table class="table table-bordered">
            <thead><tr><th>Product</th><th>Quantity</th></tr></thead>
            <tbody>
              <?php if (!empty($lowStockProducts)): ?>
                <?php foreach($lowStockProducts as $product): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                    <td><?php echo $product['quantity']; ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr><td colspan="2" class="text-center">No low stock products.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <div class="col-md-6">
          <h6 class="section-title">Recent Orders</h6>
          <table class="table table-bordered">
            <thead>
              <tr><th>Customer</th><th>Total</th><th>Status</th></tr>
            </thead>
            <tbody>
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

      <div class="row g-4 mt-4">
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
  const salesData = <?php echo json_encode($salesData); ?>;
  const topProductsData = <?php echo json_encode($topProductsData); ?>;

  // Sales Overview Chart
  new Chart(document.getElementById('salesChart'), {
    type: 'line',
    data: {
      labels: salesData.labels,
      datasets: [{
        label: 'Sales',
        data: salesData.values,
        fill: true,
        borderColor: '#af3222',
        backgroundColor: 'rgba(175, 50, 34, 0.2)',
        tension: 0.4
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          labels: {
            color: '#732015'
          }
        }
      },
      scales: {
        x: {
          ticks: { color: '#732015' }
        },
        y: {
          ticks: { color: '#732015' }
        }
      }
    }
  });

  // Top Products Chart
  new Chart(document.getElementById('productChart'), {
    type: 'bar',
    data: {
      labels: topProductsData.labels,
      datasets: [{
        label: 'Sold Quantity',
        data: topProductsData.values,
        backgroundColor: '#e39363',
        borderColor: '#af3222',
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          labels: {
            color: '#732015'
          }
        }
      },
      scales: {
        x: {
          ticks: { color: '#732015' }
        },
        y: {
          ticks: { color: '#732015' }
        }
      }
    }
  });
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
