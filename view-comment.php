<?php
include 'layouts/session.php';
include 'layouts/main.php';
include 'layouts/config.php'; // This file should include the database connection
include 'layouts/functions.php';

// Fetch the comment ID from the URL
$commentId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Initialize variables for comment details
$comment = [];
$error = '';

// Handle form submission for status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['approve']) || isset($_POST['reject']))) {
    try {
        // Check if ID is provided
        if ($commentId > 0) {
            // Determine the status based on the button clicked
            $status = '';
            if (isset($_POST['approve'])) {
                $status = 'approved';
            } elseif (isset($_POST['reject'])) {
                $status = 'rejected';
            }

            if ($status) {
                // Prepare SQL query to update the comment status
                $query = "UPDATE comments SET status = ? WHERE id = ?";
                if ($stmt = $conn->prepare($query)) {
                    $stmt->bind_param('si', $status, $commentId);
                    $stmt->execute();

                    if ($stmt->affected_rows > 0) {
                        $_SESSION['message'][] = array("type" => "success", "content" => "Comment status updated successfully!");
                    } else {
                        $_SESSION['message'][] = array("type" => "error", "content" => "Failed to update comment status.");
                    }
                    $stmt->close();
                } else {
                    $_SESSION['message'][] = array("type" => "error", "content" => "Error preparing statement: " . $conn->error);
                }
            } else {
                $_SESSION['message'][] = array("type" => "error", "content" => "Invalid action.");
            }
        } else {
            $_SESSION['message'][] = array("type" => "error", "content" => "Invalid comment ID.");
        }

        // Redirect after status update
        header("Location: blog-comments.php");
        exit();
    } catch (Exception $e) {
        $_SESSION['message'][] = array("type" => "error", "content" => $e->getMessage());
        header("Location: view-comment.php?id=" . $commentId);
        exit();
    }
}

try {
    // Start transaction
    $conn->begin_transaction();

    // Prepare SQL query to fetch the comment details
    if ($commentId > 0) {
        $query = "SELECT c.id, c.blog_post_id, b.title AS blog_title, c.name, c.email, c.website, c.comment, c.status, c.created_at FROM comments c 
                  INNER JOIN blog_posts b ON c.blog_post_id = b.id
                  WHERE c.id = ?";
        if ($stmt = $conn->prepare($query)) {
            $stmt->bind_param('i', $commentId);
            $stmt->execute();

            // Bind the result variables to match the selected fields
            $stmt->bind_result($id, $blog_post_id, $blog_title, $name, $email, $website, $commentContent, $status, $created_at);

            // Fetch the data
            if ($stmt->fetch()) {
                $comment = [
                    'id' => $id,
                    'blog_post_id' => $blog_post_id,
                    'blog_title' => $blog_title,
                    'name' => $name,
                    'email' => $email,
                    'website' => $website,
                    'comment' => $commentContent,
                    'status' => $status,
                    'created_at' => $created_at,
                ];
            } else {
                $error = "Comment not found.";
                throw new Exception($error);
            }

            $stmt->close();
        } else {
            $error = "Error preparing statement: " . $conn->error;
            throw new Exception($error);
        }
    } else {
        $error = "Invalid comment ID.";
        throw new Exception($error);
    }

    // Commit transaction
    $conn->commit();
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();

    // Set error message and redirect
    $_SESSION['message'][] = array("type" => "error", "content" => $e->getMessage());
    header("Location: blog-comment.php");
    exit();
} finally {
    // Close connection
    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <title>View Comment | Mohsin Shaheen Construction Company</title>
    <?php include 'layouts/title-meta.php'; ?>
    <?php include 'layouts/head-css.php'; ?>
</head>

<body>
    <!-- Begin page -->
    <div class="wrapper">
        <?php include 'layouts/menu.php'; ?>

        <!-- ============================================================== -->
        <!-- Start Page Content here -->
        <!-- ============================================================== -->

        <div class="content-page">
            <div class="content">
                <!-- Start Content-->
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box">
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">mscc.pk</a></li>
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Comments</a></li>
                                        <li class="breadcrumb-item active">View Comment</li>
                                    </ol>
                                </div>
                                <h4 class="page-title">View Comment</h4>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <?php if (!empty($error)): ?>
                                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                                    <?php else: ?>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <h5>Commentator Information</h5>
                                                <p><b>Blog Title:</b> <?php echo htmlspecialchars($comment['blog_title']); ?></p>
                                                <p><b>Blog Post ID:</b> <?php echo htmlspecialchars($comment['blog_post_id']); ?></p>
                                                <p><b>Name:</b> <?php echo htmlspecialchars($comment['name']); ?></p>
                                                <p><b>Email:</b> <?php echo htmlspecialchars($comment['email']); ?></p>
                                                <p><b>Website:</b> <?php echo htmlspecialchars($comment['website']); ?></p>
                                            </div>
                                            <div class="col-sm-6">
                                                <h5>Comment Information</h5>
                                                <p><b>Comment:</b><br> <?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>
                                                <p><b>Status:</b> <?php echo htmlspecialchars($comment['status']); ?></p>
                                                <p><b>Created At:</b> <?php echo htmlspecialchars(date('d-M-Y', strtotime($comment['created_at']))); ?></p>
                                            </div>
                                        </div>

                                        <!-- Action Buttons -->
                                        <form method="post" class="mt-4">
                                            <div class="text-end">
                                                <button type="submit" name="approve" class="btn btn-success">Approve</button>
                                                <button type="submit" name="reject" class="btn btn-danger">Reject</button>
                                            </div>
                                        </form>
                                    <?php endif; ?>

                                </div> <!-- end card-body -->
                            </div> <!-- end card -->
                        </div> <!-- end col -->
                    </div> <!-- end row -->
                </div> <!-- container -->
            </div> <!-- content -->

            <?php include 'layouts/footer.php'; ?>
        </div>
    </div>
    <?php include 'layouts/right-sidebar.php'; ?>
    <?php include 'layouts/footer-scripts.php'; ?>
    <script src="assets/js/app.min.js"></script>
    <script>
        $(document).ready(function() {
            "use strict";
            $("#scroll-horizontal-datatable").DataTable({
                scrollX: true,
                language: {
                    paginate: {
                        previous: "<i class='ri-arrow-left-s-line'>",
                        next: "<i class='ri-arrow-right-s-line'>",
                    },
                },
                drawCallback: function() {
                    $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
                },
            });
        });
    </script>
    <script>
        <?php
        if (isset($_SESSION['message'])) {
            foreach ($_SESSION['message'] as $message) {
                echo "toastr." . $message['type'] . "('" . $message['content'] . "');";
            }
            unset($_SESSION['message']); // Clear messages after displaying
        }
        ?>
    </script>
</body>

</html>