<?php
include __DIR__ . '/connect.php';

session_start();

// Check if user is logged in and get their ID
if (!isset($_SESSION['userID'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
}
$currentUserID = $_SESSION['userID'];

// Initialize variables and error messages
$fullName = "";
$contactNumber = "";
$fullName_error = "";
$contactNumber_error = "";
$add_customer_error = "";
$add_customer_success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate Full Name
    if (empty(trim($_POST["fullName"]))) {
        $fullName_error = "Full Name is required";
    } else {
        $fullName = trim($_POST["fullName"]);
        if (!preg_match("/^[a-zA-Z ]*$/", $fullName)) {
            $fullName_error = "Only letters and white space allowed";
        }
    }

    // Validate Contact Number
    if (empty(trim($_POST["contactNumber"]))) {
        $contactNumber_error = "Contact Number is required";
    } else {
        $contactNumber = trim($_POST["contactNumber"]);
        if (!preg_match("/^[0-9]{10}$/", $contactNumber)) { // Assuming 10-digit number
            $contactNumber_error = "Invalid contact number format. Must be 10 digits.";
        }
    }

    // If no errors, proceed to add customer
    if (empty($fullName_error) && empty($contactNumber_error)) {
        $sql = "INSERT INTO tblcustomer (ownerID, fullName, contactNumber, lastPurchaseDate, createdAt) VALUES (?, ?, ?, CURRENT_DATE, CURRENT_DATE)";
        $stmt = $connection->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("iss", $currentUserID, $fullName, $contactNumber);
            if ($stmt->execute()) {
                $add_customer_success = true;
                $fullName = ""; // Clear input fields after success
                $contactNumber = "";
            } else {
                $add_customer_error = "Error adding customer: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $add_customer_error = "Error preparing statement: " . $connection->error;
        }
    }
}

// Fetch customers for the current user along with their total debt
$customers = [];
$sql_customers = "SELECT
    customerID,
    fullName,
    contactNumber,
    totalDebt
FROM
    tblcustomer
WHERE
    ownerID = ?";
$stmt_customers = $connection->prepare($sql_customers);
if ($stmt_customers) {
    $stmt_customers->bind_param("i", $currentUserID);
    $stmt_customers->execute();
    $result_customers = $stmt_customers->get_result();
    if ($result_customers->num_rows > 0) {
        while ($row = $result_customers->fetch_assoc()) {
            $customers[] = $row;
        }
    }
    $stmt_customers->close();
} else {
    // Handle error fetching customers
    $add_customer_error = "Error fetching customer list: " . $connection->error;
}

// Replace with actual user authentication logic to get the user's name
$userName = $_SESSION['firstname'] ?? "User"; // Example user name
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Management - StoreStock</title>
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
        <div class="col-md-2 sidebar d-flex flex-column p-3">
            <img src="logo.png" alt="StoreStock Logo" class="logo">
            <h4 class="text-white">STORESTOCK</h4>
            <a href="owner_dashboard.php">Dashboard</a>
            <a href="owner_inventory.php">Inventory</a>
            <a href="owner_product.php">Products</a>
            <a href="owner_customer.php" class="fw-bold">Customers</a>
            <a href="owner_orders.php">Orders</a>
            <a href="owner_supplier.php">Suppliers</a>
            <a href="owner_settings.php">Settings</a>
            <a href="logout.php">Logout</a>
        </div>

        <div class="col-md-10 p-4">
            <h3 class="mb-4">Customer Management</h3>

            <div class="mb-5">
                <h4>Add New Customer</h4>
                <?php if ($add_customer_success): ?>
                    <div class="alert alert-success">Customer added successfully!</div>
                <?php endif; ?>
                <?php if (!empty($add_customer_error)): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($add_customer_error); ?></div>
                <?php endif; ?>
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="row">
                        <div class="col-md-4">
                            <label for="fullName" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="fullName" name="fullName" value="<?php echo htmlspecialchars($fullName); ?>" required>
                            <?php if (!empty($fullName_error)): ?>
                                <div class="error-message"><?php echo htmlspecialchars($fullName_error); ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-4">
                            <label for="contactNumber" class="form-label">Contact Number</label>
                            <input type="text" class="form-control" id="contactNumber" name="contactNumber" value="<?php echo htmlspecialchars($contactNumber); ?>" required>
                            <?php if (!empty($contactNumber_error)): ?>
                                <div class="error-message"><?php echo htmlspecialchars($contactNumber_error); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-success">➕ Add Customer</button>
                    </div>
                </form>
            </div>

            <h4 class="mt-4">Customer List</h4>
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
                        <?php foreach ($customers as $customer): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($customer['fullName']); ?></td>
                                <td><?php echo htmlspecialchars($customer['contactNumber']); ?></td>
                                <td>₱<?php echo number_format($customer['totalDebt'], 2); ?></td>
                                <td>
                                    <a href="customer_edit.php?id=<?php echo $customer['customerID']; ?>" class="btn btn-sm btn-warning">✏️</a>
                                    <a href="customer_delete.php?id=<?php echo $customer['customerID']; ?>" class="btn btn-sm btn-danger">🗑️</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4">No customers added yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>