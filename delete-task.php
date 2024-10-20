<?php
include 'layouts/session.php';
include 'layouts/config.php';
include 'layouts/functions.php';
// Check permissions
if (!hasPermission('view_tasks') || !hasPermission('delete_tasks')) {
    header('Location: index.php');
    exit();
}

// Check if an ID is provided in the URL 
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $taskId = $_GET['id'];

    try {
        // Begin the transaction
        $conn->begin_transaction();

        // SQL query to delete the assigned permission by ID
        $deleteQuery = "DELETE FROM tasks WHERE id = ?";

        // Prepare the statement
        $stmt = $conn->prepare($deleteQuery);

        // Bind the ID parameter
        $stmt->bind_param('i', $taskId);

        // Execute the statement
        if ($stmt->execute()) {
            // Commit the transaction
            $conn->commit();

            // Set success message
            $_SESSION['message'][] = ['type' => 'success', 'content' => 'Task Removed!'];

            // $_SESSION['message'] = ['type' => 'success', 'text' => 'Permission deleted successfully.'];
        } else {
            throw new Exception("Failed to delete Task: " . $stmt->error);
        }
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();

        // Set error message
        $_SESSION['message'] = ['type' => 'danger', 'text' => $e->getMessage()];
    }

    // Close the statement
    $stmt->close();
} else {
    // If no valid ID is provided, redirect with an error message
    // $_SESSION['message'] = ['type' => 'danger', 'text' => 'Invalid permission ID.'];
    $_SESSION['message'][] = ['type' => 'Danger', 'content' => 'Invalid Task id!'];
}

// Redirect to the manage page (or wherever appropriate)
header("Location: add-tasks.php");
exit;
