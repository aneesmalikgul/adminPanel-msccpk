<?php
include 'layouts/session.php';
include 'layouts/config.php';
// Assuming you have already started the session and included the database connection

if (isset($_GET['id'])) {
    try {
        $order_id = intval($_GET['id']); // Sanitize the input

        if ($order_id > 0) {
            // Fetch current order details to delete associated images
            $query = "SELECT * FROM cake_order WHERE order_id = $order_id";
            $result = mysqli_query($conn, $query);

            if ($result && mysqli_num_rows($result) > 0) {
                $cakeOrder = mysqli_fetch_assoc($result);

                // Delete associated images from the server
                $upload_dir = 'assets/uploads/';
                if (!empty($cakeOrder['cake_image_1'])) {
                    unlink($cakeOrder['cake_image_1']);
                }
                if (!empty($cakeOrder['cake_image_2'])) {
                    unlink($cakeOrder['cake_image_2']);
                }
                if (!empty($cakeOrder['cake_image_3'])) {
                    unlink($cakeOrder['cake_image_3']);
                }
                if (!empty($cakeOrder['actual_image_1'])) {
                    unlink($cakeOrder['actual_image_1']);
                }
                if (!empty($cakeOrder['actual_image_2'])) {
                    unlink($cakeOrder['actual_image_2']);
                }

                // Prepare the SQL delete query
                $query = "DELETE FROM cake_order WHERE order_id = $order_id";
                $result = mysqli_query($conn, $query);

                // Check if the record was deleted
                if ($result) {
                    $_SESSION['message'][] = array("type" => "success", "content" => "Order deleted successfully!");
                } else {
                    throw new Exception("Failed to delete order. Please try again.");
                }
            } else {
                // Order ID not found in the table
                $_SESSION['message'][] = array("type" => "error", "content" => "Order not found!");
            }
        } else {
            // Invalid Order ID
            $_SESSION['message'][] = array("type" => "error", "content" => "Invalid Order ID!");
        }
    } catch (Exception $e) {
        $_SESSION['message'][] = array("type" => "error", "content" => "Error: " . $e->getMessage());
    }

    // Close connection
    mysqli_close($conn);
    header("Location: cake-orders.php");
    exit();
}
