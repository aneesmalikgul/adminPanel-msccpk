<?php
include 'layouts/session.php';
include 'layouts/main.php';
include 'layouts/config.php';
include 'layouts/functions.php';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Comments | Mohsin Shaheen Construction Company</title>
    <?php include 'layouts/title-meta.php'; ?>
    <?php include 'layouts/head-css.php'; ?>
    <style></style>

</head>

<body>
    <div class="wrapper">
        <?php include 'layouts/menu.php'; ?>
        <div class="content-page">
            <div class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box">
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">mscc.pk</a></li>
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Comments</a></li>
                                        <li class="breadcrumb-item active">Blog Comments</li>
                                    </ol>
                                </div>
                                <h4 class="page-title">Comments</h4>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-xl-12">
                            <?php displaySessionMessage(); ?>

                            <div class="card">
                                <div class="card-body">
                                    <!-- Dropdown for filtering -->
                                    <form method="get" id="filter-form">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <label for="status-filter">Filter by Status:</label>
                                            </div>
                                            <div class="col-md-9">
                                                <select id="status-filter" name="status" class="form-control">
                                                    <option value="pending" selected>Pending</option>
                                                    <option value="approved">Approved</option>
                                                    <option value="rejected">Rejected</option>
                                                </select>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="header-title">Comments</h4>
                                    <p class="text-muted fs-14"></p>
                                    <div class="table-responsive-sm">
                                        <div id="spinner-container" class="d-flex justify-content-center align-items-center" style="height: 100%; display: none;">
                                            <div id="loading-spinner" class="spinner-border m-2" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                        </div>
                                        <table id="scroll-horizontal-datatable" class="table table-striped w-100 nowrap">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Blog Name</th>
                                                    <th>Commentor Name</th>
                                                    <th>Commentor Email</th>
                                                    <th>Website</th>
                                                    <th>Comment</th>
                                                    <th>Status</th>
                                                    <th>Created At</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody id="comments-table-body">
                                                <!-- AJAX content will be loaded here -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include 'layouts/footer.php'; ?>
        </div>
    </div>
    <?php include 'layouts/right-sidebar.php'; ?>
    <?php include 'layouts/footer-scripts.php'; ?>
    <script src="assets/js/app.min.js"></script>
    <script>
        $(document).ready(function() {
            "use strict";
            // Initialize DataTable
            var dataTable = $("#scroll-horizontal-datatable").DataTable({
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

            // Load table data on page load
            loadComments('pending');

            // Handle dropdown change event
            $('#status-filter').on('change', function() {
                var status = $(this).val();
                loadComments(status);
            });

            function loadComments(status) {
                // Show the loading spinner
                $('#loading-spinner').show();

                $.ajax({
                    url: 'fetch-comments.php',
                    type: 'GET',
                    data: {
                        status: status
                    },
                    success: function(response) {
                        var table = $("#scroll-horizontal-datatable").DataTable();
                        table.clear().destroy(); // Clear and destroy previous DataTable
                        $('#comments-table-body').html(response); // Load new data
                        table = $("#scroll-horizontal-datatable").DataTable({ // Re-initialize DataTable
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
                        // Hide the loading spinner after data is loaded
                        $('#loading-spinner').hide();
                    },
                    error: function() {
                        // Hide the spinner in case of an error as well
                        $('#loading-spinner').hide();
                    }
                });
            }
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