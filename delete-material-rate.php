<?php
include 'layouts/session.php';
include 'layouts/config.php'; // Make sure you include your database configuration file
include 'layouts/functions.php';

if (!hasPermission('delete_material_rate') || !hasPermission('manage_material_rate')) {
    header('location: index.php');
}


// Check if ID is set and numeric
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $materialId = $_GET['id'];

    // Prepare delete query
    $sql = "DELETE FROM material_rates WHERE id = ?";

    try {
        // Prepare the statement
        $stmt = mysqli_prepare($conn, $sql);

        // Bind parameters
        mysqli_stmt_bind_param($stmt, "i", $materialId);

        // Execute the statement
        if (mysqli_stmt_execute($stmt)) {
            // Delete successful, now unlink associated image
            $query = "SELECT material_image FROM material_rates WHERE id = ?";
            $stmt_image = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt_image, "i", $materialId);
            mysqli_stmt_execute($stmt_image);
            mysqli_stmt_store_result($stmt_image);

            if (mysqli_stmt_num_rows($stmt_image) > 0) {
                mysqli_stmt_bind_result($stmt_image, $material_image);
                mysqli_stmt_fetch($stmt_image);
                unlink($material_image); // Unlink the associated image
            }

            $_SESSION['message'][] = array("type" => "success", "content" => "Material Data deleted successfully!");
        } else {
            throw new Exception("Failed to delete the data. " . mysqli_error($conn));
        }
    } catch (Exception $e) {
        $_SESSION['message'][] = array("type" => "error", "content" => "Error: " . $e->getMessage());
    } finally {
        // Close the statement and connection
        mysqli_stmt_close($stmt);
        mysqli_stmt_close($stmt_image); // Close the image statement
        // mysqli_close($conn);
        header("location: material-rates.php");
        exit(); // Ensure script execution stops after header redirection
    }
} else {
    $_SESSION['message'][] = array("type" => "error", "content" => "Invalid material ID!");
    header("location: material-rates.php");
    exit(); // Ensure script execution stops after header redirection
}
