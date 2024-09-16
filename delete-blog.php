<?php
include 'layouts/session.php';
include 'layouts/config.php'; // Make sure you include your database configuration file
include 'layouts/functions.php';

// Check if ID is set and numeric
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $blogID = $_GET['id'];

    // Fetch associated images before deleting the blog post
    $query = "SELECT front_image, main_image FROM blog_posts WHERE id = ?";
    $stmt_image = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt_image, "i", $blogID);
    mysqli_stmt_execute($stmt_image);
    mysqli_stmt_bind_result($stmt_image, $frontImage, $mainImage);
    mysqli_stmt_fetch($stmt_image);
    mysqli_stmt_close($stmt_image);

    // Prepare delete query
    $sql = "DELETE FROM blog_posts WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $blogID);

    try {
        // Execute the delete statement
        if (mysqli_stmt_execute($stmt)) {
            // Delete successful, now unlink associated images
            if (!empty($frontImage) && file_exists($frontImage)) {
                if (!unlink($frontImage)) {
                    throw new Exception("Failed to delete front image.");
                }
            }
            if (!empty($mainImage) && file_exists($mainImage)) {
                if (!unlink($mainImage)) {
                    throw new Exception("Failed to delete main image.");
                }
            }

            $_SESSION['message'][] = array("type" => "success", "content" => "Blog deleted successfully!");
        } else {
            throw new Exception("Failed to delete the data. " . mysqli_error($conn));
        }
    } catch (Exception $e) {
        $_SESSION['message'][] = array("type" => "error", "content" => "Error: " . $e->getMessage());
    } finally {
        // Close the statement and connection
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        header("location: all-blogs.php");
        exit(); // Ensure script execution stops after header redirection
    }
} else {
    $_SESSION['message'][] = array("type" => "error", "content" => "Invalid blog ID!");
    header("location: all-blogs.php");
    exit(); // Ensure script execution stops after header redirection
}
