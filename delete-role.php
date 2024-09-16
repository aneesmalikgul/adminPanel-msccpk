<?php
// Include necessary files and start session
include 'layouts/session.php';
include 'layouts/config.php'; // Database connection
include 'layouts/functions.php';

// Check if the role ID is provided via GET
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['message'][] = array("type" => "error", "content" => "No role ID provided.");
    header("Location: user-roles.php");
    exit();
}

// Sanitize and validate the input to prevent SQL injection
$roleId = mysqli_real_escape_string($conn, $_GET['id']);

// Start a transaction
mysqli_begin_transaction($conn, MYSQLI_TRANS_START_READ_WRITE);

try {
    // Prepare the DELETE statement
    $query = "DELETE FROM roles WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);

    // Throw an exception if the prepare statement fails
    if ($stmt === false) {
        throw new Exception("Prepare statement failed: " . mysqli_error($conn));
    }

    // Bind the role ID to the prepared statement
    mysqli_stmt_bind_param($stmt, "i", $roleId);

    // Execute the statement
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Execute statement failed: " . mysqli_stmt_error($stmt));
    }

    // Commit the transaction
    mysqli_commit($conn);

    // Set success message in session
    $_SESSION['message'][] = array("type" => "success", "content" => "Role deleted successfully!");

    // Redirect back to the roles page
    header("Location: user-roles.php");
    exit();
} catch (Exception $e) {
    // Rollback the transaction if any error occurs
    mysqli_rollback($conn);

    // Set error message in session
    $_SESSION['message'][] = array("type" => "error", "content" => $e->getMessage());

    // Redirect back to the roles page
    header("Location: user-roles.php");
    exit();
} finally {
    // Close the statement and the connection
    if (isset($stmt)) {
        mysqli_stmt_close($stmt);
    }
    mysqli_close($conn);
}
