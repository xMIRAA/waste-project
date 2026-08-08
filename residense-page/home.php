<?php
session_start();

// Get the logged-in username from session (defaults to 'Resident' if not set)
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Resident';

// Next pickup time (this can later be dynamically fetched from your database)
$next_pickup = "Tuesday, 8:00 AM"; 
?>