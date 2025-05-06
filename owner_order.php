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

// Initialize variables for customer
$fullName = "";
$contactNumber = "";
$fullName_error = "";
$contactNumber_error = "";
$add_customer_error = "";
$add_customer_success = false;

// Initialize variables for order
$orderDate = date('Y-m-d');
$paymentStatus = "unpaid";
$paymentStatus_error = "";

// Initialize variables for order items
$productID = [];
$quantity = [];
$productID_error = [];
$quantity_error = [];

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

    // Validate Payment Status
    if (empty($_POST["paymentStatus"])) {
        $paymentStatus_error = "Payment Status is required";
    } else {
        $paymentStatus = $_POST["paymentStatus"];
        if (!in_array($paymentStatus, ['paid', 'unpaid'])) {
            $paymentStatus_error = "Invalid payment status";
        }
    }

    // Validate Order Items
    if (isset($_POST['productID']) && is_array($_POST['productID'])) {
        $productID = $_POST['productID'];
        $quantity = $_POST['quantity'];

        foreach ($productID as $key => $prodID) {
            if (empty($prodID)) {
                $productID_error[$key] = "Product is required";
            }
            if (empty($quantity[$key]) || !is_numeric($quantity[$key]) || $quantity[$key] <= 0) {
                $quantity_error[$key] = "Quantity must be a positive number";
            }
        }
    } else {
        $add_customer_error = "At least one order item is required.";
    }

    // If no errors, proceed to add customer and order
    if (
        empty($fullName_error) &&
        empty($contactNumber_error) &&
        empty($paymentStatus_error) &&
        empty($productID_error) &&
        empty($quantity_error) &&
        empty($add_customer_error)
    ) {
        $connection->begin_transaction();
        try {
            // Step 1: Check if customer already exists based on fullName and contactNumber
            $sql_check_customer = "
                SELECT customerID 
                FROM tblcustomer 
                WHERE fullName = ? AND contactNumber = ?
                LIMIT 1
            ";
            $stmt_check_customer = $connection->prepare($sql_check_customer);
            if (!$stmt_check_customer) {
                throw new Exception("Prepare check customer failed: " . $connection->error);
            }
            $stmt_check_customer->bind_param("ss", $fullName, $contactNumber);
            $stmt_check_customer->execute();
            $stmt_check_customer->bind_result($customerID);
            $stmt_check_customer->fetch();
            $stmt_check_customer->close();
    
            // If the customer already exists, update their information; otherwise, insert a new customer
            if ($customerID) {
                // Customer exists, update customer data if needed
                $sql_update_customer = "
                    UPDATE tblcustomer
                    SET lastPurchaseDate = CURRENT_DATE
                    WHERE customerID = ?
                ";
                $stmt_update_customer = $connection->prepare($sql_update_customer);
                if (!$stmt_update_customer) {
                    throw new Exception("Prepare update tblcustomer failed: " . $connection->error);
                }
                $stmt_update_customer->bind_param("i", $customerID);
                if (!$stmt_update_customer->execute()) {
                    throw new Exception("Execute update tblcustomer failed: " . $stmt_update_customer->error);
                }
                $stmt_update_customer->close();
            } else {
                // Insert new customer if not found
                $sql_customer = "
                    INSERT INTO tblcustomer 
                        (ownerID, fullName, contactNumber, lastPurchaseDate, createdAt, totalDebt) 
                    VALUES 
                        (?, ?, ?, CURRENT_DATE, CURRENT_DATE, 0)
                ";
                $stmt_customer = $connection->prepare($sql_customer);
                if (!$stmt_customer) {
                    throw new Exception("Prepare tblcustomer failed: " . $connection->error);
                }
    
                $stmt_customer->bind_param("iss", $currentUserID, $fullName, $contactNumber);
                if (!$stmt_customer->execute()) {
                    throw new Exception("Execute tblcustomer failed: " . $stmt_customer->error);
                }
                $customerID = $connection->insert_id;
                $stmt_customer->close();
            }
    
            // Step 2: Insert into tblorder
            $sql_order = "
                INSERT INTO tblorder 
                    (customerID, orderDate, paymentStatus) 
                VALUES 
                    (?, ?, ?)
            ";
            $stmt_order = $connection->prepare($sql_order);
            if (!$stmt_order) {
                throw new Exception("Prepare tblorder failed: " . $connection->error);
            }
    
            $stmt_order->bind_param("iss", $customerID, $orderDate, $paymentStatus);
            if (!$stmt_order->execute()) {
                throw new Exception("Execute tblorder failed: " . $stmt_order->error);
            }
            $orderID = $connection->insert_id;
            $stmt_order->close();
    
            // Step 3: Insert into order_items and accumulate total price for debt if unpaid
            $totalOrderAmount = 0.0;
            $sql_item = "
                INSERT INTO `storestock`.`tblorderitems`
                    (`orderID`, `productID`, `quantity`, `priceAtPurchase`)
                VALUES
                    (?, ?, ?, ?)
            ";
    
            $stmt_item = $connection->prepare($sql_item);
            if (!$stmt_item) {
                throw new Exception("Prepare order_items failed: " . $connection->error);
            }
    
            foreach ($productID as $key => $prodID) {
                $qty = (int)$quantity[$key];
    
                // Fetch unit price
                $sql_price = "SELECT sellingPrice FROM tblproduct WHERE productID = ?";
                $stmt_price = $connection->prepare($sql_price);
                if (!$stmt_price) {
                    throw new Exception("Prepare price query failed: " . $connection->error);
                }
                $stmt_price->bind_param("i", $prodID);
                $stmt_price->execute();
                $stmt_price->bind_result($unitPrice);
                $stmt_price->fetch();
                $stmt_price->close();
    
                // Insert the item
                $stmt_item->bind_param("iiid", $orderID, $prodID, $qty, $unitPrice);
                if (!$stmt_item->execute()) {
                    throw new Exception("Error adding order item: " . $stmt_item->error);
                }
    
                // Accumulate for debt
                $totalOrderAmount += $unitPrice * $qty;
            }
    
            $stmt_item->close();
    
// Step 4: Update totalPrice in tblorder
$sql_total_price = "
    UPDATE tblorder
    SET totalPrice = ?
    WHERE orderID = ?
";
$stmt_total_price = $connection->prepare($sql_total_price);
if (!$stmt_total_price) {
    throw new Exception("Prepare totalPrice update failed: " . $connection->error);
}
$stmt_total_price->bind_param("di", $totalOrderAmount, $orderID);
if (!$stmt_total_price->execute()) {
    throw new Exception("Execute totalPrice update failed: " . $stmt_total_price->error);
}
$stmt_total_price->close();

// Step 5: If unpaid, update customer's totalDebt
if (strtolower($paymentStatus) === 'unpaid') {
    $sql_debt = "
        UPDATE tblcustomer
        SET totalDebt = totalDebt + ?
        WHERE customerID = ?
    ";
    $stmt_debt = $connection->prepare($sql_debt);
    if (!$stmt_debt) {
        throw new Exception("Prepare debt update failed: " . $connection->error);
    }
    $stmt_debt->bind_param("di", $totalOrderAmount, $customerID);
    if (!$stmt_debt->execute()) {
        throw new Exception("Execute debt update failed: " . $stmt_debt->error);
    }
    $stmt_debt->close();
}

    
            // Everything OK!
            $connection->commit();
            $add_customer_success = true;
            $fullName = "";
            $contactNumber = "";
    
        } catch (Exception $e) {
            $connection->rollback();
            $add_customer_error = "Transaction failed: " . $e->getMessage();
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

// Fetch all products for the dropdown
$products = [];
$sql_products = "SELECT productID, productName, sellingPrice FROM tblproduct";
$result_products = $connection->query($sql_products);
if ($result_products) {
    while ($row_product = $result_products->fetch_assoc()) {
        $products[] = $row_product;
    }
    $result_products->free();
} else {
    $add_customer_error .= " Error fetching products: " . $connection->error;
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
        .error-message {
            color: red;
            font-size: 0.9em;
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
            <a href="owner_customer.php">Customers</a>
            <a href="owner_order.php">Orders</a>
            <a href="owner_supplier.php">Suppliers</a>
            <a href="owner_settings.php" class="fw-bold">Settings</a>
            <a href="logout.php">Logout</a>
        </div>

        <div class="col-md-10 p-4">
            <h3 class="mb-4">Customer and Order Management</h3>

            <div class="mb-5">
                <h4>Add New Customer and Order</h4>
                <?php if ($add_customer_success): ?>
                    <div class="alert alert-success">Customer and Order added successfully!</div>
                <?php endif; ?>
                <?php if (!empty($add_customer_error)): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($add_customer_error); ?></div>
                <?php endif; ?>
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <h5>Customer Information</h5>
                    <div class="row mb-3">
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

                    <h5 class="mt-3">Order Information</h5>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="orderDate" class="form-label">Order Date</label>
                            <input type="date" class="form-control" id="orderDate" name="orderDate" value="<?php echo htmlspecialchars($orderDate); ?>" readonly>
                        </div>
                        <div class="col-md-4">
                            <label for="paymentStatus" class="form-label">Payment Status</label>
                            <select class="form-select" id="paymentStatus" name="paymentStatus" required>
                                <option value="unpaid" <?php echo ($paymentStatus === 'unpaid') ? 'selected' : ''; ?>>Unpaid</option>
                                <option value="paid" <?php echo ($paymentStatus === 'paid') ? 'selected' : ''; ?>>Paid</option>
                            </select>
                            <?php if (!empty($paymentStatus_error)): ?>
                                <div class="error-message"><?php echo htmlspecialchars($paymentStatus_error); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <h5 class="mt-3">Order Items</h5>
                    <div id="orderItemsContainer">
                        <div class="row mb-3 order-item">
                            <div class="col-md-4">
                            <label for="productID_0" class="form-label">Product</label>
                            <select
                                class="form-select product-select"
                                id="productID_0"
                                name="productID[]"
                                data-row-id="0"
                                required
                            >
                                <option value="">Select Product</option>
                                <?php if (!empty($products)): ?>
                                <?php foreach ($products as $product): ?>
                                    <option
                                    value="<?php echo htmlspecialchars($product['productID']); ?>"
                                    data-unit-price="<?php echo htmlspecialchars($product['sellingPrice']); ?>"
                                    >
                                    <?php echo htmlspecialchars($product['productName']); ?>
                                    </option>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <?php if (!empty($productID_error[0])): ?>
                                <div class="error-message"><?php echo htmlspecialchars($productID_error[0]); ?></div>
                            <?php endif; ?>
                            </div>
                            <div class="col-md-3">
                            <label for="quantity_0" class="form-label">Quantity</label>
                            <input
                                type="number"
                                class="form-control quantity-input"
                                id="quantity_0"
                                name="quantity[]"
                                value="1"
                                min="1"
                                data-row-id="0"
                                required
                            >
                            <?php if (!empty($quantity_error[0])): ?>
                                <div class="error-message"><?php echo htmlspecialchars($quantity_error[0]); ?></div>
                            <?php endif; ?>
                            </div>
                            <div class="col-md-3">
                            <label for="unitPrice_0" class="form-label">Unit Price</label>
                            <input
                                type="text"
                                class="form-control unit-price-input"
                                id="unitPrice_0"
                                name="unitPrice[]"
                                value=""
                                readonly
                            >
                            </div>
                            <div class="col-md-2 align-self-end">
                            <button
                                type="button"
                                class="btn btn-danger remove-item-btn"
                                data-row-id="0"
                            >🗑️</button>
                            </div>
                        </div>
                        </div>

                        <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            // Delegate all change events on product-selects
                            document
                            .getElementById('orderItemsContainer')
                            .addEventListener('change', function(e) {
                                if (!e.target.classList.contains('product-select')) return;

                                const select = e.target;
                                const rowId = select.dataset.rowId;       // e.g. "0"
                                const selectedOpt = select.options[select.selectedIndex];
                                const rawPrice = selectedOpt.getAttribute('data-unit-price') || '';

                                const price = rawPrice;

                                const unitPriceInput = document.querySelector(
                                `#unitPrice_${rowId}`
                                );
                                if (unitPriceInput) unitPriceInput.value = price;
                            });
                        });
                        </script>

                    <div class="mt-2">
                        <button type="button" class="btn btn-info" id="addOrderItem">➕ Add Item</button>
                    </div>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-success">➕ Add Customer and Create Order</button>
                    </div>
                </form>
            </div>

<h4 class="mt-4">Customer List</h4>
<table class="table table-bordered table-striped">
    <thead class="table-light">
        <tr>
            <th>Name</th>
            <th>Contact</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($customers)): ?>
            <?php foreach ($customers as $customer): ?>
                <tr>
                    <td><?php echo htmlspecialchars($customer['fullName']); ?></td>
                    <td><?php echo htmlspecialchars($customer['contactNumber']); ?></td>
                    <td>
                        <button class="btn btn-primary btn-sm view-order-btn"
                                data-bs-toggle="modal"
                                data-bs-target="#viewOrderModal"
                                data-customer='<?php echo json_encode([
                                  'fullName' => $customer['fullName'],
                                  'contactNumber' => $customer['contactNumber'],
                                  'orders' => $customer['orders'] ?? []
                                ]); ?>'>
                            View Order
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="3">No customers added yet.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<!-- Modal -->
<div class="modal fade" id="viewOrderModal" tabindex="-1" aria-labelledby="viewOrderModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Customer Orders</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p><strong>Name:</strong> <span id="modalCustomerName"></span></p>
        <p><strong>Contact:</strong> <span id="modalCustomerContact"></span></p>

        <table class="table table-bordered">
          <thead>
            <tr>
              <th>Product Name</th>
              <th>Quantity</th>
              <th>Price at Purchase</th>
            </tr>
          </thead>
          <tbody id="modalOrderItems">
            <!-- Filled dynamically -->
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
document.querySelectorAll('.view-order-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const customer = JSON.parse(btn.getAttribute('data-customer'));

    document.getElementById('modalCustomerName').textContent = customer.fullName;
    document.getElementById('modalCustomerContact').textContent = customer.contactNumber;

    const tableBody = document.getElementById('modalOrderItems');
    tableBody.innerHTML = '';

    if (customer.orders.length === 0) {
      tableBody.innerHTML = '<tr><td colspan="3">No orders found.</td></tr>';
      return;
    }

    customer.orders.forEach(order => {
      const row = document.createElement('tr');
      row.innerHTML = `
        <td>${order.productName}</td>
        <td>${order.quantity}</td>
        <td>${order.priceAtPurchase}</td>
      `;
      tableBody.appendChild(row);
    });
  });
});
</script>
