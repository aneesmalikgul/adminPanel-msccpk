<?php
include 'layouts/session.php';
include 'layouts/main.php';
include 'layouts/config.php';
include 'layouts/functions.php';

// Check if ID is present in the URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['message'][] = array("type" => "error", "content" => "ID not found.");
    header("Location: projects.php");
    exit();
}

$id = mysqli_real_escape_string($conn, $_GET['id']);

try {
    // Fetch project details
    $query = "SELECT * FROM projects WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    // Check if project exists
    if (mysqli_stmt_num_rows($stmt) == 0) {
        $_SESSION['message'][] = array("type" => "error", "content" => "Record not found.");
        header("Location: projects.php");
        exit();
    }

    // Fetch project details
    mysqli_stmt_bind_result($stmt, $id, $project_name, $project_category, $client_name, $start_date, $end_date, $project_image, $created_by, $created_at, $updated_by, $updated_at, $is_active);
    mysqli_stmt_fetch($stmt);

    // Delete project image if it exists
    if (!empty($project_image) && file_exists($project_image)) {
        unlink($project_image); // Delete the file from the server
    }

    // Delete project from database
    $delete_query = "DELETE FROM projects WHERE id = ?";
    $delete_stmt = mysqli_prepare($conn, $delete_query);
    mysqli_stmt_bind_param($delete_stmt, "i", $id);

    if (mysqli_stmt_execute($delete_stmt)) {
        $_SESSION['message'][] = array("type" => "success", "content" => "Project deleted successfully!");
    } else {
        throw new Exception("Database delete operation failed: " . mysqli_error($conn));
    }

    mysqli_stmt_close($delete_stmt);
    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    header("Location: projects.php");
    exit();
} catch (Exception $e) {
    $_SESSION['message'][] = array("type" => "error", "content" => $e->getMessage());
    header("Location: projects.php");
    exit();
} finally {
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    header("Location: projects.php");
    exit();
}
