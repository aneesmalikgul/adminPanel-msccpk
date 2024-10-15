<?php
include 'layouts/session.php';
include 'layouts/config.php';
include 'layouts/functions.php';
include 'layouts/main.php';

if (!hasPermission('mark_attendance') || !hasPermission('view_attendance')) {
    header('Location: index.php');
    exit;
}



if ($_SERVER["REQUEST_METHOD"] == "POST" && (isset($_POST['btnCheckIn']) || isset($_POST['btnCheckOut']))) {
    // Fetch username from session
    if (isset($_SESSION['username'])) {
        $username = $_SESSION['username']; // Username from session
    } else {
        $_SESSION['message'][] = ["type" => "danger", "content" => "User session has expired. Please log in again."];
        header("Location:auth-login.php"); // Redirect to login page
        exit();
    }

    // Get user ID based on username from users table
    $userQuery = "SELECT id FROM users WHERE username = ?";
    $stmt = $conn->prepare($userQuery);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $userId = $user['id']; // User ID fetched from the database
    } else {
        $_SESSION['message'][] = ["type" => "danger", "content" => "User not found."];
        header("Location: auth-login.php");
        exit();
    }

    // Collect and sanitize inputs for date and time
    $date = $conn->real_escape_string($_POST['date']); // Date from form
    $time = $conn->real_escape_string($_POST['time']); // Time from form
    $currentDate = date('Y-m-d'); // Current date for checking today's records

    // Common data
    $createdBy = $userId; // Assuming the user is creating their own attendance
    $attendanceStatus = 'present'; // Default attendance status
    $createdAt = date('Y-m-d H:i'); // Current timestamp
    $updatedAt = date('Y-m-d H:i'); // Current timestamp

    // Start a transaction
    $conn->begin_transaction();

    try {
        if (isset($_POST['btnCheckIn'])) {
            // Check if the user has already checked in today
            $checkQuery = "SELECT * FROM attendance WHERE user_id = ? AND attendance_date = ?";
            $stmt = $conn->prepare($checkQuery);
            $stmt->bind_param("is", $userId, $currentDate);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 0) {
                // Convert check-in time to 12-hour format before storing
                $time12HourCheckIn = date("h:i A", strtotime($time));

                // Insert a new check-in record
                $insertQuery = "INSERT INTO attendance (user_id, attendance_date, check_in, attendance_status, created_at, created_by) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($insertQuery);
                $stmt->bind_param("issssi", $userId, $date, $time12HourCheckIn, $attendanceStatus, $createdAt, $createdBy);

                if ($stmt->execute()) {
                    $conn->commit();
                    $_SESSION['message'][] = ["type" => "success", "content" => "Check-in successful!"];
                } else {
                    throw new Exception("Failed to check in: " . $stmt->error);
                }
            } else {
                // User has already checked in today
                $_SESSION['message'][] = ["type" => "danger", "content" => "You have already checked in today."];
            }
        } elseif (isset($_POST['btnCheckOut'])) {
            // Check if the user has already checked out today
            $checkQuery = "SELECT * FROM attendance WHERE user_id = ? AND attendance_date = ? AND check_out IS NOT NULL";
            $stmt = $conn->prepare($checkQuery);
            $stmt->bind_param("is", $userId, $currentDate);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 0) {
                // Convert check-out time to 12-hour format before storing
                $time12HourCheckOut = date("h:i A", strtotime($time)); // Converts to 12-hour format

                // Update the check-out time for today's record
                $updateQuery = "UPDATE attendance SET check_out = ?, updated_at = ?, updated_by = ? WHERE user_id = ? AND attendance_date = ?";
                $stmt = $conn->prepare($updateQuery);
                $stmt->bind_param("ssiis", $time12HourCheckOut, $updatedAt, $createdBy, $userId, $currentDate);

                if ($stmt->execute()) {
                    if ($stmt->affected_rows > 0) {
                        $conn->commit();
                        $_SESSION['message'][] = ["type" => "success", "content" => "Check-out successful!"];
                    } else {
                        throw new Exception("No check-in record found for today.");
                    }
                } else {
                    throw new Exception("Failed to check out: " . $stmt->error);
                }
            } else {
                // User has already checked out today
                $_SESSION['message'][] = ["type" => "danger", "content" => "You have already checked out today. Only one check-out is allowed."];
            }
        }
    } catch (Exception $e) {
        // Rollback transaction in case of error
        $conn->rollback();
        $_SESSION['message'][] = ["type" => "danger", "content" => $e->getMessage()];
    }
}

?>



<head>
    <title>Manage Users | Mohsin Shaheen Construction Company</title>
    <?php include 'layouts/title-meta.php'; ?>

    <!-- Select2 css -->
    <link href="assets/vendor/select2/css/select2.min.css" rel="stylesheet" type="text/css" />

    <?php include 'layouts/head-css.php'; ?>
    <style></style>

</head>

<body>
    <div class="wrapper">
        <?php include 'layouts/menu.php'; ?>
        <div class="content-page">
            <div class="content">
                <div class="container-fluid">
                    <!-- Display session messages here -->
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box">
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">mscc.pk</a></li>
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Manage Attendance</a></li>
                                        <li class="breadcrumb-item active">Mark Attendance</li>
                                    </ol>
                                </div>
                                <h4 class="page-title">Mark Attendance</h4>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <?php displaySessionMessage(); ?>
                            <div id="session-messages">
                                <!-- Error or success messages will be appended here dynamically via JS -->
                            </div>

                            <h2 class="text-center">Mark Attendance</h2>
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="needs-validation" novalidate enctype="multipart/form-data">
                                            <div class="row mb-3">
                                                <h3>Daily Check-In</h3>

                                                <div class="col-lg-6">
                                                    <div class="mb-2">
                                                        <label for="userName" class="form-label">User Name *</label>
                                                        <input type="text" id="userName" name="userName" value="<?php echo htmlspecialchars($_SESSION["username"]); ?>" class="form-control" required placeholder="User Name" disabled>
                                                        <div class="valid-feedback">Looks good!</div>
                                                        <div class="invalid-feedback">Please fill this field.</div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="mb-2">
                                                        <label for="date" class="form-label">Date*</label>
                                                        <input type="date" id="date" name="date" class="form-control" required readonly>
                                                        <div class="valid-feedback">Looks good!</div>
                                                        <div class="invalid-feedback">Please fill this field.</div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="mb-2">
                                                        <label for="time" class="form-label">Time*</label>
                                                        <input type="time" id="time" name="time" class="form-control" required readonly>
                                                        <div class="valid-feedback">Looks good!</div>
                                                        <div class="invalid-feedback">Please fill this field.</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-lg-12 text-center">
                                                    <button type="submit" id="btnCheckIn" name="btnCheckIn" class=" btn btn-primary"> Check In</button>
                                                    <button type="submit" id="btnCheckOut" name="btnCheckOut" class="btn btn-danger">Check Out</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="header-title">Attendance Record</h4>
                                    <p class="text-muted fs-14">Your attendance records are listed below.</p>
                                    <div class="table-responsive-sm">
                                        <table id="scroll-horizontal-datatable" class="table table-striped w-100 nowrap">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Username</th>
                                                    <th>Attendance Date</th>
                                                    <th>Check-In</th>
                                                    <th>Check-Out</th>
                                                    <th>Attendance Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $current_user = $_SESSION['username']; // Assuming 'username' is stored in session when logged in

                                                try {
                                                    // Start transaction if needed
                                                    $conn->begin_transaction();

                                                    // Query to fetch attendance records of the logged-in user
                                                    $query = "SELECT a.id, u.username, a.attendance_date, a.check_in, a.check_out, a.attendance_status 
                                          FROM attendance a
                                          LEFT JOIN users u ON a.user_id = u.id
                                          WHERE u.username = ?"; // Select only the logged-in user's records

                                                    // Prepare the statement
                                                    if ($stmt = $conn->prepare($query)) {
                                                        // Bind the parameter (username) to the prepared statement
                                                        $stmt->bind_param("s", $current_user);

                                                        // Execute the statement
                                                        $stmt->execute();

                                                        // Get the result set
                                                        $result = $stmt->get_result();
                                                        $id = 0;
                                                        // Check if rows were found
                                                        if ($result->num_rows > 0) {
                                                            // Loop through the results and display them
                                                            while ($row = $result->fetch_assoc()) {
                                                                $id += 1;
                                                                echo "<tr>";
                                                                echo "<td>" . $id . "</td>";
                                                                echo "<td>" . htmlspecialchars($row['username']) . "</td>";

                                                                // Convert and display date in d-M-Y format
                                                                echo "<td>" . htmlspecialchars($row['attendance_date']) . "</td>";

                                                                echo "<td>" . htmlspecialchars($row['check_in']) . "</td>";
                                                                echo "<td>" . htmlspecialchars($row['check_out']) . "</td>";

                                                                // Display the actual attendance status ('present' or 'absent')
                                                                $statusText = ($row['attendance_status'] == 'present') ? 'Present' : 'Absent';
                                                                echo "<td>" . htmlspecialchars($statusText) . "</td>";

                                                                echo "</tr>";
                                                            }
                                                        } else {
                                                            echo "<tr><td colspan='6'>No attendance records found</td></tr>";
                                                        }

                                                        // Commit the transaction (optional)
                                                        $conn->commit();
                                                    } else {
                                                        throw new Exception("Error preparing query: " . $conn->error);
                                                    }

                                                    // Close the statement
                                                    $stmt->close();
                                                } catch (Exception $e) {
                                                    // Rollback in case of error
                                                    $conn->rollback();

                                                    // Display or log the error
                                                    echo "<tr><td colspan='6'>Error: " . $e->getMessage() . "</td></tr>";
                                                } finally {
                                                    // Close the connection
                                                    $conn->close();
                                                }
                                                ?>
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

    <!--  Select2 Plugin Js -->
    <script src="assets/vendor/select2/js/select2.min.js"></script>

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
    <!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script> -->


    <script>
        // Get the current date
        const now = new Date();

        // Format the date to YYYY-MM-DD
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0'); // Months are 0-indexed
        const day = String(now.getDate()).padStart(2, '0');

        // Set the input value for date
        document.getElementById('date').value = `${year}-${month}-${day}`;

        // Format the time to HH:MM
        const hours = String(now.getHours()).padStart(2, '0'); // Get hours and pad if needed
        const minutes = String(now.getMinutes()).padStart(2, '0'); // Get minutes and pad if needed

        // Set the input value for time
        document.getElementById('time').value = `${hours}:${minutes}`;
    </script>

</body>

</html>