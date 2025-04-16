<?php
  include __DIR__ . '/connect.php';
  session_start();

  if (!$connection) {
    die("Database connection failed: " . mysqli_connect_error());
  }

  $userID = $_SESSION['userID'];

  // Fetch user and owner info
  $query = "SELECT u.firstname, u.lastname, u.email, u.contactNum, u.address, o.storeName 
            FROM tblUser u JOIN tblOwner o ON u.userID = o.userID WHERE u.userID = ?";
  $stmt = $connection->prepare($query);
  $stmt->bind_param("i", $userID);
  $stmt->execute();
  $result = $stmt->get_result();
  $profile = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Settings - StoreStock</title>
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
      <a href="inventory_owner.php">Inventory</a>
      <a href="owner_product.php">Products</a>
      <a href="#">Customers</a>
      <a href="#">Orders</a>
      <a href="#">Debts</a>
      <a href="owner_supplier.php">Suppliers</a>
      <a href="#">Reports</a>
      <a href="#" class="fw-bold">Settings</a>
      <a href="#">Logout</a>
    </div>

    <!-- Main Content -->
    <div class="col-md-10 p-4">
      <h3 class="mb-4">Settings</h3>

      <!-- Profile Management -->
      <form action="update_profile.php" method="POST" class="mb-5">
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
          <button type="submit" class="btn btn-primary">Update Profile</button>
        </div>
      </form>

      <!-- Store Information -->
      <form action="update_store.php" method="POST" class="mb-5">
        <h5 class="section-title">Store Information</h5>
        <div class="row">
          <div class="col-md-6">
            <label>Store Name</label>
            <input type="text" name="storeName" value="<?php echo $profile['storeName']; ?>" class="form-control" required>
          </div>
        </div>
        <div class="mt-3">
          <button type="submit" class="btn btn-success">Update Store</button>
        </div>
      </form>

      <!-- Change Password -->
      <form action="update_password.php" method="POST">
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
          <button type="submit" class="btn btn-danger">Update Password</button>
        </div>
      </form>
    </div>
  </div>
</div>
</body>
</html>
