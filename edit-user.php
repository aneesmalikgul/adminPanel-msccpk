<?php
include 'layouts/session.php';
include 'layouts/config.php';
include 'layouts/functions.php';
include 'layouts/main.php';

// Check if the role ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['message'][] = ["type" => "error", "content" => "Please Provide User ID"];
    header("Location: manage-users.php");
    exit();
}
// Initialize user ID
$userId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user = null;

try {
    // Check if user ID is valid
    if ($userId <= 0) {
        throw new Exception('Invalid user ID.');
    }

    // Begin transaction
    $conn->begin_transaction();

    // Prepare the SQL statement to fetch user data
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    // Bind parameters to avoid SQL injection
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if user exists
    if ($result->num_rows === 0) {
        throw new Exception('User not found.');
    }

    // Fetch the user data
    $user = $result->fetch_assoc();

    // Commit transaction
    $conn->commit();
} catch (Exception $e) {
    // Rollback if any error occurs
    $conn->rollback();

    // Set error message in session
    $_SESSION['message'][] = ["type" => "danger", "content" => $e->getMessage()];

    // Redirect to manage-user.php
    header('Location: manage-user.php');
    exit;
}


$roles = [];
try {
    $conn->begin_transaction();

    // Using a prepared statement
    $queryRoles = "SELECT * FROM roles WHERE status = 1 ORDER BY id ASC;";
    $stmt = $conn->prepare($queryRoles);

    if ($stmt) {
        $stmt->execute();
        $resultRoles = $stmt->get_result();

        if ($resultRoles) {
            while ($row = $resultRoles->fetch_assoc()) {
                $roles[] = $row;
            }
        } else {
            throw new Exception('Error fetching roles: ' . $conn->error);
        }

        // Close the statement
        $stmt->close();
    } else {
        throw new Exception('Prepare statement failed: ' . $conn->error);
    }

    // Commit the transaction
    $conn->commit();
} catch (Exception $e) {
    // Rollback the transaction on error
    $conn->rollback();
    $_SESSION['message'] = ["type" => "error", "content" => $e->getMessage()];
}
?>


<head>
    <title>Edit User | Mohsin Shaheen Construction Company</title>
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
                                        <li class="breadcrumb-item active">Edit User</li>
                                    </ol>
                                </div>
                                <h4 class="page-title">Edit User</h4>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <h2 class="text-center">Edit User Details</h2>
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <p class="text-muted fs-14"> </p>
                                    <div class="row">
                                        <div>
                                            <form action="<?php echo htmlspecialchars('manage-users.php'); ?>" method="post" class="needs-validation" novalidate enctype="multipart/form-data">
                                                <div class="row mb-3">
                                                    <h3>User Information</h3>
                                                    <input type="hidden" name="userId" value="<?php echo htmlspecialchars($user['id']); ?>">

                                                    <!-- First Name -->
                                                    <div class="col-lg-6">
                                                        <div class="mb-2">
                                                            <label for="userFirstName" class="form-label">First Name *</label>
                                                            <input type="text" id="userFirstName" name="userFirstName" class="form-control" value="<?php echo htmlspecialchars($user['first_name']); ?>" required placeholder="Enter first name of the user">
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback">Please fill this field.</div>
                                                        </div>
                                                    </div>

                                                    <!-- Last Name -->
                                                    <div class="col-lg-6">
                                                        <div class="mb-2">
                                                            <label for="userLastName" class="form-label">Last Name *</label>
                                                            <input type="text" id="userLastName" name="userLastName" class="form-control" value="<?php echo htmlspecialchars($user['last_name']); ?>" required placeholder="Enter last name of the user">
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback">Please fill this field.</div>
                                                        </div>
                                                    </div>

                                                    <!-- CNIC -->
                                                    <div class="col-lg-6">
                                                        <div class="mb-2">
                                                            <label for="userCNIC" class="form-label">CNIC No. *</label>
                                                            <input type="text" id="userCNIC" name="userCNIC" class="form-control" value="<?php echo htmlspecialchars($user['cnic']); ?>" required placeholder="Enter CNIC of the user" data-toggle="input-mask" data-mask-format="00000-0000000-0">
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback">Please fill this field.</div>
                                                        </div>
                                                    </div>

                                                    <!-- Date of Birth -->
                                                    <div class="col-lg-6">
                                                        <div class="mb-2">
                                                            <label for="userDOB" class="form-label">Date of Birth *</label>
                                                            <input type="date" id="userDOB" name="userDOB" class="form-control" value="<?php echo htmlspecialchars($user['dob']); ?>" required>
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback">Please fill this field.</div>
                                                        </div>
                                                    </div>

                                                    <!-- Email -->
                                                    <div class="col-lg-6">
                                                        <div class="mb-2">
                                                            <label for="userEmail" class="form-label">Email *</label>
                                                            <input type="email" id="userEmail" name="userEmail" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required placeholder="Enter email of the user">
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback">Please fill this field.</div>
                                                        </div>
                                                    </div>

                                                    <!-- Username -->
                                                    <div class="col-lg-6">
                                                        <div class="mb-2">
                                                            <label for="userName" class="form-label">Username *</label>
                                                            <input type="text" id="userName" name="userName" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" required placeholder="Enter username of the user">
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback">Please fill this field.</div>
                                                        </div>
                                                    </div>

                                                    <!-- Contact Number -->
                                                    <div class="col-lg-6">
                                                        <div class="mb-2">
                                                            <label for="contactNumber" class="form-label">Contact Number *</label>
                                                            <input type="tel" id="contactNumber" name="contactNumber" class="form-control" value="<?php echo htmlspecialchars($user['contact_number']); ?>" required placeholder="Enter Contact Number of the user" data-toggle="input-mask" data-mask-format="(+00)-000-0000000">
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback">Please fill this field.</div>
                                                        </div>
                                                    </div>

                                                    <!-- WhatsApp Contact -->
                                                    <div class="col-lg-6">
                                                        <div class="mb-2">
                                                            <label for="whatsAppContact" class="form-label">WhatsApp Contact Number *</label>
                                                            <input type="tel" id="whatsAppContact" name="whatsAppContact" class="form-control" value="<?php echo htmlspecialchars($user['whatsapp_contact']); ?>" required placeholder="Enter WhatsApp Contact of the user" data-toggle="input-mask" data-mask-format="(+00)-000-0000000">
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback">Please fill this field.</div>
                                                        </div>
                                                    </div>

                                                    <!-- Address -->
                                                    <div class="col-lg-12">
                                                        <div class="mb-2">
                                                            <label for="userAddress" class="form-label">Address *</label>
                                                            <input type="text" id="userAddress" name="userAddress" class="form-control" value="<?php echo htmlspecialchars($user['address']); ?>" required placeholder="Enter address of the user">
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback">Please fill this field.</div>
                                                        </div>
                                                    </div>

                                                    <!-- User Role -->
                                                    <div class="col-lg-6">
                                                        <div class="mb-2">
                                                            <label for="userRole" class="form-label">User Role *</label>
                                                            <select id="userRole" name="userRole" class="form-select select2" data-toggle="select2" required>
                                                                <?php foreach ($roles as $role): ?>
                                                                    <option value="<?php echo htmlspecialchars($role['id']); ?>" <?php echo $role['id'] == $user['role_id'] ? 'selected' : ''; ?>>
                                                                        <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $role['role_name']))); ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback">Please fill this field.</div>
                                                        </div>
                                                    </div>

                                                    <!-- User Status -->
                                                    <div class="col-lg-6">
                                                        <div class="mb-2">
                                                            <label for="userStatus" class="form-label">User Status *</label>
                                                            <select id="userStatus" name="userStatus" class="form-select select2" data-toggle="select2" required>
                                                                <option value="1" <?php echo $user['is_active'] == 1 ? 'selected' : ''; ?>>Active</option>
                                                                <option value="0" <?php echo $user['is_active'] == 0 ? 'selected' : ''; ?>>Inactive</option>
                                                            </select>
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback">Please select a status.</div>
                                                        </div>
                                                    </div>

                                                    <!-- Profile Picture -->
                                                    <div class="col-lg-6">
                                                        <div class="mb-2">
                                                            <label for="userProfilePic" class="form-label">Profile Picture *</label>
                                                            <input type="file" id="userProfilePic" name="userProfilePic" class="form-control" accept="image/*" onchange="displayImage(this)">
                                                            <img id="profilePicPreview" src="<?php echo htmlspecialchars($user['profile_pic_path']); ?>" alt="Profile Picture" class="img-thumbnail mt-2" style="max-width: 150px;">
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback" id="imageError">Please fill this field.</div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row mb-3">
                                                    <div class="col-lg-12 text-center">
                                                        <button type="submit" id="btnUpdateUserData" name="btnUpdateUserData" class="btn btn-primary">Update User Details</button>
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
    <!-- JavaScript to preview image -->
    <script>
        function displayImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('profilePicPreview').src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>


</body>

</html>