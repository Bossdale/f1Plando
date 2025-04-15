<?php
$connection = mysqli_connect(
    "localhost", 
    "root", 
    "", 
    "storestock", 
    null, 
    "/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock"
);

if (!$connection) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
