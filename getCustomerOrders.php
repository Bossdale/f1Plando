<?php
// Filename: getCustomerOrders.php
include __DIR__ . '/connect.php';
session_start();

// Require login
if (!isset($_SESSION['userID'])) {
    http_response_code(403);
    exit();
}

// Validate input
if (!isset($_GET['customerID'])) {
    http_response_code(400);
    exit();
}
$customerID = (int) $_GET['customerID'];

// Fetch customer info
$stmt = $connection->prepare(
    "SELECT fullName, contactNumber
     FROM tblcustomer
     WHERE customerID = ?"
);
$stmt->bind_param("i", $customerID);
$stmt->execute();
$customer = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch line-items for that customer
$stmt = $connection->prepare(
    "SELECT p.productName, oi.quantity, oi.priceAtPurchase
     FROM tblorderitems oi
     JOIN tblorder o ON oi.orderID = o.orderID
     JOIN tblproduct p ON oi.productID = p.productID
     WHERE o.customerID = ?
     ORDER BY o.orderDate DESC, p.productName"
);
$stmt->bind_param("i", $customerID);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($item = $result->fetch_assoc()) {
    $orders[] = $item;
}
$stmt->close();

// Return JSON
header('Content-Type: application/json');
echo json_encode([
    'customer' => $customer,
    'orders'   => $orders
]);


/* ===================================================================== */

// Filename: customer_list.php
include __DIR__ . '/connect.php';
session_start();

// Require login
if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}
$currentUserID = $_SESSION['userID'];

// Fetch customers and total debt
$customers = [];
$sql = "
    SELECT
      c.customerID,
      c.fullName,
      c.contactNumber,
      COALESCE(SUM(o.totalAmount),0) AS totalDebt
    FROM tblcustomer c
    LEFT JOIN tblorder o
      ON c.customerID = o.customerID
    WHERE c.ownerID = ?
    GROUP BY c.customerID, c.fullName, c.contactNumber
    ORDER BY c.fullName
";
$stmt = $connection->prepare($sql);
$stmt->bind_param("i", $currentUserID);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $customers[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Management - StoreStock</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #f8f9fa; }
        .sidebar { height: 100vh; background-color: rgb(115,32,21); color: white; }
        .sidebar a { color: white; text-decoration: none; padding: 10px 20px; display: block; }
        .sidebar a:hover { background-color: #af3222; }
        .section-title { margin: 20px 0 10px; font-weight: bold; }
        .logo { border-radius: 50%; width: 100px; height: 100px; object-fit: cover; }
        .error-message { color: red; font-size: 0.9em; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h4>Customer List</h4>
        <table class="table table-bordered table-striped">
            <thead class="table-light">
                <tr><th>Name</th><th>Contact</th><th>Total Debt (₱)</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php if (!empty($customers)): ?>
                    <?php foreach ($customers as $c): ?>
                        <tr>
                            <td><?= htmlspecialchars($c['fullName']) ?></td>
                            <td><?= htmlspecialchars($c['contactNumber']) ?></td>
                            <td><?= number_format($c['totalDebt'],2) ?></td>
                            <td>
                                <button class="btn btn-info view-customer-btn"
                                        data-customer-id="<?= $c['customerID'] ?>"
                                        data-bs-toggle="modal"
                                        data-bs-target="#customerModal">
                                    View Orders
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4">No customers found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="customerModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Orders for <span id="modalCustomerName"></span></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <p id="modalCustomerContact"></p>
            <table class="table table-bordered">
              <thead>
                <tr><th>Product Name</th><th>Quantity</th><th>Price (₱)</th></tr>
              </thead>
              <tbody id="modalOrderList"></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.querySelectorAll('.view-customer-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        const id = btn.dataset.customerId;
        fetch(`getCustomerOrders.php?customerID=${id}`)
          .then(res => res.json())
          .then(data => {
            document.getElementById('modalCustomerName').textContent = data.customer.fullName;
            document.getElementById('modalCustomerContact').textContent = 'Contact: ' + data.customer.contactNumber;

            const tbody = document.getElementById('modalOrderList');
            tbody.innerHTML = '';

            if (data.orders.length === 0) {
              tbody.innerHTML = '<tr><td colspan="3">No orders found.</td></tr>';
            } else {
              data.orders.forEach(item => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                  <td>${item.productName}</td>
                  <td>${item.quantity}</td>
                  <td>${parseFloat(item.priceAtPurchase).toFixed(2)}</td>
                `;
                tbody.appendChild(tr);
              });
            }
          })
          .catch(console.error);
      });
    });
    </script>
</body>
</html>