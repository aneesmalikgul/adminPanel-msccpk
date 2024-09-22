<?php
// Include necessary files and start session
include 'layouts/session.php';
include 'layouts/config.php'; // Database connection
include 'layouts/functions.php';

// Check if the user ID is provided via GET
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['message'][] = ["type" => "error", "content" => "No User Found."];
    header("Location: manage-users.php");
    exit();
}

// Sanitize and get user ID from the URL
$userId = $conn->real_escape_string($_GET['id']);

// Start a transaction
$conn->begin_transaction();

try {
    // Prepare delete query
    $deleteQuery = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($deleteQuery);

    // Bind the parameter (assuming ID is an integer)
    $stmt->bind_param("i", $userId);

    // Execute the query
    if ($stmt->execute()) {
        // Commit the transaction
        $conn->commit();
        $_SESSION['message'][] = ["type" => "success", "content" => "User deleted successfully!"];
    } else {
        throw new Exception("Failed to delete user: " . $stmt->error);
    }

    // Close the statement
    $stmt->close();
} catch (Exception $e) {
    // Rollback the transaction on error
    $conn->rollback();
    $_SESSION['message'][] = ["type" => "danger", "content" => $e->getMessage()];
}

// Redirect to manage users page
header("Location: manage-users.php");
exit();
