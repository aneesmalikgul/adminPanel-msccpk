<?php
// Include necessary files and start session
include 'layouts/session.php';
include 'layouts/config.php'; // Database connection
include 'layouts/functions.php';

if (!hasPermission('manage_user') || !hasPermission('manage_role') || !hasPermission('delete_role')) {
    header('Location: manage-role.php');
    exit();
}

// Check if the role ID is provided via GET
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['message'][] = ["type" => "error", "content" => "No role ID provided."];
    header("Location: user-roles.php");
    exit();
}

// Sanitize and validate the input to prevent SQL injection
$roleId = (int)$_GET['id']; // Cast to integer

// Start a transaction
$conn->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);

try {
    // Prepare the DELETE statement
    $query = "DELETE FROM roles WHERE id = ?";
    $stmt = $conn->prepare($query);

    // Throw an exception if the prepare statement fails
    if ($stmt === false) {
        throw new Exception("Prepare statement failed: " . $conn->error);
    }

    // Bind the role ID to the prepared statement
    $stmt->bind_param("i", $roleId);

    // Execute the statement
    if (!$stmt->execute()) {
        throw new Exception("Execute statement failed: " . $stmt->error);
    }

    // Commit the transaction
    $conn->commit();

    // Set success message in session
    $_SESSION['message'][] = ["type" => "success", "content" => "Role deleted successfully!"];

    // Redirect back to the roles page
    header("Location: user-roles.php");
    exit();
} catch (Exception $e) {
    // Rollback the transaction if any error occurs
    $conn->rollback();

    // Set error message in session
    $_SESSION['message'][] = ["type" => "error", "content" => $e->getMessage()];

    // Redirect back to the roles page
    header("Location: user-roles.php");
    exit();
} finally {
    // Close the statement and the connection
    if (isset($stmt)) {
        $stmt->close();
    }
    $conn->close();
}
