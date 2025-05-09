<?php
include __DIR__ . '/connect.php';
session_start();

if (!isset($_SESSION['userID'])) {
    http_response_code(401);
    exit(json_encode(['error' => 'Unauthorized']));
}

if (!isset($_GET['customer_id'])) {
    http_response_code(400);
    exit(json_encode(['error' => 'Customer ID required']));
}

$customerId = (int)$_GET['customer_id'];
$currentUserID = $_SESSION['userID'];

$query = "SELECT
    oi.orderItemID,
    oi.orderID,
    p.productName,
    oi.quantity,
    oi.priceAtPurchase
FROM storestock.tblorderitems AS oi
INNER JOIN storestock.tblorder       AS o  ON oi.orderID    = o.orderID
INNER JOIN storestock.tblcustomer    AS c  ON o.customerID   = c.customerID
INNER JOIN storestock.tblproduct     AS p  ON oi.productID  = p.productID
WHERE 
    o.customerID = ?     -- the customer whose orders you want
    AND c.ownerID  = ?;  -- the owner assigned to that customer
";  # Use customer's owner

$stmt = $connection->prepare($query);
$stmt->bind_param("ii", $customerId, $currentUserID);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}

header('Content-Type: application/json');
echo json_encode($orders);
$stmt->close();
$connection->close();
?>