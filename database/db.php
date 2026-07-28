<?php
// 1. Connection credentials
$host = "localhost";
$user = "root";      // Default username for XAMPP
$pass = "";          // Default password for XAMPP (blank)
$dbname = "waste_db"; // Name of database created by schema.sql

// 2. Initialize the connection using PHP's native mysqli extension
$conn = new mysqli($host, $user, $pass, $dbname);

// 3. CHECK THE CONNECTION
if ($conn->connect_error) {
    // If something went wrong, stop execution and print the error message
    die("Database Connection Failed: " . $conn->connect_error);
}

// Connection successful! 
// $conn object is now ready to run SQL queries in other PHP files.
?>