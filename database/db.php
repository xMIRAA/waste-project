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

// Create one reusable PDO connection object for the whole app.
try {
    $conn = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    // If the connection failed, stop the script immediately.
    die("Database Connection Failed.");
}

// The connection is ready, so other PHP files can run queries using $conn.