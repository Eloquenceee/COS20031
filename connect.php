<?php
$host = "feenix-mariadb.swin.edu.au";      // or 127.0.0.1
$user = "s103485545";       // your DB username
$pwd = "chillgroup12";           // your DB password
$sql_db = "s103485545_db"; // your database name
$port = 3306;                // your DB port

// Create connection
$conn = @mysqli_connect(
    $host,
    $user,
    $pwd,
    $sql_db,
    $port
);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>