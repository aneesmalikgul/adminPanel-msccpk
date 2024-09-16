<?php include 'layouts/session.php'; ?>
<?php include 'layouts/main.php'; ?>
<?php include 'layouts/config.php'; ?>
<?php include 'layouts/functions.php'; ?>

<head>
    <title>Drawing Inquiries | Mohsin Shaheen Construction Company</title>
    <?php include 'layouts/title-meta.php'; ?>

    <?php include 'layouts/head-css.php'; ?>
    <style>

    </style>
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

                    <!-- start page title -->
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box">
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">mscc.pk</a></li>
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Inquiries</a></li>
                                        <li class="breadcrumb-item active">Drawing Inquiries</li>
                                    </ol>
                                </div>
                                <h4 class="page-title">Drawing Inquiries</h4>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card">
                                <div class="card-body">

                                    <h4 class="header-title">All Drawing Inquiries</h4>
                                    <p class="text-muted fs-14"></p>

                                    <div class="table-responsive-sm">
                                        <table id="scroll-horizontal-datatable" class="table table-striped w-100 nowrap">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Name</th>
                                                    <th>WhatsApp Number</th>
                                                    <th>Plot Location</th>
                                                    <th>Plot Size</th>
                                                    <th>Drawing Type</th>
                                                    <th>Created At</th>
                                                    <th>Status</th>

                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $query = "SELECT * FROM inquiries WHERE is_drawing = 1";
                                                $result = mysqli_query($conn, $query);

                                                if ($result) {
                                                    while ($row = mysqli_fetch_assoc($result)) {
                                                        echo "<tr>";
                                                        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                                                        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                                                        echo "<td>" . htmlspecialchars($row['whatsapp_number']) . "</td>";
                                                        echo "<td>" . htmlspecialchars($row['plot_location']) . "</td>";
                                                        echo "<td>" . htmlspecialchars($row['plot_size']) . "</td>";
                                                        echo "<td>" . htmlspecialchars($row['drawing_type']) . "</td>";
                                                        echo "<td>" . htmlspecialchars(date('d-M-Y', strtotime($row['created_at']))) . "</td>";
                                                        echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                                                        echo "</tr>";
                                                    }
                                                } else {
                                                    echo "<tr><td colspan='8'>No Drawing Inquiries Found</td></tr>";
                                                }

                                                mysqli_close($conn);
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

        <!-- ============================================================== -->
        <!-- End Page content -->
        <!-- ============================================================== -->
    </div>

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
    </script>
    <script>
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