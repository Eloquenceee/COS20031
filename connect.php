<?php
// Database connection configuration
$host = "feenix-mariadb.swin.edu.au";      // Database server hostname
$user = "s103485545";                       // Database username
$pwd = "chillgroup12";                      // Database password
$sql_db = "s103485545_db";                 // Target database name
$port = 3306;                              // Database server port

// Establish database connection with error suppression
$conn = @mysqli_connect(
    $host,
    $user,
    $pwd,
    $sql_db,
    $port
);

// Verify connection success
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>