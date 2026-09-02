<?php

$servername = "localhost";
$username   = "root";
$password   = "";
$database   = "online_it_quiz";

$conn = mysqli_connect(
    $servername,
    $username,
    $password,
    $database
);

if (!$conn) {
    die("Database connection failed.");
}

// Use UTF-8 for proper handling of text
mysqli_set_charset($conn, "utf8mb4");

?>