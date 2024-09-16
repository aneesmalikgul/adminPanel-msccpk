<?php include 'layouts/session.php'; ?>
<?php include 'layouts/main.php'; ?>
<?php include 'layouts/config.php'; ?>

<head>
    <title>Orders Management | Cakepalace Orders</title>
    <?php include 'layouts/title-meta.php'; ?>

    <?php include 'layouts/head-css.php'; ?>
    <style>
        .custom-button {
            background-color: #d8a43e;
            color: white;
            /* Set text color to white */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            /* Add drop shadow */
            width: 50%;
            /* Make button 50% wide */
            padding: 10px 20px;
            /* Adjust padding for better width */
            border: none;
            border-radius: 5px;
            /* Optional: add rounded corners */
            font-size: 16px;
            /* Adjust font size for better readability */
        }

        .custom-button:hover {
            background-color: #c49336;
            /* Slightly darker shade for hover effect */
            color: white;
        }

        .text-center {
            text-align: center;
        }
    </style>
</head>
<?php
if (isset($_GET['id'])) {
    $order_id = intval($_GET['id']); // Sanitize the input

    if ($order_id > 0) {
        // Fetch current order details
        $query = "SELECT * FROM cake_order WHERE order_id = $order_id";
        $result = mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            $order = mysqli_fetch_assoc($result);
        } else {
            // Order ID not found in the table
            $_SESSION['message'][] = array("type" => "error", "content" => "Order not found!");
            header("Location: cake-orders.php");
            exit();
        }
    } else {
        // Invalid Order ID
        $_SESSION['message'][] = array("type" => "error", "content" => "Invalid Order ID!");
        header("Location: cake-orders.php");
        exit();
    }
} else {
    // Order ID not provided
    $_SESSION['message'][] = array("type" => "error", "content" => "Order ID not provided!");
    header("Location: cake-orders.php");
    exit();
}
?>

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
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Cakepalace</a></li>
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Orders</a></li>
                                        <li class="breadcrumb-item active">Orders Management</li>
                                    </ol>
                                </div>
                                <h4 class="page-title">Orders Management</h4>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xl-12">
                            <div class="text-center">
                                <h2 class="header-title">Cake Order Form</h2>
                                <p class="text-muted fs-14"> </p>
                            </div>
                            <div class="card">
                                <div class="card-body">

                                    <div>
                                        <form action="<?php echo "cake-orders.php" . "?id=" . $order_id; ?>" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                                            <div class="row mb-3">
                                                <h3>Customer Information</h3>
                                                <div class="col-lg-4">
                                                    <div class="mb-3">
                                                        <label for="customerName" class="form-label">Name</label>
                                                        <input type="text" id="customerName" name="customerName" class="form-control" value="<?php echo htmlspecialchars($order['customer_name']); ?>" required>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4">
                                                    <div class="mb-3">
                                                        <label for="customerAddress" class="form-label">Address</label>
                                                        <input type="text" id="customerAddress" name="customerAddress" class="form-control" value="<?php echo htmlspecialchars($order['customer_address']); ?>" required>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4">
                                                    <div class="mb-3">
                                                        <label for="customerContact" class="form-label">Contact Number</label>
                                                        <input type="number" id="customerContact" name="customerContact" class="form-control" value="<?php echo htmlspecialchars($order['customer_contact']); ?>" required>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <h3>Event Information</h3>
                                                <div class="col-lg-4">
                                                    <div class="mb-3">
                                                        <label for="eventDate" class="form-label">Event Date</label>
                                                        <input type="date" id="eventDate" name="eventDate" class="form-control" value="<?php echo htmlspecialchars($order['event_date']); ?>" required>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4">
                                                    <div class="mb-3">
                                                        <label for="eventTime" class="form-label">Event Time</label>
                                                        <input type="time" id="eventTime" name="eventTime" class="form-control" value="<?php echo htmlspecialchars($order['delivery_time']); ?>" required>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4">
                                                    <div class="mb-3">
                                                        <label for="orderDate" class="form-label">Order Date</label>
                                                        <input type="date" id="orderDate" name="orderDate" class="form-control" value="<?php echo htmlspecialchars($order['order_date']); ?>" required>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <h3>Cake Information</h3>
                                                <div class="col-lg-4">
                                                    <div class="mb-3">
                                                        <label for="cakeWeight" class="form-label">Cake Weight (kg)</label>
                                                        <input type="number" id="cakeWeight" name="cakeWeight" class="form-control" value="<?php echo htmlspecialchars($order['cake_weight']); ?>" required>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4">
                                                    <div class="mb-3">
                                                        <label for="cakeFlavour" class="form-label">Cake Flavor</label>
                                                        <input type="text" id="cakeFlavour" name="cakeFlavour" class="form-control" value="<?php echo htmlspecialchars($order['cake_flavor']); ?>" required>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4">
                                                    <div class="mb-3">
                                                        <label for="cakePrice" class="form-label">Cake Price</label>
                                                        <input type="number" id="cakePrice" name="cakePrice" class="form-control" value="<?php echo htmlspecialchars($order['cake_price']); ?>" required>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4">
                                                    <div class="mb-3">
                                                        <label for="deliveryCharges" class="form-label">Delivery Charges</label>
                                                        <input type="number" id="deliveryCharges" name="deliveryCharges" class="form-control" value="<?php echo htmlspecialchars($order['delivery_charges']); ?>" required>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4">
                                                    <div class="mb-3">
                                                        <label for="deliveryChanges" class="form-label">Delivery Changes</label>
                                                        <input type="text" id="deliveryChanges" name="deliveryChanges" class="form-control" value="<?php echo htmlspecialchars($order['delivery_changes']); ?>" required>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <h3>Images</h3>
                                                <div class="col-lg-4">
                                                    <div class="mb-3">
                                                        <label for="cakeImage_1" class="form-label">Cake Image 1</label>
                                                        <input type="file" id="cakeImage_1" name="cakeImage_1" class="form-control" value="<?php echo htmlspecialchars($order['cake_image_1']); ?>">
                                                        <img id="cakeImagePreview_1" src="<?php echo htmlspecialchars($order['cake_image_1']); ?>" width="100">
                                                    </div>
                                                </div>
                                                <div class="col-lg-4">
                                                    <div class="mb-3">
                                                        <label for="cakeImage_2" class="form-label">Cake Image 2</label>
                                                        <input type="file" id="cakeImage_2" name="cakeImage_2" class="form-control" value="<?php echo htmlspecialchars($order['cake_image_2']); ?>">
                                                        <img id="cakeImagePreview_2" src="<?php echo htmlspecialchars($order['cake_image_2']); ?>" width="100">
                                                    </div>
                                                </div>
                                                <div class="col-lg-4">
                                                    <div class="mb-3">
                                                        <label for="cakeImage_3" class="form-label">Cake Image 3</label>
                                                        <input type="file" id="cakeImage_3" name="cakeImage_3" class="form-control" value="<?php echo htmlspecialchars($order['cake_image_3']); ?>">
                                                        <img id="cakeImagePreview_3" src="<?php echo htmlspecialchars($order['cake_image_3']); ?>" width="100">
                                                    </div>
                                                </div>
                                                <div class="col-lg-4">
                                                    <div class="mb-3">
                                                        <label for="actualImage_1" class="form-label">Actual Image 1</label>
                                                        <input type="file" id="actualImage_1" name="actualImage_1" class="form-control" value="<?php echo htmlspecialchars($order['actual_image_1']); ?>">
                                                        <img id="actual_ImagePreview_1" src="<?php echo htmlspecialchars($order['actual_image_1']); ?>" width="100">
                                                    </div>
                                                </div>
                                                <div class="col-lg-4">
                                                    <div class="mb-3">
                                                        <label for="actualImage_2" class="form-label">Actual Image 2</label>
                                                        <input type="file" id="actualImage_2" name="actualImage_2" class="form-control" value="<?php echo htmlspecialchars($order['actual_image_2']); ?>">
                                                        <img id="actual_ImagePreview_2" src="<?php echo htmlspecialchars($order['actual_image_2']); ?>" width="100">
                                                    </div>
                                                </div>

                                            </div>

                                            <div class="row">
                                                <div class="col-12 text-center">
                                                    <button type="submit" name="btnUpdateOrder" class="mb-3 btn btn-cake custom-button">Update Order</button>
                                                    <br>
                                                    <a href="cake-orders.php" class="btn btn-secondary">Cancel</a>
                                                </div>
                                            </div>
                                        </form>
                                    </div>

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
    <script>
        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                };
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.src = "";
            }
        }
    </script>

</body>

</html>