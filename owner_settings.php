<?php
  include __DIR__ . '/connect.php';
  session_start();

  if (!$connection) {
    die("Database connection failed: " . mysqli_connect_error());
  }

  $userID = $_SESSION['userID'];

  // Update logic
  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Update Profile
    if (isset($_POST['update_profile'])) {
      $firstname = $_POST['firstname'];
      $lastname = $_POST['lastname'];
      $email = $_POST['email'];
      $contactNum = $_POST['contactNum'];
      $address = $_POST['address'];

      $stmt = $connection->prepare("UPDATE tblUser SET firstname=?, lastname=?, email=?, contactNum=?, address=? WHERE userID=?");
      $stmt->bind_param("sssssi", $firstname, $lastname, $email, $contactNum, $address, $userID);
      $stmt->execute();

      $_SESSION['alert'] = "Profile updated successfully!";
      header("Location: owner_settings.php");
      exit();
    }

    // Update Store Name
    if (isset($_POST['update_store'])) {
      $storeName = $_POST['storeName'];

      $stmt = $connection->prepare("UPDATE tblOwner SET storeName=? WHERE userID=?");
      $stmt->bind_param("si", $storeName, $userID);
      $stmt->execute();

      $_SESSION['alert'] = "Store name updated successfully!";
      header("Location: owner_settings.php");
      exit();
    }

    // Update Password
    if (isset($_POST['update_password'])) {
      $current = $_POST['currentPassword'];
      $new = $_POST['newPassword'];
      $confirm = $_POST['confirmPassword'];

      $stmt = $connection->prepare("SELECT password FROM tblUser WHERE userID=?");
      $stmt->bind_param("i", $userID);
      $stmt->execute();
      $result = $stmt->get_result();
      $user = $result->fetch_assoc();
      $userPass = $user['password'];
      if ($current === $userPass) {
        if ($new === $confirm) {
          $update = $connection->prepare("UPDATE tblUser SET password=? WHERE userID=?");
          $update->bind_param("si", $new, $userID);
          $update->execute();
          $_SESSION['alert'] = "Password changed successfully!";
        } else {
          echo "<script>alert('New passwords do not match');</script>";
          $_SESSION['alert'] = "New passwords do not match.";
        }
      } else {
        echo "<script>alert('Current password is incorrect');</script>";
        $_SESSION['alert'] = "Current password is incorrect.";
      }
      header("Location: owner_settings.php");
      exit();
    }
  }

  // Fetch user and owner info
  $query = "SELECT u.firstname, u.lastname, u.email, u.contactNum, u.address, o.storeName 
            FROM tblUser u JOIN tblOwner o ON u.userID = o.userID WHERE u.userID = ?";
  $stmt = $connection->prepare($query);
  $stmt->bind_param("i", $userID);
  $stmt->execute();
  $result = $stmt->get_result();
  $profile = $result->fetch_assoc();

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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
      <span class="fs-4 fw-bold text-white">SETTINGS</span>
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
      <a href="owner_product.php"><i class="fas fa-tags"></i> Products</a>
      <a href="owner_customer.php"><i class="fas fa-users"></i> Customers</a>
      <a href="owner_order.php"><i class="fas fa-receipt"></i> Orders</a>
      <a href="owner_supplier.php"><i class="fas fa-truck"></i> Suppliers</a>
      <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main-content p-4 flex-grow-1" id="mainContent">
        <?php if (isset($_SESSION['alert'])): ?>
      <div class="alert alert-info alert-dismissible fade show" role="alert">
        <?php echo $_SESSION['alert']; unset($_SESSION['alert']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

      <h3 class="mb-4">Settings</h3>

      <!-- Profile Management -->
      <form method="POST" class="mb-5">
        <input type="hidden" name="update_profile" value="1">
        <h5 class="section-title">Profile Information</h5>
        <div class="row">
          <div class="col-md-4">
            <label>First Name</label>
            <input type="text" name="firstname" value="<?php echo $profile['firstname']; ?>" class="form-control" required>
          </div>
          <div class="col-md-4">
            <label>Last Name</label>
            <input type="text" name="lastname" value="<?php echo $profile['lastname']; ?>" class="form-control" required>
          </div>
          <div class="col-md-4">
            <label>Email</label>
            <input type="email" name="email" value="<?php echo $profile['email']; ?>" class="form-control" required>
          </div>
        </div>
        <div class="row mt-2">
          <div class="col-md-6">
            <label>Contact Number</label>
            <input type="text" name="contactNum" value="<?php echo $profile['contactNum']; ?>" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label>Address</label>
            <input type="text" name="address" value="<?php echo $profile['address']; ?>" class="form-control" required>
          </div>
        </div>
        <div class="mt-3">
        <button type="submit" class="btn btn-primary" style="background-color: #af3222; border-color: #af3222;">Update Profile</button>
        </div>
      </form>

      <!-- Store Information -->
      <form method="POST" class="mb-5">
        <input type="hidden" name="update_store" value="1">
        <h5 class="section-title">Store Information</h5>
        <div class="row">
          <div class="col-md-6">
            <label>Store Name</label>
            <input type="text" name="storeName" value="<?php echo $profile['storeName']; ?>" class="form-control" required>
          </div>
        </div>
        <div class="mt-3">
          <button type="submit" class="btn btn-success" style="background-color: #af3222; border-color: #af3222;">Update Store</button>
        </div>
      </form>

      <!-- Change Password -->
      <form method="POST" style="margin-bottom: 100px;">
        <input type="hidden" name="update_password" value="1">
        <h5 class="section-title">Change Password</h5>
        <div class="row">
          <div class="col-md-4">
            <label>Current Password</label>
            <input type="password" name="currentPassword" class="form-control" required>
          </div>
          <div class="col-md-4">
            <label>New Password</label>
            <input type="password" name="newPassword" class="form-control" required>
          </div>
          <div class="col-md-4">
            <label>Confirm New Password</label>
            <input type="password" name="confirmPassword" class="form-control" required>
          </div>
        </div>
        <div class="mt-3">
          <button type="submit" class="btn btn-danger" style="background-color: #af3222; border-color: #af3222;">Update Password</button>
        </div>
      </form>
    </div>
  </div>
</div>

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
