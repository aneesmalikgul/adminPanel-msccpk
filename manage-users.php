<?php
include 'layouts/session.php';
include 'layouts/config.php';
include 'layouts/functions.php';
include 'layouts/main.php';

$roles = [];
try {
    mysqli_begin_transaction($conn);

    $queryRoles = "SELECT * FROM roles WHERE status = 1 ORDER BY id ASC;";
    $resultRoles = mysqli_query($conn, $queryRoles);
    if ($resultRoles) {
        while ($row = mysqli_fetch_assoc($resultRoles)) {
            $roles[] = $row;
        }
    } else {
        throw new Exception('Error Fetching roles: ' . mysqli_error($conn));
    }
    // Commit the transaction
    mysqli_commit($conn);
} catch (Exception $e) {
    // Rollback the transaction on error
    mysqli_rollback($conn);
    $_SESSION['message'] = ['type' => 'error', 'text' => $e->getMessage()];
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btnSaveUserData'])) {
    try {
        // Begin the transaction
        mysqli_begin_transaction($conn);

        // Sanitize user inputs
        $firstName = htmlspecialchars(trim($_POST['userFirstName']));
        $lastName = htmlspecialchars(trim($_POST['userLastName']));
        $cnic = htmlspecialchars(trim($_POST['userCNIC']));
        $dob = htmlspecialchars(trim($_POST['userDOB']));
        $email = htmlspecialchars(trim($_POST['userEmail']));
        $username = htmlspecialchars(trim($_POST['userName']));
        $contactNumber = htmlspecialchars(trim($_POST['contactNumber']));
        $whatsAppContact = htmlspecialchars(trim($_POST['whatsAppContact']));
        $address = htmlspecialchars(trim($_POST['userAddress']));
        $role = (int)$_POST['userRole'];
        $status = (int)$_POST['userStatus'];
        $createdBy = $_SESSION['user_id']; // Assume this is set in the session

        // Check if profile picture is uploaded and validate its dimensions
        $profilePicPath = NULL;
        if (isset($_FILES['userProfilePic']) && $_FILES['userProfilePic']['error'] == 0) {
            $fileTmpPath = $_FILES['userProfilePic']['tmp_name'];
            $fileName = basename($_FILES['userProfilePic']['name']);
            $targetDir = "uploads/"; // Directory for uploads
            $targetFilePath = $targetDir . $fileName;

            // Validate image dimensions
            list($width, $height) = getimagesize($fileTmpPath);
            if ($width != 500 || $height != 500) {
                throw new Exception("Profile picture must be 500x500 pixels. Current dimensions are $width x $height.");
            }

            move_uploaded_file($fileTmpPath, $targetFilePath);
            $profilePicPath = $targetFilePath;
        }

        // Check if username or email already exists to prevent duplication
        $checkQuery = "SELECT COUNT(*) as count FROM users WHERE username = ? OR email = ?";
        $stmt = mysqli_prepare($conn, $checkQuery);
        mysqli_stmt_bind_param($stmt, 'ss', $username, $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $count);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        if ($count > 0) {
            throw new Exception("Username or Email already exists.");
        }

        // Prepare the insert query
        $query = "INSERT INTO users (username, password_hash, email, first_name, last_name, cnic, dob, contact_number, whatsapp_contact, address, profile_pic_path, role_id, is_active, created_by) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        // Hash the password (assuming the form includes it)
        $passwordHash = password_hash("defaultPassword", PASSWORD_DEFAULT); // Set default password

        // Bind parameters and execute the query
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'sssssssssssiii', $username, $passwordHash, $email, $firstName, $lastName, $cnic, $dob, $contactNumber, $whatsAppContact, $address, $profilePicPath, $role, $status, $createdBy);

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Failed to insert user: " . mysqli_stmt_error($stmt));
        }

        // Commit the transaction
        mysqli_commit($conn);

        // Success message
        $_SESSION['message'] = ['type' => 'success', 'text' => 'User created successfully.'];
    } catch (Exception $e) {
        // Rollback the transaction if there’s an error
        mysqli_rollback($conn);
        // Error message
        $_SESSION['message'] = ['type' => 'danger', 'text' => $e->getMessage()];
    } finally {
        // Close the statement and connection
        if (isset($stmt)) {
            mysqli_stmt_close($stmt);
        }
        mysqli_close($conn);
    }

    // Redirect to another page after processing (optional)
    header("Location: users_list.php");
    exit;
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
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">User Management</a></li>
                                        <li class="breadcrumb-item active">Manage Users</li>
                                    </ol>
                                </div>
                                <h4 class="page-title">Manage Users</h4>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <?php displaySessionMessage(); ?>
                            <h2 class="text-center">Add New User</h2>
                            <div class="card">
                                <div class="card-body">
                                    <p class="text-muted fs-14"> </p>
                                    <div class="row">
                                        <div>
                                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="needs-validation" novalidate enctype="multipart/form-data">
                                                <div class="row mb-3">
                                                    <h3>User Information</h3>

                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="userFirstName" class="form-label">First Name</label>
                                                            <input type="text" id="userFirstName" name="userFirstName" class="form-control" required placeholder="Enter first name of the user">
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback">Please fill this field.</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="userLastName" class="form-label">Last Name</label>
                                                            <input type="text" id="userLastName" name="userLastName" class="form-control" required placeholder="Enter last name of the user">
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback">Please fill this field.</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="userCNIC" class="form-label">CNIC No.</label>
                                                            <input type="number" id="userCNIC" name="userCNIC" class="form-control" required placeholder="Enter CNIC of the user">
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback">Please fill this field.</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="userDOB" class="form-label">Date of Birth.</label>
                                                            <input type="date" id="userDOB" name="userDOB" class="form-control" required placeholder="Enter Date of Birth of the user">
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback">Please fill this field.</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="userEmail" class="form-label">Email</label>
                                                            <input type="email" id="userEmail" name="userEmail" class="form-control" required placeholder="Enter email of the user. ">
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback">Please fill this field. Must be unique</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="userName" class="form-label">Username</label>
                                                            <input type="text" id="userName" name="userName" class="form-control" required placeholder="Enter username of the user. ">
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback">Please fill this field. Must be unique</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="contactNumber" class="form-label">Contact Number</label>
                                                            <input type="number" id="contactNumber" name="contactNumber" class="form-control" required placeholder="Enter Contact Number of the user. ">
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback">Please fill this field. </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="whatsAppContact" class="form-label">WhatsApp Contact Number</label>
                                                            <input type="number" id="whatsAppContact" name="whatsAppContact" class="form-control" required placeholder="Enter WhatsApp Contact of the user. ">
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback">Please fill this field. </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-12">
                                                        <div class="mb-3">
                                                            <label for="userAddress" class="form-label">Address</label>
                                                            <input type="text" id="userAddress" name="userAddress" class="form-control" required placeholder="Enter address of the user. ">
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback">Please fill this field. </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="userRole" class="form-label">User Role</label>
                                                            <select id="userRole" name="userRole" class="form-select select2" data-toggle="select2">
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
                                                            <label for="userStatus" class="form-label">User Status</label>
                                                            <select id="userStatus" name="userStatus" class="form-select select2" data-toggle="select2" required>
                                                                <!-- <option selected disabled value="">Select User Status</option> -->
                                                                <option value="1">Active</option>
                                                                <option value="0">Inactive</option>
                                                            </select>
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback">Please select a status.</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="userProfilePic" class="form-label">Profile Picture</label>
                                                            <input type="file" id="userProfilePic" name="userProfilePic" class="form-control" onchange="validateImage(this)" required>
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback" id="imageError">Please fill this field.</div>
                                                        </div>
                                                    </div>

                                                </div>
                                                <div class="row mb-3">
                                                    <div class="col-lg-12 text-center">
                                                        <button type="submit" id="btnSaveUserData" name="btnSaveUserData" class="btn btn-primary ">Save Role</button>
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
                                                    <th>First Name</th>
                                                    <th>Last Name</th>
                                                    <th>CNIC</th>
                                                    <th>Date of Birth</th>
                                                    <th>Email</th>
                                                    <th>Username</th>
                                                    <th>Contact No.</th>
                                                    <th>WhatsApp Contact No.</th>
                                                    <th>Address</th>
                                                    <th>Profile Pic</th>
                                                    <th>Role</th>
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
                                                    $query = "SELECT u.*, 
                                                                    r.role_name, 
                                                                    uc.username AS created_by_username, 
                                                                    uu.username AS updated_by_username
                                                                FROM users u
                                                                LEFT JOIN roles r ON u.role_id = r.id
                                                                LEFT JOIN users uc ON u.created_by = uc.id
                                                                LEFT JOIN users uu ON u.updated_by = uu.id;
                                                                ";

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
                                                            echo "<td>" . htmlspecialchars($row['first_name']) . "</td>";
                                                            echo "<td>" . htmlspecialchars($row['last_name']) . "</td>";
                                                            echo "<td>" . htmlspecialchars($row['cnic']) . "</td>";
                                                            echo "<td>" . htmlspecialchars($row['dob']) . "</td>";
                                                            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                                                            echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                                                            echo "<td>" . htmlspecialchars($row['contact_number']) . "</td>";
                                                            echo "<td>" . htmlspecialchars($row['whatsapp_contact']) . "</td>";
                                                            echo "<td>" . htmlspecialchars($row['address']) . "</td>";
                                                            echo "<td>" . htmlspecialchars($row['profile_pic_path']) . "</td>";
                                                            echo "<td>" . htmlspecialchars($row['role_name']) . "</td>";

                                                            // Convert status to Active/Inactive
                                                            $statusText = $row['is_active'] == 1 ? 'Active' : 'Not Active';
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
        function validateImage(input) {
            const file = input.files[0];
            if (file) {
                const img = new Image();
                img.onload = function() {
                    const width = img.width;
                    const height = img.height;
                    const errorElement = document.getElementById('imageError');
                    if (width !== 500 || height !== 500) {
                        errorElement.textContent = 'Profile picture must be 500x500 pixels. Current dimensions are ' + width + 'x' + height + '.';
                        input.value = ''; // Clear the file input
                    } else {
                        errorElement.textContent = ''; // Clear any previous error message
                    }
                };
                img.src = URL.createObjectURL(file);
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