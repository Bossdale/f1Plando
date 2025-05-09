<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include __DIR__ . '/connect.php';

// Session validation
if (!isset($_SESSION['userID']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'supplier') {
    header("Location: signin.php");
    exit;
}

$supplierID = $_SESSION['userID'];
$userName = $_SESSION['username'];
$firstName = $_SESSION['firstname'];

// Date filters
$fromDate = $_GET['fromDate'] ?? null;
$toDate = $_GET['toDate'] ?? null;

function getDateCondition($fromDate, $toDate, $dateColumn = 'o.orderDate') {
    $condition = '';
    if ($fromDate && $toDate) {
        $condition = " AND $dateColumn >= ? AND $dateColumn <= ?";
    } elseif ($fromDate) {
        $condition = " AND $dateColumn >= ?";
    } elseif ($toDate) {
        $condition = " AND $dateColumn <= ?";
    }
    return $condition;
}

$stmt = $connection->prepare("SELECT COUNT(*) AS total FROM tblProduct WHERE supplierID = ?");
$stmt->bind_param("i", $supplierID);
$stmt->execute();
$totalSuppliedProducts = $stmt->get_result()->fetch_assoc()['total'];

$query = "
    SELECT COUNT(DISTINCT i.ownerID) AS totalStores
    FROM tblInventory i
    JOIN tblProduct p ON i.productID = p.productID
    WHERE p.supplierID = ?
";

$stmt = $connection->prepare($query);
$stmt->bind_param("i", $supplierID);
$stmt->execute();
$totalStores = $stmt->get_result()->fetch_assoc()['totalStores'];

// 3. Last Delivery Date
$query = "
    SELECT MAX(o.orderDate) AS lastDelivery
    FROM tblOrder o
    JOIN tblOrderItems oi ON o.orderID = oi.orderID
    JOIN tblProduct p ON oi.productID = p.productID
    WHERE p.supplierID = ?
";
$stmt = $connection->prepare($query);
$stmt->bind_param("i", $supplierID);
$stmt->execute();

$lastDeliveryDate = $stmt->get_result()->fetch_assoc()['lastDelivery'] ?? 'N/A';

// 4. Recent Deliveries
$recentDeliveries = [];
$query = "
    SELECT o.orderDate AS delivery_date, ow.storeName AS store_name, COUNT(oi.orderItemID) AS items
    FROM tblOrder o
    JOIN tblOrderItems oi ON o.orderID = oi.orderID
    JOIN tblProduct p ON oi.productID = p.productID
    JOIN tblInventory i ON p.productID = i.productID
    JOIN tblOwner ow ON i.ownerID = ow.ownerID
    WHERE p.supplierID = ?
    GROUP BY o.orderID
    ORDER BY o.orderDate DESC
    LIMIT 5
";
$stmt = $connection->prepare($query);
$stmt->bind_param("i", $supplierID);
$stmt->execute();
$recentDeliveries = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// 5. Most Delivered Products
$topDeliveredProducts = [];
$query = "
    SELECT p.productName AS name, SUM(oi.quantity) AS quantity
    FROM tblOrderItems oi
    JOIN tblProduct p ON oi.productID = p.productID
    JOIN tblOrder o ON oi.orderID = o.orderID
    WHERE p.supplierID = ?" . getDateCondition($fromDate, $toDate, "o.orderDate") . "
    GROUP BY p.productID
    ORDER BY quantity DESC
    LIMIT 5
";

$paramTypes = "i";
$params = [$supplierID];
if ($fromDate && $toDate) {
    $paramTypes .= "ss";
    $params[] = $fromDate;
    $params[] = $toDate;
} elseif ($fromDate || $toDate) {
    $paramTypes .= "s";
    $params[] = $fromDate ?? $toDate;
}

$stmt = $connection->prepare($query);
$stmt->bind_param($paramTypes, ...$params);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $topDeliveredProducts[] = [
        "name" => $row['name'],
        "quantity" => (int)$row['quantity']
    ];
}

// 6. Delivery Volume Chart
$deliveryChartData = ['labels' => [], 'values' => []];
$query = "
    SELECT DATE(o.orderDate) AS date, SUM(oi.quantity) AS total
    FROM tblOrder o
    JOIN tblOrderItems oi ON o.orderID = oi.orderID
    JOIN tblProduct p ON oi.productID = p.productID
    WHERE p.supplierID = ?" . getDateCondition($fromDate, $toDate) . "
    GROUP BY DATE(o.orderDate)
    ORDER BY DATE(o.orderDate)
";
$stmt = $connection->prepare($query);
$stmt->bind_param($paramTypes, ...$params);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $deliveryChartData['labels'][] = $row['date'];
    $deliveryChartData['values'][] = (int)$row['total'];
}

$query = "
    SELECT ow.storeName, SUM(oi.quantity) AS total
    FROM tblOrder o
    JOIN tblOrderItems oi ON o.orderID = oi.orderID
    JOIN tblProduct p ON oi.productID = p.productID
    JOIN tblInventory i ON p.productID = i.productID
    JOIN tblOwner ow ON i.ownerID = ow.ownerID
    WHERE p.supplierID = ?" . getDateCondition($fromDate, $toDate, "o.orderDate") . "
    GROUP BY ow.ownerID
    ORDER BY total DESC
    LIMIT 5
";

$stmt = $connection->prepare($query);
$stmt->bind_param($paramTypes, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$topStoresData = ['labels' => [], 'values' => []];
while ($row = $result->fetch_assoc()) {
    $topStoresData['labels'][] = $row['storeName'];
    $topStoresData['values'][] = (int)$row['total'];
}

return [
    'totalSuppliedProducts' => $totalSuppliedProducts,
    'totalStores' => $totalStores,
    'lastDeliveryDate' => $lastDeliveryDate,
    'recentDeliveries' => $recentDeliveries,
    'topDeliveredProducts' => $topDeliveredProducts,
    'deliveryChartData' => $deliveryChartData,
    'topStoresData' => $topStoresData
];
?>
