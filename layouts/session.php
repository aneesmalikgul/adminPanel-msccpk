<?php
ob_start();
// Start a session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Check if the user is not logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: auth-login.php");
    exit;
}

// Function to display session messages using Toastr
function showSessionMessage()
{
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        echo "<script>toastr.{$message['type']}('{$message['content']}');</script>";
        unset($_SESSION['message']); // Clear the message after showing it
    }
}
