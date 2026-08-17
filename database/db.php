<?php
// ------------------------------------------------------
// database/db.php
// Creates the shared database connection for the waste
// management system so all pages can run SQL queries.
// ------------------------------------------------------

// Store the local database host name used by the XAMPP setup.
$host = "localhost";

// Use the default XAMPP MySQL username for local development.
$user = "root";

// XAMPP usually runs MySQL without a password in local testing.
$pass = "";

// This is the database name created by the schema.sql file.
$dbname = "waste_db";

// Create one reusable mysqli connection object for the whole app.
$conn = new mysqli($host, $user, $pass, $dbname);

// If the connection failed, stop the script immediately and show the error.
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

// The connection is ready, so other PHP files can run queries using $conn.
?>