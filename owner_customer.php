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
    fullName,
    contactNumber,
    totalDebt
FROM
    tblcustomer
WHERE
    ownerID = ?
GROUP BY
    fullName, contactNumber, totalDebt";
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
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Add New Customer - STORESTOCK</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f8f9fa;
        }
        .sidebar {
            height: 100vh; /* Make sidebar full height */
            background-color: rgb(115, 32, 21);
            color: white;
            position: fixed; /* Fixed position to stay in view */
            top: 0;
            left: 0;
            width: 300px; /* Inherit width from col-md-2 */
        }
        .sidebar .logo {
            border-radius: 50%;
            width: 100px;
            height: 100px;
            object-fit: cover;
            margin: 20px auto; /* Center logo */
            display: block;
        }
        /* shesh */
        .sidebar h4 {
            text-align: center;
            margin-bottom: 20px;
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

        .form-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .error-message {
            color: red;
            font-size: 0.875rem;
            margin-top: 5px;
        }
        .success-message {
            color: green;
            font-weight: bold;
            margin-bottom: 15px;
        }
        .customer-list-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .customer-list-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .customer-list-table th, .customer-list-table td {
            border: 1px solid #dee2e6;
            padding: 8px;
            text-align: left;
        }
        .customer-list-table th {
            background-color: #f8f9fa;
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
            <a href="owner_product.php">Products</a>
            <a href="owner_customer.php">Customers</a>
            <a href="#">Orders</a>
            <a href="#">Debts</a>
            <a href="owner_supplier.php">Suppliers</a>
            <a href="#">Reports</a>
            <a href="#">Settings</a>
            <a href="#">Logout</a>
        </div>

        <div class="col-md-10 p-4 offset-md-2">
            <div class="mb-3">
                <h3>Add New Customer</h3>
            </div>

            <div class="form-container">
                <?php if ($add_customer_success): ?>
                    <div class="success-message">Customer added successfully!</div>
                <?php endif; ?>
                <?php if (!empty($add_customer_error)): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($add_customer_error); ?></div>
                <?php endif; ?>
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="form-group">
                        <label for="fullName" class="form-label">Full Name:</label>
                        <input type="text" class="form-control" id="fullName" name="fullName" value="<?php echo htmlspecialchars($fullName); ?>">
                        <?php if (!empty($fullName_error)): ?>
                            <div class="error-message"><?php echo htmlspecialchars($fullName_error); ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="contactNumber" class="form-label">Contact Number:</label>
                        <input type="text" class="form-control" id="contactNumber" name="contactNumber" value="<?php echo htmlspecialchars($contactNumber); ?>">
                        <?php if (!empty($contactNumber_error)): ?>
                            <div class="error-message"><?php echo htmlspecialchars($contactNumber_error); ?></div>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Customer</button>
                </form>
            </div>

            <div class="customer-list-container">
                <h6 class="section-title">Your Customers</h6>
                <?php if (!empty($customers)): ?>
                    <table class="customer-list-table">
                        <thead>
                            <tr>
                                <th>Full Name</th>
                                <th>Contact Number</th>
                                <th>Total Debt</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($customers as $customer): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($customer['fullName']); ?></td>
                                    <td><?php echo htmlspecialchars($customer['contactNumber']); ?></td>
                                    <td>₱<?php echo number_format($customer['totalDebt'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No customers added yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>