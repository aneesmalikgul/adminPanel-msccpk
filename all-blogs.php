<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include necessary files and start session
include 'layouts/session.php';
include 'layouts/main.php';
include 'layouts/config.php';
include 'layouts/functions.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btnUpdateBlog'])) {
    $postId = mysqli_real_escape_string($conn, trim($_POST['postId']));
    $title = mysqli_real_escape_string($conn, trim($_POST['blogTitle']));
    $author = mysqli_real_escape_string($conn, trim($_POST['blogAuthor']));
    $shortDesc = mysqli_real_escape_string($conn, trim($_POST['shortDesc']));
    $content = mysqli_real_escape_string($conn, trim($_POST['blogContent']));
    $updatedBy = mysqli_real_escape_string($conn, $_SESSION['username']);

    if (empty($postId)) {
        $_SESSION['message'][] = array("type" => "error", "content" => "Post ID is empty.");
        header("Location: all-blogs.php");
        exit();
    }

    $frontImageFileName = "";
    $innerImageFileName_1 = "";
    $innerImageFileName_2 = "";

    $uploadDir = 'assets/uploads/blog_images/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Handle Front Image Upload
    if (isset($_FILES['frontImage']) && $_FILES['frontImage']['error'] == UPLOAD_ERR_OK) {
        $frontImageFileName = time() . '_' . basename($_FILES['frontImage']['name']);
        $frontTargetFilePath = $uploadDir . $frontImageFileName;
        move_uploaded_file($_FILES['frontImage']['tmp_name'], $frontTargetFilePath);
    }

    // Handle First Inner Image Upload
    if (isset($_FILES['inner_image_1']) && $_FILES['inner_image_1']['error'] == UPLOAD_ERR_OK) {
        $innerImageFileName_1 = time() . '_1_' . basename($_FILES['inner_image_1']['name']);
        $innerImageTargetFilePath_1 = $uploadDir . $innerImageFileName_1;
        move_uploaded_file($_FILES['inner_image_1']['tmp_name'], $innerImageTargetFilePath_1);
    }

    // Handle Second Inner Image Upload
    if (isset($_FILES['inner_image_2']) && $_FILES['inner_image_2']['error'] == UPLOAD_ERR_OK) {
        $innerImageFileName_2 = time() . '_2_' . basename($_FILES['inner_image_2']['name']);
        $innerImageTargetFilePath_2 = $uploadDir . $innerImageFileName_2;
        move_uploaded_file($_FILES['inner_image_2']['tmp_name'], $innerImageTargetFilePath_2);
    }

    // Build the SQL query
    $sql = "UPDATE blog_posts SET 
                title = '$title', 
                author = '$author', 
                content = '$content', 
                short_desc = '$shortDesc', 
                updated_by = '$updatedBy'";

    if (!empty($frontTargetFilePath)) {
        $sql .= ", front_image = '$frontTargetFilePath'";
    }

    if (!empty($innerImageTargetFilePath_1)) {
        $sql .= ", inner_image_1 = '$innerImageTargetFilePath_1'";
    }

    if (!empty($innerImageTargetFilePath_2)) {
        $sql .= ", inner_image_2 = '$innerImageTargetFilePath_2'";
    }

    $sql .= " WHERE id = '$postId'";

    try {
        if (mysqli_query($conn, $sql)) {
            $_SESSION['message'][] = array("type" => "success", "content" => "Blog post updated successfully!");
        } else {
            $error = mysqli_error($conn);
            throw new Exception("Failed to update the data. " . $error);
        }
    } catch (Exception $e) {
        $_SESSION['message'][] = array("type" => "error", "content" => "Error: " . $e->getMessage());
    } finally {
        // mysqli_close($conn);
        header("Location: all-blogs.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>All Blogs | Mohsin Shaheen Construction Company</title>
    <?php include 'layouts/title-meta.php'; ?>
    <?php include 'layouts/head-css.php'; ?>
</head>

<body>
    <!-- Begin page -->
    <div class="wrapper">
        <?php include 'layouts/menu.php'; ?>

        <!-- Start Page Content here -->
        <div class="content-page">
            <div class="content">
                <!-- Start Content-->
                <div class="container-fluid">
                    <!-- start page title -->
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box">
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">mscc.pk</a></li>
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Blogs</a></li>
                                        <li class="breadcrumb-item active">All Blogs</li>
                                    </ol>
                                </div>
                                <h4 class="page-title">All Blogs</h4>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xl-12">
                            <?php displaySessionMessage(); ?>

                            <div class="card">
                                <div class="card-body">
                                    <h4 class="header-title">All Blogs</h4>
                                    <div class="table-responsive-sm">
                                        <table id="scroll-horizontal-datatable" class="table table-striped w-100 nowrap">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Blog Title</th>
                                                    <th>Author</th>
                                                    <th>Created By</th>
                                                    <th>Created At</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $query = "SELECT * FROM blog_posts;";
                                                $result = mysqli_query($conn, $query);

                                                if ($result) {
                                                    while ($row = mysqli_fetch_assoc($result)) {
                                                        echo "<tr>";
                                                        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                                                        echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                                                        echo "<td>" . htmlspecialchars($row['author']) . "</td>";
                                                        echo "<td>" . htmlspecialchars($row['created_by']) . "</td>";
                                                        echo "<td>" . htmlspecialchars(date('d-M-Y', strtotime($row['created_at']))) . "</td>";
                                                        echo "<td>";
                                                        // Edit button
                                                        echo "<a href='edit-blog.php?id=" . urlencode($row['id']) . "' class='btn btn-warning'><i class='ri-pencil-line'></i></a>";
                                                        echo "  ";
                                                        // Delete button
                                                        echo "<a href='delete-blog.php?id=" . urlencode($row['id']) . "' class='btn btn-danger' ><i class='ri-delete-bin-line'></i></a>";
                                                        echo "</td>";
                                                        echo "</tr>";
                                                    }
                                                } else {
                                                    echo "<tr><td colspan='6'>No Blog Posts Found</td></tr>";
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div> <!-- end table-responsive-->
                                </div> <!-- end card body-->
                            </div> <!-- end card -->
                        </div><!-- end col-->
                    </div>
                    <!-- end row-->
                </div> <!-- container -->
            </div> <!-- content -->

            <?php include 'layouts/footer.php'; ?>
        </div>
        <!-- End Page content -->
    </div>
    <!-- END wrapper -->

    <?php include 'layouts/right-sidebar.php'; ?>
    <?php include 'layouts/footer-scripts.php'; ?>

    <!-- App js -->
    <script src="assets/js/app.min.js"></script>

    <script>
        $(document).ready(function() {
            "use strict";
            $("#scroll-horizontal-datatable").DataTable({
                scrollX: !0,
                language: {
                    paginate: {
                        previous: "<i class='ri-arrow-left-s-line'>",
                        next: "<i class='ri-arrow-right-s-line'>",
                    },
                },
                drawCallback: function() {
                    $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
                },
            })
        });

        $(document).ready(function() {
            <?php
            if (isset($_SESSION['message'])) {
                foreach ($_SESSION['message'] as $message) {
                    $type = $message['type'];
                    $content = $message['content'];
                    echo "toastr.$type('$content');";
                }
                unset($_SESSION['message']);
            }
            ?>
        });
    </script>
</body>

</html>