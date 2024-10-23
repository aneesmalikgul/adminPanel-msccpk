<?php
include 'layouts/session.php';
include 'layouts/config.php';
include 'layouts/functions.php';
include 'layouts/main.php';

if (!hasPermission('manage_user') || !hasPermission('manage_role') || !hasPermission('edit_role')) {
    header('Location: manage-role.php');
    exit();
}

// Check if the role ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['message'][] = ["type" => "error", "content" => "No role ID provided."];
    header("Location: user-roles.php");
    exit();
}

// Sanitize the input to prevent SQL injection
$roleId = (int)$_GET['id']; // Cast to integer

// Fetch role details
$query = "SELECT role_name, status FROM roles WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $roleId);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    // Fetch the role details
    $role = $result->fetch_assoc();
} else {
    // If no role is found, set an error message and redirect back
    $_SESSION['message'][] = ["type" => "danger", "content" => "Role not found."];
    header("Location: user-roles.php");
    exit();
}

// Close the statement
$stmt->close();
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
                        <h2 class="text-center">Update Role</h2>
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <p class="text-muted fs-14"> </p>
                                    <div class="row">
                                        <div>
                                            <form action="<?php echo "user-roles.php"; ?>" method="post" class="needs-validation" novalidate enctype="multipart/form-data">
                                                <div class="row mb-3">
                                                    <h3>Update Role Information</h3>
                                                    <!-- Hidden input to store the role ID -->
                                                    <input type="hidden" name="roleId" value="<?= htmlspecialchars($roleId); ?>">

                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="roleName" class="form-label">Role Name</label>
                                                            <input type="text" id="roleName" name="roleName" class="form-control" value="<?= htmlspecialchars(ucwords(str_replace('_', ' ', $role['role_name']))); ?>" required>
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback">Please fill this field.</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="roleStatus" class="form-label">Role Status</label>
                                                            <select id="roleStatus" name="roleStatus" class="form-select" required>
                                                                <option value="1" <?= ($role['status'] == 1) ? 'selected' : ''; ?>>Active</option>
                                                                <option value="0" <?= ($role['status'] == 0) ? 'selected' : ''; ?>>Inactive</option>
                                                            </select>
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback">Please select a status.</div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row mb-3">
                                                    <div class="col-lg-12 text-center">
                                                        <button type="submit" id="btnUpdateRole" name="btnUpdateRole" class="btn btn-primary">Update Role</button>
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