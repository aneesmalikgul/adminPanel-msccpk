<?php
include 'layouts/session.php';
include 'layouts/config.php';
include 'layouts/functions.php';
include 'layouts/main.php';

// Fetch roles for the 'roleName' dropdown
$roles = [];
$permissions = [];

try {
    // Start the transaction
    mysqli_begin_transaction($conn);

    // Query to fetch roles
    $queryRoles = "SELECT id, role_name FROM roles  WHERE status = 1 ORDER BY id ASC;";
    $resultRoles = mysqli_query($conn, $queryRoles);
    if ($resultRoles) {
        while ($row = mysqli_fetch_assoc($resultRoles)) {
            $roles[] = $row;
        }
    } else {
        throw new Exception('Error fetching roles: ' . mysqli_error($conn));
    }

    // Query to fetch permissions
    $queryPermissions = "SELECT id, permission_name FROM permissions  WHERE status = 1 ORDER BY id ASC;";
    $resultPermissions = mysqli_query($conn, $queryPermissions);
    if ($resultPermissions) {
        while ($row = mysqli_fetch_assoc($resultPermissions)) {
            $permissions[] = $row;
        }
    } else {
        throw new Exception('Error fetching permissions: ' . mysqli_error($conn));
    }

    // Commit the transaction
    mysqli_commit($conn);
} catch (Exception $e) {
    // Rollback the transaction on error
    mysqli_rollback($conn);
    $_SESSION['message'] = ['type' => 'error', 'text' => $e->getMessage()];
}

// Close the database connection
// mysqli_close($conn);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btnAssignRole'])) {
    // Get POST data and sanitize
    $roleId = isset($_POST['roleName']) ? (int)$_POST['roleName'] : 0;
    $permissionId = isset($_POST['permissionName']) ? (int)$_POST['permissionName'] : 0;
    $status = isset($_POST['roleStatus']) ? (int)$_POST['roleStatus'] : 0;
    $createdBy = $_SESSION['user_id']; // Assuming user ID is stored in session
    $updatedBy = $_SESSION['user_id'];

    try {
        // Start the transaction
        mysqli_begin_transaction($conn);

        // Insert into role_permissions table
        $stmt = mysqli_prepare($conn, "INSERT INTO role_permissions (role_id, permission_id, created_at, updated_at, created_by, updated_by, status) VALUES (?, ?, NOW(), NOW(), ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'iiiii', $roleId, $permissionId, $createdBy, $updatedBy, $status);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['message'][] = ['type' => 'success', 'content' => 'Permission assigned successfully!'];
        } else {
            throw new Exception('Error inserting data: ' . mysqli_error($conn));
        }

        // Commit the transaction
        mysqli_commit($conn);
    } catch (Exception $e) {
        // Rollback the transaction on error
        mysqli_rollback($conn);
        $_SESSION['message'][] = ['type' => 'error', 'content' => $e->getMessage()];
    }

    // Redirect to the roles page with a message
    header('Location: assign-permissions.php');
    exit();
}
// Code handling the update operation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btnUpdateAssignPermission'])) {
    // Get and sanitize POST data
    $rolePermissionId = isset($_POST['rolePermissionId']) ? intval($_POST['rolePermissionId']) : 0;
    $roleId = isset($_POST['roleName']) ? intval($_POST['roleName']) : 0;
    $permissionId = isset($_POST['permissionName']) ? intval($_POST['permissionName']) : 0;
    $status = isset($_POST['roleStatus']) ? intval($_POST['roleStatus']) : 0;

    try {
        // Validate input
        if ($rolePermissionId <= 0 || $roleId <= 0 || $permissionId <= 0) {
            throw new Exception('Invalid input data.');
        }

        // Begin transaction
        mysqli_begin_transaction($conn, MYSQLI_TRANS_START_READ_WRITE);

        // Prepare the update query
        $query = "
            UPDATE role_permissions
            SET role_id = ?, permission_id = ?, status = ?
            WHERE id = ?;
        ";
        $stmt = mysqli_prepare($conn, $query);

        if ($stmt === false) {
            throw new Exception('Prepare statement failed: ' . mysqli_error($conn));
        }

        // Bind parameters and execute statement
        mysqli_stmt_bind_param($stmt, "iiii", $roleId, $permissionId, $status, $rolePermissionId);
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception('Execute statement failed: ' . mysqli_stmt_error($stmt));
        }

        // Commit transaction
        mysqli_commit($conn);

        // Set success message and redirect
        $_SESSION['message'][] = ['type' => 'success', 'content' => 'Role permission updated successfully.'];

        // $_SESSION['message'] = ['type' => 'success', 'content' => 'Role permission updated successfully.'];
    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($conn);

        // Set error message
        $_SESSION['message'][] = ['type' => 'error', 'content' => $e->getMessage()];
    } finally {
        // Ensure statement and connection are closed
        if (isset($stmt)) {
            mysqli_stmt_close($stmt);
        }
        // mysqli_close($conn);

        // Redirect to ensure page reload
        header('Location: assign-permissions.php');
        exit();
    }
}


?>

<head>
    <title>Assign Permissions | Mohsin Shaheen Construction Company</title>
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
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box">
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">mscc.pk</a></li>
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">User Management</a></li>
                                        <li class="breadcrumb-item active">Assign Permissions</li>
                                    </ol>
                                </div>
                                <h4 class="page-title">Assign Permissions</h4>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <?php displaySessionMessage(); ?>

                            <h2 class="text-center">Assign Permissions to Roles</h2>
                            <div class="card">
                                <div class="card-body">
                                    <p class="text-muted fs-14"> </p>
                                    <div class="row">
                                        <div>
                                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="needs-validation" novalidate enctype="multipart/form-data">
                                                <div class="row mb-3">
                                                    <h3>Role & Permission Information</h3>

                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="roleName" class="form-label">Role Name</label>
                                                            <select id="roleName" name="roleName" class="form-select select2" data-toggle="select2">
                                                                <?php foreach ($roles as $role): ?>
                                                                    <option value="<?php echo htmlspecialchars($role['id']); ?>">
                                                                        <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $role['role_name']))); ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback">Please fill this field.</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="permissionName" class="form-label">Permission Name</label>
                                                            <select id="permissionName" name="permissionName" class="form-select select2" data-toggle="select2">
                                                                <?php foreach ($permissions as $permission): ?>
                                                                    <option value="<?php echo htmlspecialchars($permission['id']); ?>">
                                                                        <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $permission['permission_name']))); ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback">Please select a status.</div>
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
                                                        <button type="submit" id="btnAssignRole" name="btnAssignRole" class="btn btn-primary ">Assign Permission</button>
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
                                    <h4 class="header-title">All Assigned Permissions</h4>
                                    <p class="text-muted fs-14"></p>
                                    <div class="table-responsive-sm">
                                        <table id="scroll-horizontal-datatable" class="table table-striped w-100 nowrap">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Role Name</th>
                                                    <th>Assigned Permission</th>
                                                    <th>Status</th>
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
                                                    $query = "SELECT rp.id, rp.status, rp.created_at,
                                                                r.role_name, p.permission_name,
                                                                u.username AS created_by
                                                                FROM role_permissions rp
                                                                LEFT JOIN roles r ON r.id = rp.role_id
                                                                LEFT JOIN permissions p ON p.id = rp.permission_id
                                                                LEFT JOIN users u ON u.id = rp.created_by
                                                                WHERE rp.status = 1;";

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
                                                            echo "<td>" . htmlspecialchars(ucwords(str_replace('_', ' ', $row['role_name']))) . "</td>";
                                                            echo "<td>" . htmlspecialchars(ucwords(str_replace('_', ' ', $row['permission_name']))) . "</td>";


                                                            // Convert status to Active/Inactive
                                                            $statusText = $row['status'] == 1 ? 'Active' : 'Not Active';
                                                            echo "<td>" . htmlspecialchars($statusText) . "</td>";
                                                            echo "<td>" . htmlspecialchars($row['created_by']) . "</td>";
                                                            echo "<td>" . htmlspecialchars(date('d-M-Y', strtotime($row['created_at']))) . "</td>";
                                                            echo "<td>";
                                                            // Edit button
                                                            echo "<a href='edit-assign-permissions.php?id=" . urlencode($row['id']) . "' class='btn btn-warning'><i class='ri-pencil-line'></i></a>";
                                                            echo "  ";
                                                            // Delete button
                                                            echo "<a href='delete-assign-permissions.php?id=" . urlencode($row['id']) . "' class='btn btn-danger' onclick='return confirmDelete();' ><i class='ri-delete-bin-line'></i></a>";
                                                            echo "</td>";
                                                            echo "</tr>";
                                                        }
                                                    }
                                                    // else {
                                                    //     echo "<tr><td colspan='6'>No roles found</td></tr>";
                                                    // }

                                                    // Commit transaction (optional if you're modifying data)
                                                    mysqli_commit($conn);
                                                } catch (Exception $e) {
                                                    // Rollback in case of error
                                                    mysqli_rollback($conn);

                                                    // Display or log the error
                                                    echo "<tr><td colspan='6'>Error: " . $e->getMessage() . "</td></tr>";
                                                } finally {
                                                    // Close the connection
                                                    // mysqli_close($conn);
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