<?php
include 'layouts/session.php';
include 'layouts/config.php';
include 'layouts/functions.php';
include 'layouts/main.php';

if (!hasPermission('manage_user') || !hasPermission('manage_assign_permission') || !hasPermission('edit_assign_permission')) {
    header('Location: index.php');
    exit();
}

$rolePermissionId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$rolePermission = null;

try {
    if ($rolePermissionId > 0) {
        // Start the transaction
        $conn->begin_transaction();

        // Prepare the query to fetch the role_permission details
        $query = "
            SELECT rp.id, rp.role_id, rp.permission_id, rp.status, rp.created_at, 
                   r.role_name, p.permission_name 
            FROM role_permissions rp
            LEFT JOIN roles r ON r.id = rp.role_id
            LEFT JOIN permissions p ON p.id = rp.permission_id
            WHERE rp.id = ?
        ";

        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }

        // Bind the rolePermissionId to the query
        $stmt->bind_param("i", $rolePermissionId);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if we got a result
        if ($result && $result->num_rows > 0) {
            $rolePermission = $result->fetch_assoc();
        } else {
            throw new Exception('No role_permission record found.');
        }

        // Commit the transaction
        $conn->commit();
    } else {
        throw new Exception('Invalid role_permission ID.');
    }
} catch (Exception $e) {
    // Rollback the transaction in case of error
    $conn->rollback();

    // Set an error message and redirect or handle it accordingly
    $_SESSION['message'] = ['type' => 'error', 'text' => $e->getMessage()];
    header('Location: manage-role-permissions.php'); // Redirect back to manage page if there's an error
    exit;
}
// No need to close the connection here if you have persistent connection handling
// Fetch roles for the 'roleName' dropdown
$roles = [];
$permissions = [];

try {
    // Start the transaction
    $conn->begin_transaction();

    // Query to fetch roles
    $queryRoles = "SELECT id, role_name FROM roles WHERE status = 1 ORDER BY id ASC;";
    $stmtRoles = $conn->prepare($queryRoles);
    if (!$stmtRoles) {
        throw new Exception('Prepare roles failed: ' . $conn->error);
    }
    $stmtRoles->execute();
    $resultRoles = $stmtRoles->get_result();
    while ($row = $resultRoles->fetch_assoc()) {
        $roles[] = $row;
    }

    // Query to fetch permissions
    $queryPermissions = "SELECT id, permission_name FROM permissions WHERE status = 1 ORDER BY id ASC;";
    $stmtPermissions = $conn->prepare($queryPermissions);
    if (!$stmtPermissions) {
        throw new Exception('Prepare permissions failed: ' . $conn->error);
    }
    $stmtPermissions->execute();
    $resultPermissions = $stmtPermissions->get_result();
    while ($row = $resultPermissions->fetch_assoc()) {
        $permissions[] = $row;
    }

    // Commit the transaction
    $conn->commit();
} catch (Exception $e) {
    // Rollback the transaction on error
    $conn->rollback();
    $_SESSION['message'] = ['type' => 'error', 'text' => $e->getMessage()];
}

// Close the statement objects
$stmtRoles->close();
$stmtPermissions->close();
// No need to close connection explicitly if you're reusing it
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
                        <h2 class="text-center">Edit Assigned Permissions for Roles</h2>
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <p class="text-muted fs-14"> </p>
                                    <div class="row">
                                        <div>
                                            <form action="<?php echo htmlspecialchars("assign-permissions.php"); ?>" method="post" class="needs-validation" novalidate enctype="multipart/form-data">
                                                <div class="row mb-3">
                                                    <h3>Role & Permission Information</h3>
                                                    <input type="hidden" name="rolePermissionId" value="<?php echo htmlspecialchars($rolePermissionId); ?>">
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="roleName" class="form-label">Role Name</label>
                                                            <select id="roleName" name="roleName" class="form-select select2" data-toggle="select2" required>
                                                                <?php foreach ($roles as $role): ?>
                                                                    <option value="<?php echo htmlspecialchars($role['id']); ?>"
                                                                        <?php echo ($rolePermission && $role['id'] == $rolePermission['role_id']) ? 'selected' : ''; ?>>
                                                                        <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $role['role_name']))); ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback">Please select a role.</div>
                                                        </div>
                                                    </div>

                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="permissionName" class="form-label">Permission Name</label>
                                                            <select id="permissionName" name="permissionName" class="form-select select2" data-toggle="select2" required>
                                                                <?php foreach ($permissions as $permission): ?>
                                                                    <option value="<?php echo htmlspecialchars($permission['id']); ?>"
                                                                        <?php echo ($rolePermission && $permission['id'] == $rolePermission['permission_id']) ? 'selected' : ''; ?>>
                                                                        <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $permission['permission_name']))); ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback">Please select a permission.</div>
                                                        </div>
                                                    </div>

                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="roleStatus" class="form-label">Role Status</label>
                                                            <select id="roleStatus" name="roleStatus" class="form-select" required>
                                                                <option selected disabled value="">Select Role Status</option>
                                                                <option value="1" <?php echo ($rolePermission && $rolePermission['status'] == 1) ? 'selected' : ''; ?>>Active</option>
                                                                <option value="0" <?php echo ($rolePermission && $rolePermission['status'] == 0) ? 'selected' : ''; ?>>Inactive</option>
                                                            </select>
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback">Please select a status.</div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row mb-3">
                                                    <div class="col-lg-12 text-center">
                                                        <button type="submit" id="btnUpdateAssignPermission" name="btnUpdateAssignPermission" class="btn btn-primary">Assign Permission</button>
                                                    </div>
                                                </div>
                                            </form>

                                        </div>
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