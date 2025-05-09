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
    header("Location: supplier_settings.php");
    exit();
  }

  // Update Supplier Info
  if (isset($_POST['update_supplier'])) {
    $companyName = $_POST['companyName'];
    $contactPerson = $_POST['contactPerson'];

    $stmt = $connection->prepare("UPDATE tblSupplier SET companyName=?, contactPerson=? WHERE userID=?");
    $stmt->bind_param("ssi", $companyName, $contactPerson, $userID);
    $stmt->execute();

    $_SESSION['alert'] = "Supplier info updated successfully!";
    header("Location: supplier_settings.php");
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
        $_SESSION['alert'] = "New passwords do not match.";
      }
    } else {
      $_SESSION['alert'] = "Current password is incorrect.";
    }
    header("Location: supplier_settings.php");
    exit();
  }
}

// Fetch user and supplier info
$query = "SELECT u.firstname, u.lastname, u.email, u.contactNum, u.address, s.companyName, s.contactPerson 
          FROM tblUser u 
          JOIN tblSupplier s ON u.userID = s.userID 
          WHERE u.userID = ?";
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
    <div class="col-md-2 sidebar p-3">
      <img src="logo.png" class="logo mb-3" alt="Logo">
      <a href="supplier_dashboard.php">🏠 Dashboard</a>
      <a href="supplier_products.php">📦 My Products</a>
      <a href="supplier_stores.php">🏬 Linked Stores</a>
      <a href="supplier_settings.php">⚙️ Settings</a>
      <a href="logout.php">🔓 Logout</a>
    </div>

    <!-- Main Content -->
    <div class="col-md-10 p-4">
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
          <button type="submit" class="btn btn-primary">Update Profile</button>
        </div>
      </form>

      <!-- Supplier Information -->
      <form method="POST" class="mb-5">
        <input type="hidden" name="update_supplier" value="1">
        <h5 class="section-title">Supplier Information</h5>
        <div class="row">
          <div class="col-md-6">
            <label>Company Name</label>
            <input type="text" name="companyName" value="<?php echo $profile['companyName']; ?>" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label>Contact Person</label>
            <input type="text" name="contactPerson" value="<?php echo $profile['contactPerson']; ?>" class="form-control" required>
          </div>
        </div>
        <div class="mt-3">
          <button type="submit" class="btn btn-success">Update Supplier</button>
        </div>
      </form>

      <!-- Change Password -->
      <form method="POST">
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
          <button type="submit" class="btn btn-danger">Update Password</button>
        </div>
      </form>
    </div>
  </div>
</div>
</body>
</html>
