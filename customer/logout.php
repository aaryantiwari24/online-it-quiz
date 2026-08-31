<?php
// Turn on error reporting temporarily to see if it's failing
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Remove all session variables
session_unset();

// Destroy the session completely
session_destroy();

// Redirect back to the main public homepage
header("Location: ../index.php");
exit();