<?php
session_start(); // Start the session

include __DIR__ . '/connect.php';

// Check if user is logged in
if (!isset($_SESSION['userID']) || !isset($_SESSION['role'])) {
    header("Location: signin.php");
    exit;
}

// Check if role is 'owner' (adjust this if you also want to allow 'supplier')
if ($_SESSION['role'] !== 'owner') {
    echo "Access denied. Only owners can view this dashboard.";
    exit;
}

// Use logged-in owner's ID from session
//$ownerID = $_SESSION['userID'];

// Initialize variables
$totalProducts = 0;
$totalSales = 0;
$totalDebts = 0;
$totalCustomers = 0;
$lowStockProducts = [];
$recentOrders = [];
$salesData = ['labels' => [], 'values' => []];
$topProductsData = ['labels' => [], 'values' => []];

// Get current owner ID (assuming logged in owner)
$userName = $_SESSION['username'];
$firstName = $_SESSION['firstname'];
$ownerID = $_SESSION['userID'];

// Get date filter parameters
$fromDate = $_GET['fromDate'] ?? null;
$toDate = $_GET['toDate'] ?? null;

// Function to generate date condition for queries
function getDateCondition($fromDate, $toDate, $dateColumn = 'o.orderDate') {
    $condition = '';
    if ($fromDate && $toDate) {
        $condition = " AND $dateColumn >= '$fromDate' AND $dateColumn <= '$toDate'";
    } elseif ($fromDate) {
        $condition = " AND $dateColumn >= '$fromDate'";
    } elseif ($toDate) {
        $condition = " AND $dateColumn <= '$toDate'";
    }
    return $condition;
}

$query = "SELECT storeName FROM tblOwner";
$result = mysqli_query($connection, $query);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $storeName= $row['storeName'];
}

// Get total products for current owner
$query = "SELECT COUNT(p.productID) as total
          FROM tblProduct p
          JOIN tblInventory i ON p.productID = i.productID
          WHERE i.ownerID = $ownerID AND p.isActive = TRUE";
$result = mysqli_query($connection, $query);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $totalProducts = $row['total'];
}

// Get total sales for current owner (paid orders) with date filter
$query = "SELECT SUM(o.totalPrice) as total
          FROM tblOrder o
          JOIN tblCustomer c ON o.customerID = c.customerID
          WHERE c.ownerID = $ownerID AND o.paymentStatus = 'paid'" . getDateCondition($fromDate, $toDate);
$result = mysqli_query($connection, $query);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $totalSales = $row['total'] ?? 0;
}

// Get total debts for current owner (unpaid orders) with date filter
$query = "SELECT SUM(o.totalPrice) as total
          FROM tblOrder o
          JOIN tblCustomer c ON o.customerID = c.customerID
          WHERE c.ownerID = $ownerID AND o.paymentStatus = 'unpaid'" . getDateCondition($fromDate, $toDate);
$result = mysqli_query($connection, $query);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $totalDebts = $row['total'] ?? 0;
}

// Get total customers for current owner (no date filter needed)
$query = "SELECT COUNT(*) as total
          FROM tblCustomer
          WHERE ownerID = $ownerID";
$result = mysqli_query($connection, $query);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $totalCustomers = $row['total'];
}

// Get low stock products for current owner (no date filter needed)
$query = "SELECT p.productName as name, i.quantity
          FROM tblInventory i
          JOIN tblProduct p ON i.productID = p.productID
          WHERE i.ownerID = $ownerID AND i.quantity <= i.reorderLevel
          ORDER BY i.quantity ASC
          LIMIT 5";
$result = mysqli_query($connection, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $lowStockProducts[] = $row;
    }
}

// Get recent orders for current owner (no date filter needed for the list itself,
// but the totals in header cards are filtered)
$query = "SELECT c.fullName as customer_name, o.totalPrice as total_amount, o.paymentStatus as status, o.orderDate
          FROM tblOrder o
          JOIN tblCustomer c ON o.customerID = c.customerID
          WHERE c.ownerID = $ownerID
          ORDER BY o.orderDate DESC, o.orderID DESC
          LIMIT 5";
$result = mysqli_query($connection, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $recentOrders[] = [
            'customer_name' => $row['customer_name'],
            'total_amount' => $row['total_amount'],
            'status' => ucfirst($row['status']),
            'order_date' => $row['orderDate']
        ];
    }
}

// Get sales data for chart (within the selected date range for current owner)
$query = "SELECT DATE(o.orderDate) as day, SUM(o.totalPrice) as total
          FROM tblOrder o
          JOIN tblCustomer c ON o.customerID = c.customerID
          WHERE c.ownerID = $ownerID" . getDateCondition($fromDate, $toDate) . "
          GROUP BY DATE(o.orderDate)
          ORDER BY day ASC
          LIMIT 5"; // Limiting to 5 for consistency with previous chart
$result = mysqli_query($connection, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $salesData['labels'][] = date('D', strtotime($row['day']));
        $salesData['values'][] = $row['total'] ?? 0;
    }
}

// If no sales data, create empty dataset for chart
if (empty($salesData['labels'])) {
    // If a date range is selected, show empty data for that range
    if ($fromDate || $toDate) {
        // You might want to generate labels based on the date range if needed
        $salesData = [
            'labels' => [],
            'values' => []
        ];
    } else {
        // Default empty data if no date range is selected
        $salesData = [
            'labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
            'values' => [0, 0, 0, 0, 0]
        ];
    }
}

// Get top products data for current owner (no date filter for this example)
$query = "SELECT p.productName as name, SUM(oi.quantity) as total_quantity
          FROM tblOrderItems oi
          JOIN tblOrder o ON oi.orderID = o.orderID
          JOIN tblCustomer c ON o.customerID = c.customerID
          JOIN tblProduct p ON oi.productID = p.productID
          WHERE c.ownerID = $ownerID" . getDateCondition($fromDate, $toDate) . "
          GROUP BY p.productID
          ORDER BY total_quantity DESC
          LIMIT 4";
$result = mysqli_query($connection, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $topProductsData['labels'][] = $row['name'];
        $topProductsData['values'][] = $row['total_quantity'];
    }
}

// If no top products data, create sample data for chart
if (empty($topProductsData['labels'])) {
    $topProductsData = [
        'labels' => ['Coke', 'Lucky Me', 'Sardines', 'Piattos'],
        'values' => [0, 0, 0, 0]
    ];
}

mysqli_close($connection);
?>