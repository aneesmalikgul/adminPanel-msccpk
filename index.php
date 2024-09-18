<?php include 'layouts/session.php'; ?>
<?php include 'layouts/config.php'; ?>
<?php include 'layouts/functions.php'; ?>
<?php include 'layouts/main.php'; ?>

<head>

    <title>Dashboard | Mohsin Shaheen Construction Company</title>
    <?php include 'layouts/title-meta.php'; ?>

    <!-- Daterangepicker css -->
    <link rel="stylesheet" href="assets/vendor/daterangepicker/daterangepicker.css">

    <!-- Vector Map css -->
    <link rel="stylesheet" href="assets/vendor/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.css">

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
                            <?php
                            if (isset($_SESSION['message'])) { ?>
                                <div class="mt-3">
                                    <?php displaySessionMessage(); ?>
                                </div>
                            <?php } ?>
                            <div class="page-title-box">
                                <div class="page-title-right">
                                    <form class="d-flex">
                                        <div class="input-group">
                                            <input type="text" class="form-control shadow border-0" id="dash-daterange">
                                            <span class="input-group-text bg-primary border-primary text-white">
                                                <i class="ri-calendar-todo-fill fs-13"></i>
                                            </span>
                                        </div>
                                        <a href="javascript: void(0);" class="btn btn-primary ms-2">
                                            <i class="ri-refresh-line"></i>
                                        </a>
                                    </form>
                                </div>
                                <h4 class="page-title">Dashboard</h4>
                            </div>
                        </div>
                    </div>

                    <div class="row row-cols-1 row-cols-xxl-5 row-cols-lg-3 row-cols-md-2">
                        <div class="col">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <h5 class="text-muted fw-normal mt-0" title="Number of Customers">Customers</h5>
                                            <h3 class="my-3">54,214</h3>
                                            <p class="mb-0 text-muted text-truncate">
                                                <span class="badge bg-success me-1"><i class="ri-arrow-up-line"></i> 2,541</span>
                                                <span>Since last month</span>
                                            </p>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <div id="widget-customers" class="apex-charts" data-colors="#47ad77,#e3e9ee"></div>
                                        </div>
                                    </div>
                                </div> <!-- end card-body-->
                            </div> <!-- end card-->
                        </div> <!-- end col-->

                        <div class="col">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <h5 class="text-muted fw-normal mt-0" title="Number of Orders">Orders</h5>
                                            <h3 class="my-3">7,543</h3>
                                            <p class="mb-0 text-muted text-truncate">
                                                <span class="badge bg-danger me-1"><i class="ri-arrow-down-line"></i> 1.08%</span>
                                                <span>Since last month</span>
                                            </p>
                                        </div>
                                        <div id="widget-orders" class="apex-charts" data-colors="#3e60d5,#e3e9ee"></div>
                                    </div>
                                </div> <!-- end card-body-->
                            </div> <!-- end card-->
                        </div> <!-- end col-->

                        <div class="col">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <h5 class="text-muted fw-normal mt-0" title="Average Revenue">Revenue</h5>
                                            <h3 class="my-3">$9,254</h3>
                                            <p class="mb-0 text-muted text-truncate">
                                                <span class="badge bg-danger me-1"><i class="ri-arrow-down-line"></i> 7.00%</span>
                                                <span>Since last month</span>
                                            </p>
                                        </div>
                                        <div id="widget-revenue" class="apex-charts" data-colors="#16a7e9,#e3e9ee"></div>
                                    </div>

                                </div> <!-- end card-body-->
                            </div> <!-- end card-->
                        </div> <!-- end col-->

                        <div class="col col-lg-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <h5 class="text-muted fw-normal mt-0" title="Growth">Growth</h5>
                                            <h3 class="my-3">+ 20.6%</h3>
                                            <p class="mb-0 text-muted text-truncate">
                                                <span class="badge bg-success me-1"><i class="ri-arrow-up-line"></i> 4.87%</span>
                                                <span>Since last month</span>
                                            </p>
                                        </div>
                                        <div id="widget-growth" class="apex-charts" data-colors="#ffc35a,#e3e9ee"></div>
                                    </div>

                                </div> <!-- end card-body-->
                            </div> <!-- end card-->
                        </div> <!-- end col-->
                        <div class="col col-lg-6 col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <h5 class="text-muted fw-normal mt-0" title="Conversation Ration">Conversation</h5>
                                            <h3 class="my-3">9.62%</h3>
                                            <p class="mb-0 text-muted text-truncate">
                                                <span class="badge bg-success me-1"><i class="ri-arrow-up-line"></i> 3.07%</span>
                                                <span>Since last month</span>
                                            </p>
                                        </div>
                                        <div id="widget-conversation" class="apex-charts" data-colors="#f15776,#e3e9ee"></div>
                                    </div>

                                </div> <!-- end card-body-->
                            </div> <!-- end card-->
                        </div> <!-- end col-->
                    </div> <!-- end row -->


                    <!-- end row -->

                </div>
                <!-- container -->

            </div>
            <!-- content -->

            <?php include 'layouts/footer.php'; ?>

        </div>

        <!-- ============================================================== -->
        <!-- End Page content -->
        <!-- ============================================================== -->

    </div>
    <!-- END wrapper -->

    <?php include 'layouts/right-sidebar.php'; ?>

    <?php include 'layouts/footer-scripts.php'; ?>

    <!-- Daterangepicker js -->
    <script src="assets/vendor/daterangepicker/moment.min.js"></script>
    <script src="assets/vendor/daterangepicker/daterangepicker.js"></script>

    <!-- Apex Charts js -->
    <script src="assets/vendor/apexcharts/apexcharts.min.js"></script>

    <!-- Vector Map js -->
    <script src="assets/vendor/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.min.js"></script>
    <script src="assets/vendor/admin-resources/jquery.vectormap/maps/jquery-jvectormap-world-mill-en.js"></script>

    <!-- Dashboard App js -->
    <script src="assets/js/pages/demo.dashboard.js"></script>

    <!-- App js -->
    <script src="assets/js/app.min.js"></script>

    <script>
        $(document).ready(function() {
            <?php
            showSessionMessage();
            ?>
        });
    </script>

</body>

</html>