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
        .error-message {
            color: red;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
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
                        <!-- View button -->
                        <button class="btn btn-info view-customer-btn" data-customer-id="<?php echo $customer['customerID']; ?>" data-bs-toggle="modal" data-bs-target="#customerModal">View</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="4">No customers added yet.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<!-- Modal Structure -->
<div class="modal fade" id="customerModal" tabindex="-1" aria-labelledby="customerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="customerModalLabel">Customer Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="customerName"></p>
                <p id="customerContact"></p>
                <h5>Products Ordered:</h5>
                <table id="orderTable" class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Quantity</th>
                            <th>Price</th>
                        </tr>
                    </thead>
                    <tbody id="orderItems"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Add event listener for the "View" button
    document.querySelectorAll('.view-customer-btn').forEach(function(button) {
        button.addEventListener('click', function() {
            var customerID = this.getAttribute('data-customer-id');

            // Make an AJAX request to fetch customer details and orders
            fetch('getCustomerOrders.php?customerID=' + customerID)
                .then(response => response.json())
                .then(data => {
                    console.log(data); // Debugging line to check response

                    // Check if data is valid
                    if (data && data.customer && data.orders) {
                        // Populate the modal with the customer details
                        document.getElementById('customerName').textContent = 'Name: ' + data.customer.fullName;
                        document.getElementById('customerContact').textContent = 'Contact: ' + data.customer.contactNumber;

                        // Populate the products ordered table
                        var orderItemsContainer = document.getElementById('orderItems');
                        orderItemsContainer.innerHTML = '';  // Clear any previous items

                        // Loop through each order and add it to the table
                        data.orders.forEach(function(order) {
                            var row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${order.productName}</td>
                                <td>${order.quantity}</td>
                                <td>₱${order.priceAtPurchase.toFixed(2)}</td>
                            `;
                            orderItemsContainer.appendChild(row);
                        });
                    } else {
                        console.error('Invalid data:', data); // Debugging line if data is not valid
                    }
                })
                .catch(error => console.error('Error fetching customer orders:', error));
        });
    });
</script>
</body>
</html>
