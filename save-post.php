<?php
include 'layouts/session.php';
include 'layouts/config.php';
include 'layouts/functions.php'; // Ensure to include functions if used for utility

// Initialize variables
$title = '';
$author = '';
$content = '';
$shortDesc = '';
$created_by = 'admin';  // You can replace this with the actual user if available
$frontImagePath = '';
$firstInnerImagePath = '';
$secondInnerImagePath = '';

try {
    // Check if form is submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Get form data and escape to prevent SQL injection
        $title = mysqli_real_escape_string($conn, $_POST['blogTitle']);
        $author = mysqli_real_escape_string($conn, $_POST['blogAuthor']);
        $content = mysqli_real_escape_string($conn, $_POST['blogContent']);
        $shortDesc = mysqli_real_escape_string($conn, $_POST['shortDesc']);

        // Validate form inputs
        if (empty($title) || empty($author) || empty($content) || empty($shortDesc)) {
            throw new Exception("All fields are required.");
        }

        // Directory to upload images
        $uploadDir = 'assets/uploads/blog_images/';

        // Check if the directory exists, if not, create it
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true)) {
            throw new Exception("Failed to create upload directory.");
        }

        // Start transaction
        mysqli_begin_transaction($conn);

        // Insert data into database without image paths
        $sql = "INSERT INTO blog_posts (title, author, content, short_desc, created_by) 
                VALUES ('$title', '$author', '$content', '$shortDesc', '$created_by')";

        if (!mysqli_query($conn, $sql)) {
            throw new Exception("Error inserting data: " . mysqli_error($conn));
        }

        // Get the inserted blog post ID
        $blogPostId = mysqli_insert_id($conn);

        // Current date and unique number
        $currentDate = date('Ymd');
        $uniqueNumber = uniqid();

        // File upload validation
        $allowedFileTypes = ['jpg', 'jpeg', 'png', 'gif'];
        $maxFileSize = 2 * 1024 * 1024; // 2MB

        function validateFile($file, $allowedFileTypes, $maxFileSize)
        {
            if ($file['error'] !== UPLOAD_ERR_OK) {
                throw new Exception("File upload error.");
            }
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            if (!in_array(strtolower($extension), $allowedFileTypes)) {
                throw new Exception("Invalid file type.");
            }
            if ($file['size'] > $maxFileSize) {
                throw new Exception("File size exceeds limit.");
            }
        }

        // Handle file uploads
        if (isset($_FILES['frontImage']) && $_FILES['frontImage']['error'] == UPLOAD_ERR_OK) {
            validateFile($_FILES['frontImage'], $allowedFileTypes, $maxFileSize);
            $frontImageName = "blog_{$blogPostId}_{$currentDate}_{$uniqueNumber}_front." . pathinfo($_FILES['frontImage']['name'], PATHINFO_EXTENSION);
            $frontImagePath = $uploadDir . $frontImageName;
            move_uploaded_file($_FILES['frontImage']['tmp_name'], $frontImagePath);
        }

        if (isset($_FILES['innerImage1']) && $_FILES['innerImage1']['error'] == UPLOAD_ERR_OK) {
            validateFile($_FILES['innerImage1'], $allowedFileTypes, $maxFileSize);
            $firstInnerImageName = "blog_{$blogPostId}_{$currentDate}_{$uniqueNumber}_inner1." . pathinfo($_FILES['innerImage1']['name'], PATHINFO_EXTENSION);
            $firstInnerImagePath = $uploadDir . $firstInnerImageName;
            move_uploaded_file($_FILES['innerImage1']['tmp_name'], $firstInnerImagePath);
        }

        if (isset($_FILES['innerImage2']) && $_FILES['innerImage2']['error'] == UPLOAD_ERR_OK) {
            validateFile($_FILES['innerImage2'], $allowedFileTypes, $maxFileSize);
            $secondInnerImageName = "blog_{$blogPostId}_{$currentDate}_{$uniqueNumber}_inner2." . pathinfo($_FILES['innerImage2']['name'], PATHINFO_EXTENSION);
            $secondInnerImagePath = $uploadDir . $secondInnerImageName;
            move_uploaded_file($_FILES['innerImage2']['tmp_name'], $secondInnerImagePath);
        }

        // Update database with image paths
        $sql = "UPDATE blog_posts SET front_image='$frontImagePath', inner_image_1='$firstInnerImagePath', inner_image_2='$secondInnerImagePath' 
                WHERE id='$blogPostId'";

        if (!mysqli_query($conn, $sql)) {
            throw new Exception("Error updating data: " . mysqli_error($conn));
        }

        // Commit transaction
        mysqli_commit($conn);

        // Set success message
        $_SESSION['message'][] = array("type" => "success", "content" => "Blog saved successfully!");
    }
} catch (Exception $e) {
    // Rollback transaction if any error occurs
    mysqli_rollback($conn);

    // Set error message
    $_SESSION['message'][] = array("type" => "error", "content" => "Error: " . $e->getMessage());
} finally {
    // Close the connection
    mysqli_close($conn);

    // Redirect to blog posts page
    header("Location: blog-posts.php");
    exit(); // Ensure script execution stops after header redirection
}
