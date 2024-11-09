<?php
$hostname = "localhost";
$dbUser = "root";
$dbPassword = "";
$dbName = "escape_avenue";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Enable error reporting for debugging
$conn = mysqli_connect($hostname, $dbUser, $dbPassword, $dbName);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8"); // Set character encoding to UTF-8
?>
