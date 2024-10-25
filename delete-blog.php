<?php
include 'layouts/session.php';
include 'layouts/config.php'; // Make sure you include your database configuration file
include 'layouts/functions.php';

// Check if ID is set and numeric
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $blogID = $_GET['id'];

    // Fetch associated images before deleting the blog post
    $query = "SELECT front_image, inner_image_1, inner_image_2 FROM blog_posts WHERE id = ?";
    $stmt_image = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt_image, "i", $blogID);
    mysqli_stmt_execute($stmt_image);
    mysqli_stmt_bind_result($stmt_image, $frontImage, $innerImage1, $innerImage2);
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
            if (!empty($innerImage1) && file_exists($innerImage1)) {
                if (!unlink($innerImage1)) {
                    throw new Exception("Failed to delete inner image 1.");
                }
            }
            if (!empty($innerImage2) && file_exists($innerImage2)) {
                if (!unlink($innerImage2)) {
                    throw new Exception("Failed to delete inner image 2.");
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
