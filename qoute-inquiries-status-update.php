<?php
include 'layouts/session.php';
include 'layouts/main.php';
include 'layouts/config.php';
include 'layouts/functions.php';

// Check if the user has the necessary permissions
if (!hasPermission('view_inquiry') || !hasPermission('change_inquiry_status') || !hasPermission('responde_to_inquiry')) {
    header('Location: index.php');
    exit();
}

$inquiry = [];
if (isset($_GET['id'])) {
    $inquiry_id = intval($_GET['id']);

    try {
        // Start the transaction
        $conn->begin_transaction();

        // Query to fetch the inquiry details
        $queryInquiry = "SELECT * FROM inquiries WHERE id = ?;";
        $stmtInquiry = $conn->prepare($queryInquiry);
        $stmtInquiry->bind_param('i', $inquiry_id);
        $stmtInquiry->execute();
        $resultInquiry = $stmtInquiry->get_result();

        if ($resultInquiry->num_rows > 0) {
            $inquiry = $resultInquiry->fetch_assoc();
        } else {
            header("Location: qoute-inquiries.php");
            exit();
        }

        // Commit the transaction
        $conn->commit();
    } catch (Exception $e) {
        // Rollback the transaction on error
        $conn->rollback();
        $_SESSION['message'] = ['type' => 'error', 'content' => $e->getMessage()];
    }

    // Close the statement
    $stmtInquiry->close();
} else {
    header("Location: qoute-inquiries.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
    $new_status = $_POST['status'];

    try {
        // Start the transaction
        $conn->begin_transaction();

        // Update the status of the inquiry
        $queryUpdate = "UPDATE inquiries SET status = ? WHERE id = ?;";
        $stmtUpdate = $conn->prepare($queryUpdate);
        $stmtUpdate->bind_param('si', $new_status, $inquiry_id);
        $stmtUpdate->execute();

        // Commit the transaction
        $conn->commit();

        // Redirect after successful update
        header("Location: qoute-inquiries.php");
        exit();
    } catch (Exception $e) {
        // Rollback the transaction on error
        $conn->rollback();
        $_SESSION['message'] = ['type' => 'error', 'content' => $e->getMessage()];
        header("Location: qoute-inquiries.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Inquiries | Mohsin Shaheen Construction Company</title>
    <?php include 'layouts/title-meta.php'; ?>
    <?php include 'layouts/head-css.php'; ?>

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
                                        <li class="breadcrumb-item"><a href="javascript:void(0);">mscc.pk</a></li>
                                        <li class="breadcrumb-item"><a href="javascript:void(0);">Inquiries</a></li>
                                        <li class="breadcrumb-item active">Qoute Inquiries</li>
                                    </ol>
                                </div>
                                <h4 class="page-title">Status Update</h4>
                            </div>
                        </div>
                    </div>

                    <div class="row justify-content-center">
                        <div class="col-lg-12">
                            <?php displaySessionMessage(); ?>

                            <h2 class="text-center mb-4">Qoute Inquiries Information</h2>
                            <div class="card custom-card shadow-sm w-100">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 ">
                                            <p style="font-size: 1rem; margin-left: 50px;"><strong>Name:</strong> <?php echo htmlspecialchars($inquiry['name']); ?></p>
                                            <p style="font-size: 1rem;margin-left: 50px;"><strong>WhatsApp Number:</strong> <?php echo htmlspecialchars($inquiry['whatsapp_number']); ?></p>
                                            <p style="font-size: 1rem;margin-left: 50px;"><strong>Plot Location:</strong> <?php echo htmlspecialchars($inquiry['plot_location']); ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p style="font-size: 1rem;"><strong>Plot Size:</strong> <?php echo htmlspecialchars($inquiry['plot_size']); ?></p>
                                            <p style="font-size: 1rem;"><strong>Quotation Type</strong> <?php echo htmlspecialchars($inquiry['quote_type']); ?></p>
                                            <p style="font-size: 1rem;"><strong>Structure Type</strong> <?php echo htmlspecialchars($inquiry['structure_type']); ?></p>
                                            <p style="font-size: 1rem;"><strong>Created At:</strong> <?php echo htmlspecialchars($inquiry['created_at']); ?></p>
                                        </div>
                                    </div>
                                    <div class="col-md-3" style="margin-top:-40px;">
                                        <form method="POST">
                                            <strong style="font-size: 1rem;  margin-left: 50px;">Status:</strong>
                                            <select name="status" class="form-control mb-2" style="font-size: 1rem;  margin-left: 50px;">
                                                <option value="pending" <?php echo ($inquiry['status'] === 'pending') ? 'selected' : ''; ?>>Pending</option>
                                                <option value="completed" <?php echo ($inquiry['status'] === 'completed') ? 'selected' : ''; ?>>Completed</option>
                                            </select>
                                            <button type="submit" class="btn btn-primary" style="font-size: 1rem;  margin-left: 50px;">Save Status</button>
                                        </form>
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
    <script src="assets/js/app.min.js"></script>~

</body>

</html>