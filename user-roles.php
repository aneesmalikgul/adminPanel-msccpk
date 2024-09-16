<?php
include 'layouts/session.php';
include 'layouts/config.php';
include 'layouts/functions.php';
include 'layouts/main.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btnSaveRole'])) {
    // Collect form data and sanitize inputs
    $roleName = mysqli_real_escape_string($conn, $_POST['roleName']);

    $roleName = strtolower($roleName);
    $roleName = str_replace([' ', '-'], '_', $roleName);

    $roleStatus = mysqli_real_escape_string($conn, $_POST['roleStatus']);
    $createdBy = $_SESSION['user_id']; // Assuming username is stored in the session
    $createdAt = date('Y-m-d H:i:s');

    // Start a transaction
    mysqli_begin_transaction($conn, MYSQLI_TRANS_START_READ_WRITE);

    try {
        // Prepare the SQL insert query
        $query = "INSERT INTO roles (role_name, status, created_by, created_at) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);

        // Throw an exception if the prepare statement fails
        if ($stmt === false) {
            throw new Exception("Prepare statement failed: " . mysqli_error($conn));
        }

        // Bind parameters to the prepared statement (prevents SQL injection)
        mysqli_stmt_bind_param($stmt, "siss", $roleName, $roleStatus, $createdBy, $createdAt);

        // Execute the prepared statement
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Execute statement failed: " . mysqli_stmt_error($stmt));
        }

        // Commit the transaction
        mysqli_commit($conn);

        // Set success message in session
        $_SESSION['message'][] = array("type" => "success", "content" => "Role added successfully!");

        // Redirect to the roles page (or wherever you'd like)
        header("Location: user-roles.php");
        exit();
    } catch (Exception $e) {
        // Rollback the transaction if any error occurs
        mysqli_rollback($conn);

        // Set error message in session
        $_SESSION['message'][] = array("type" => "danger", "content" => $e->getMessage());
    } finally {
        // Close the prepared statement and the connection
        if (isset($stmt)) {
            mysqli_stmt_close($stmt);
        }
        mysqli_close($conn);

        // Redirect to the roles page
        header("Location: user-roles.php");
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["btnUpdateRole"])) {
    // Collect form data and sanitize inputs
    $roleId = mysqli_real_escape_string($conn, $_POST['roleId']);
    $roleName = mysqli_real_escape_string($conn, $_POST['roleName']);
    $roleName = strtolower($roleName);
    $roleName = str_replace([' ', '-'], '_', $roleName);
    $roleStatus = mysqli_real_escape_string($conn, $_POST['roleStatus']);
    $updatedBy = $_SESSION['user_id']; // Assuming the logged-in user ID is stored in session
    $updatedAt = date('Y-m-d H:i:s');

    // Start a transaction
    mysqli_begin_transaction($conn, MYSQLI_TRANS_START_READ_WRITE);

    try {
        // Prepare the update query
        $query = "UPDATE roles SET role_name = ?, status = ?, updated_by = ?, updated_at = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);

        // Check if the statement was prepared successfully
        if ($stmt === false) {
            throw new Exception("Prepare statement failed: " . mysqli_error($conn));
        }

        // Bind parameters to the prepared statement
        mysqli_stmt_bind_param($stmt, "siisi", $roleName, $roleStatus, $updatedBy, $updatedAt, $roleId);

        // Execute the prepared statement
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Execute statement failed: " . mysqli_stmt_error($stmt));
        }

        // Commit the transaction
        mysqli_commit($conn);

        // Set success message and redirect to user-roles.php
        $_SESSION['message'][] = array("type" => "success", "content" => "Role updated successfully!");
        header("Location: user-roles.php");
        exit();
    } catch (Exception $e) {
        // Rollback the transaction on error
        mysqli_rollback($conn);

        // Set error message and redirect
        $_SESSION['message'][] = array("type" => "danger", "content" => $e->getMessage());
        header("Location: edit-role.php?id=" . urlencode($roleId));
        exit();
    } finally {
        // Close the statement and connection
        if (isset($stmt)) {
            mysqli_stmt_close($stmt);
        }
        mysqli_close($conn);
    }
}
?>

<head>
    <title>User Roles | Mohsin Shaheen Construction Company</title>
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
                    <!-- Display session messages here -->
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box">
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">mscc.pk</a></li>
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">User Management</a></li>
                                        <li class="breadcrumb-item active">User Roles</li>
                                    </ol>
                                </div>
                                <h4 class="page-title">User Roles</h4>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <?php displaySessionMessage(); ?>
                            <h2 class="text-center">Add New Role</h2>
                            <div class="card">
                                <div class="card-body">
                                    <p class="text-muted fs-14"> </p>
                                    <div class="row">
                                        <div>
                                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="needs-validation" novalidate enctype="multipart/form-data">
                                                <div class="row mb-3">
                                                    <h3>Role Information</h3>

                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="roleName" class="form-label">Role Name</label>
                                                            <input type="text" id="roleName" name="roleName" class="form-control" required placeholder="Enter the name of Role">
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback">Please fill this field.</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="roleStatus" class="form-label">Role Status</label>
                                                            <select id="roleStatus" name="roleStatus" class="form-select" required>
                                                                <option selected disabled value="">Select Role Status</option>
                                                                <option value="1">Active</option>
                                                                <option value="0">Inactive</option>
                                                            </select>
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback">Please select a status.</div>
                                                        </div>
                                                    </div>

                                                </div>
                                                <div class="row mb-3">
                                                    <div class="col-lg-12 text-center">
                                                        <button type="submit" id="btnSaveRole" name="btnSaveRole" class="btn btn-primary ">Save Role</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="header-title">All Created User Roles</h4>
                                    <p class="text-muted fs-14"></p>
                                    <div class="table-responsive-sm">
                                        <table id="scroll-horizontal-datatable" class="table table-striped w-100 nowrap">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Role Name</th>
                                                    <th>Role Status</th>
                                                    <th>Created By</th>
                                                    <th>Created At</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                try {
                                                    // Start transaction if needed
                                                    mysqli_begin_transaction($conn);

                                                    // Define the query to fetch roles with the user who created them
                                                    $query = "SELECT r.id, r.role_name, r.status, r.created_at, u.username AS created_by
                                                                FROM roles r
                                                                LEFT JOIN users u ON r.created_by = u.id;";

                                                    // Execute the query
                                                    $result = mysqli_query($conn, $query);

                                                    // Check for query execution errors
                                                    if (!$result) {
                                                        throw new Exception("Error executing query: " . mysqli_error($conn));
                                                    }

                                                    // If the query is successful, loop through the results
                                                    if (mysqli_num_rows($result) > 0) {
                                                        while ($row = mysqli_fetch_assoc($result)) {
                                                            echo "<tr>";
                                                            echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                                                            echo "<td>" . htmlspecialchars(ucwords(str_replace(['_', '-'], ' ', $row['role_name']))) . "</td>";

                                                            // Convert status to Active/Inactive
                                                            $statusText = $row['status'] == 1 ? 'Active' : 'Not Active';
                                                            echo "<td>" . htmlspecialchars($statusText) . "</td>";

                                                            echo "<td>" . htmlspecialchars($row['created_by']) . "</td>";
                                                            echo "<td>" . htmlspecialchars(date('d-M-Y', strtotime($row['created_at']))) . "</td>";
                                                            echo "<td>";
                                                            // Edit button
                                                            echo "<a href='edit-role.php?id=" . urlencode($row['id']) . "' class='btn btn-warning'><i class='ri-pencil-line'></i></a>";
                                                            echo "  ";
                                                            // Delete button
                                                            echo "<a href='delete-role.php?id=" . urlencode($row['id']) . "' class='btn btn-danger' onclick='return confirmDelete();' ><i class='ri-delete-bin-line'></i></a>";
                                                            echo "</td>";
                                                            echo "</tr>";
                                                        }
                                                    } else {
                                                        echo "<tr><td colspan='6'>No roles found</td></tr>";
                                                    }

                                                    // Commit transaction (optional if you're modifying data)
                                                    mysqli_commit($conn);
                                                } catch (Exception $e) {
                                                    // Rollback in case of error
                                                    mysqli_rollback($conn);

                                                    // Display or log the error
                                                    echo "<tr><td colspan='6'>Error: " . $e->getMessage() . "</td></tr>";
                                                } finally {
                                                    // Close the connection
                                                    mysqli_close($conn);
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
    <script src="assets/js/app.min.js"></script>
    <script>
        function confirmDelete() {
            // Show a confirmation dialog
            var result = confirm("Are you sure you want to delete this role?");

            // If the user clicks "OK", return true to allow the default action (redirect)
            if (result) {
                return true;
            } else {
                // If the user clicks "Cancel", prevent the redirection
                return false;
            }
        }
    </script>

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

</body>

</html>