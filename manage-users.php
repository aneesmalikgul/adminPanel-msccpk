<?php
include 'layouts/session.php';
include 'layouts/config.php';
include 'layouts/functions.php';
include 'layouts/main.php';

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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btnSaveUserData'])) {
    // Collect and sanitize inputs
    $userFirstName = $conn->real_escape_string($_POST['userFirstName']);
    $userLastName = $conn->real_escape_string($_POST['userLastName']);
    $userCNIC = $conn->real_escape_string($_POST['userCNIC']);
    $userDOB = $conn->real_escape_string($_POST['userDOB']);
    $userEmail = $conn->real_escape_string($_POST['userEmail']);
    $userName = $conn->real_escape_string($_POST['userName']);
    $contactNumber = $conn->real_escape_string($_POST['contactNumber']);
    $whatsAppContact = $conn->real_escape_string($_POST['whatsAppContact']);
    $userAddress = $conn->real_escape_string($_POST['userAddress']);
    $userRole = $conn->real_escape_string($_POST['userRole']);
    $userStatus = $conn->real_escape_string($_POST['userStatus']);

    // Assuming the current user's ID is stored in the session
    $createdBy = $_SESSION['user_id']; // Adjust based on how you store user ID in session
    $createdAt = date('Y-m-d H:i:s'); // Current timestamp

    // Image upload handling
    $imageError = '';
    $userProfilePic = $_FILES['userProfilePic'];
    if ($userProfilePic['error'] === UPLOAD_ERR_OK) {
        // Validate image size
        list($width, $height) = getimagesize($userProfilePic['tmp_name']);
        if ($width !== 500 || $height !== 500) {
            $imageError = "Image must be 500x500 pixels.";
        }

        // Create the uploads directory if it doesn't exist
        $uploadDir = 'assets/uploads/user-image/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Create a unique name for the image
        $imageName = "{$userCNIC}-{$userName}-" . date('YmdHis') . '.' . pathinfo($userProfilePic['name'], PATHINFO_EXTENSION);
        $imagePath = $uploadDir . $imageName;

        // Move the uploaded file
        if (!move_uploaded_file($userProfilePic['tmp_name'], $imagePath)) {
            $imageError = "Failed to move uploaded file.";
        }
    } else {
        $imageError = "Failed to upload image.";
    }

    // Check for uniqueness
    if (empty($imageError)) {
        // $checkQuery = "SELECT COUNT(*) FROM users WHERE email = ? OR username = ? OR cnic = ?";
        // $stmt = $conn->prepare($checkQuery);
        // $stmt->bind_param("ssi", $userEmail, $userName, $userCNIC);
        // $stmt->execute();
        // $stmt->bind_result($count);
        // $stmt->fetch();
        // $stmt->close();

        // if ($count > 0) {
        //     $_SESSION['message'][] = ["type" => "danger", "content" => "Email, Username, or CNIC already exists."];


        // Check if email, username, or CNIC already exists for another user
        if (checkUniqueField($conn, 'email', $userEmail)) {
            $_SESSION['message'][] = ["type" => "danger", "content" => "Email already exists for another user."];
        } elseif (checkUniqueField($conn, 'username', $userName)) {
            $_SESSION['message'][] = ["type" => "danger", "content" => "Username already exists for another user."];
        } elseif (checkUniqueField($conn, 'cnic', $userCNIC)) {
            $_SESSION['message'][] = ["type" => "danger", "content" => "CNIC already exists for another user."];
        } else {
            // Start a transaction
            $conn->begin_transaction();

            try {
                // Prepare insert query
                // Prepare insert query
                $insertQuery = "INSERT INTO users (first_name, last_name, cnic, dob, email, username, contact_number, whatsapp_contact, address, role_id, is_active, profile_pic_path, created_by, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($insertQuery);

                // Adjust the binding parameters
                $stmt->bind_param("ssssssisssssis", $userFirstName, $userLastName, $userCNIC, $userDOB, $userEmail, $userName, $contactNumber, $whatsAppContact, $userAddress, $userRole, $userStatus, $imagePath, $createdBy, $createdAt);

                // Execute the query
                if ($stmt->execute()) {
                    $conn->commit();
                    $_SESSION['message'][] = ["type" => "success", "content" => "User added successfully!"];
                    header("Location: manage-users.php");
                    exit();
                } else {
                    throw new Exception("Failed to insert user: " . $stmt->error);
                }
            } catch (Exception $e) {
                $conn->rollback();
                $_SESSION['message'][] = ["type" => "danger", "content" => $e->getMessage()];
            }
            // finally {
            //     if (isset($stmt) && $stmt) {
            //         $stmt->close();
            //     }
            // }
        }
    } else {
        $_SESSION['message'][] = ["type" => "danger", "content" => $imageError];
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btnUpdateUserData'])) {
    // Collect and sanitize inputs
    $userId = $conn->real_escape_string($_POST['userId']);
    $userFirstName = $conn->real_escape_string($_POST['userFirstName']);
    $userLastName = $conn->real_escape_string($_POST['userLastName']);
    $userCNIC = $conn->real_escape_string($_POST['userCNIC']);
    $userDOB = $conn->real_escape_string($_POST['userDOB']);
    $userEmail = $conn->real_escape_string($_POST['userEmail']);
    $userName = $conn->real_escape_string($_POST['userName']);
    $contactNumber = $conn->real_escape_string($_POST['contactNumber']);
    $whatsAppContact = $conn->real_escape_string($_POST['whatsAppContact']);
    $userAddress = $conn->real_escape_string($_POST['userAddress']);
    $userRole = $conn->real_escape_string($_POST['userRole']);
    $userStatus = $conn->real_escape_string($_POST['userStatus']);

    // Assuming the current user's ID is stored in the session
    $updatedBy = $_SESSION['user_id']; // Adjust based on how you store user ID in session
    $updatedAt = date('Y-m-d H:i:s'); // Current timestamp

    // Image upload handling
    $imageError = '';
    $userProfilePic = $_FILES['userProfilePic'];
    $imagePath = '';

    if ($userProfilePic['error'] === UPLOAD_ERR_OK) {
        // Validate image size
        list($width, $height) = getimagesize($userProfilePic['tmp_name']);
        if ($width !== 500 || $height !== 500) {
            $imageError = "Image must be 500x500 pixels.";
        }

        // Create the uploads directory if it doesn't exist
        $uploadDir = 'assets/uploads/user-image/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Create a unique name for the image
        $imageName = "{$userCNIC}-{$userName}-" . date('YmdHis') . '.' . pathinfo($userProfilePic['name'], PATHINFO_EXTENSION);
        $imagePath = $uploadDir . $imageName;

        // Move the uploaded file
        if (!move_uploaded_file($userProfilePic['tmp_name'], $imagePath)) {
            $imageError = "Failed to move uploaded file.";
        }
    }

    // Check for uniqueness, except for the current user being updated
    if (empty($imageError)) {

        // Check if email, username, or CNIC already exists for another user
        if (checkUniqueField($conn, 'email', $userEmail, $userId)) {
            $_SESSION['message'][] = ["type" => "danger", "content" => "Email already exists for another user."];
        } elseif (checkUniqueField($conn, 'username', $userName, $userId)) {
            $_SESSION['message'][] = ["type" => "danger", "content" => "Username already exists for another user."];
        } elseif (checkUniqueField($conn, 'cnic', $userCNIC, $userId)) {
            $_SESSION['message'][] = ["type" => "danger", "content" => "CNIC already exists for another user."];
        } else {
            // Start a transaction
            $conn->begin_transaction();

            try {
                // Prepare update query
                $updateQuery = "UPDATE users SET first_name=?, last_name=?, cnic=?, dob=?, email=?, username=?, contact_number=?, whatsapp_contact=?, address=?, role_id=?, is_active=?, updated_by=?, updated_at=?";

                // Add profile picture field if image uploaded successfully
                if (!empty($imagePath)) {
                    $updateQuery .= ", profile_pic_path=?";
                    $queryParams = [$userFirstName, $userLastName, $userCNIC, $userDOB, $userEmail, $userName, $contactNumber, $whatsAppContact, $userAddress, $userRole, $userStatus, $updatedBy, $updatedAt, $imagePath, $userId];
                    $paramTypes = "ssssssssssisssi";
                } else {
                    $queryParams = [$userFirstName, $userLastName, $userCNIC, $userDOB, $userEmail, $userName, $contactNumber, $whatsAppContact, $userAddress, $userRole, $userStatus, $updatedBy, $updatedAt, $userId];
                    $paramTypes = "ssssssssssissi";
                }

                // Add where clause for user ID
                $updateQuery .= " WHERE id = ?";

                // Prepare the statement
                $stmt = $conn->prepare($updateQuery);

                // Bind the parameters
                $stmt->bind_param($paramTypes, ...$queryParams);

                // Execute the query
                if ($stmt->execute()) {
                    $conn->commit();
                    $_SESSION['message'][] = ["type" => "success", "content" => "User updated successfully!"];
                    header("Location: manage-users.php");
                    exit();
                } else {
                    throw new Exception("Failed to update user: " . $stmt->error);
                }
            } catch (Exception $e) {
                $conn->rollback();
                $_SESSION['message'][] = ["type" => "danger", "content" => $e->getMessage()];
            }
        }
    } else {
        $_SESSION['message'][] = ["type" => "danger", "content" => $imageError];
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
                            <div id="session-messages">
                                <!-- Error or success messages will be appended here dynamically via JS -->
                            </div>

                            <h2 class="text-center">Add New User</h2>
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="needs-validation" novalidate enctype="multipart/form-data">
                                            <div class="row mb-3">
                                                <h3>User Information</h3>

                                                <div class="col-lg-6">
                                                    <div class="mb-2">
                                                        <label for="userFirstName" class="form-label">First Name *</label>
                                                        <input type="text" id="userFirstName" name="userFirstName" class="form-control" required placeholder="Enter first name of the user">
                                                        <div class="valid-feedback">Looks good!</div>
                                                        <div class="invalid-feedback">Please fill this field.</div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="mb-2">
                                                        <label for="userLastName" class="form-label">Last Name *</label>
                                                        <input type="text" id="userLastName" name="userLastName" class="form-control" required placeholder="Enter last name of the user">
                                                        <div class="valid-feedback">Looks good!</div>
                                                        <div class="invalid-feedback">Please fill this field.</div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="mb-2">
                                                        <label for="userCNIC" class="form-label">CNIC No. *</label>
                                                        <input type="text" id="userCNIC" name="userCNIC" class="form-control" required placeholder="Enter CNIC of the user" data-toggle="input-mask" data-mask-format="00000-0000000-0">
                                                        <div class="valid-feedback">Looks good!</div>
                                                        <div class="invalid-feedback">Please fill this field.</div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="mb-2">
                                                        <label for="userDOB" class="form-label">Date of Birth *</label>
                                                        <input type="date" id="userDOB" name="userDOB" class="form-control" required>
                                                        <div class="valid-feedback">Looks good!</div>
                                                        <div class="invalid-feedback">Please fill this field.</div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="mb-2">
                                                        <label for="userEmail" class="form-label">Email *</label>
                                                        <input type="email" id="userEmail" name="userEmail" class="form-control" required placeholder="Enter email of the user">
                                                        <div class="valid-feedback">Looks good!</div>
                                                        <div class="invalid-feedback">Please fill this field.</div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="mb-2">
                                                        <label for="userName" class="form-label">Username *</label>
                                                        <input type="text" id="userName" name="userName" class="form-control" required placeholder="Enter username of the user">
                                                        <div class="valid-feedback">Looks good!</div>
                                                        <div class="invalid-feedback">Please fill this field.</div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="mb-2">
                                                        <label for="contactNumber" class="form-label">Contact Number *</label>
                                                        <input type="tel" id="contactNumber" name="contactNumber" class="form-control" required placeholder="Enter Contact Number of the user" data-toggle="input-mask" data-mask-format="(+00)-000-0000000">
                                                        <div class="valid-feedback">Looks good!</div>
                                                        <div class="invalid-feedback">Please fill this field.</div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="mb-2">
                                                        <label for="whatsAppContact" class="form-label">WhatsApp Contact Number *</label>
                                                        <input type="tel" id="whatsAppContact" name="whatsAppContact" class="form-control" required placeholder="Enter WhatsApp Contact of the user" data-toggle="input-mask" data-mask-format="(+00)-000-0000000">
                                                        <div class="valid-feedback">Looks good!</div>
                                                        <div class="invalid-feedback">Please fill this field.</div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-12">
                                                    <div class="mb-2">
                                                        <label for="userAddress" class="form-label">Address *</label>
                                                        <input type="text" id="userAddress" name="userAddress" class="form-control" required placeholder="Enter address of the user">
                                                        <div class="valid-feedback">Looks good!</div>
                                                        <div class="invalid-feedback">Please fill this field.</div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="mb-2">
                                                        <label for="userRole" class="form-label">User Role *</label>
                                                        <select id="userRole" name="userRole" class="form-select select2" data-toggle="select2" required>
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
                                                    <div class="mb-2">
                                                        <label for="userStatus" class="form-label">User Status *</label>
                                                        <select id="userStatus" name="userStatus" class="form-select select2" data-toggle="select2" required>
                                                            <option value="1">Active</option>
                                                            <option value="0">Inactive</option>
                                                        </select>
                                                        <div class="valid-feedback">Looks good!</div>
                                                        <div class="invalid-feedback">Please select a status.</div>
                                                    </div>
                                                </div>
                                                <!-- <div class="col-lg-6">
                                                    <div class="mb-2">
                                                        <label for="userProfilePic" class="form-label">Profile Picture *</label>
                                                        <input type="file" id="userProfilePic" name="userProfilePic" class="form-control" accept="image/*" onchange="validateImage(this)" required>
                                                        <img id="profilePicPreview" src="<?php // echo htmlspecialchars($user['profile_pic_path']); 
                                                                                            ?>" alt="Profile Picture" class="img-thumbnail mt-2" style="max-width: 150px;">
                                                        <div class="valid-feedback">Looks good!</div>
                                                        <div class="invalid-feedback" id="imageError">Please Upload a Profile Picture. It must be 500x500 pixels. </div>
                                                    </div>
                                                </div> -->
                                                <div class="col-lg-6">
                                                    <div class="mb-3">
                                                        <label for="userProfilePic" class="form-label">Profile Picture</label>
                                                        <input type="file" id="userProfilePic" name="userProfilePic" class="form-control" onchange="validateImage(this)" required>
                                                        <img id="profilePicPreview" src="<?php
                                                                                            echo htmlspecialchars($user['profile_pic_path']);
                                                                                            ?>"
                                                            alt="Profile Picture" class="img-thumbnail mt-2" style="max-width: 150px;">
                                                        <div class="valid-feedback">Looks good!</div>
                                                        <div class="invalid-feedback" id="imageError">Please Upload a Profile Picture. It must be 500x500 pixels. </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-lg-12 text-center">
                                                    <button type="submit" id="btnSaveUserData" name="btnSaveUserData" class="btn btn-primary">Save User</button>
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
                                                    $conn->begin_transaction();

                                                    // Define the query to fetch roles with the user who created and updated them
                                                    $query = "SELECT u.*, 
                                                                    r.role_name, 
                                                                    uc.username AS created_by_username, 
                                                                    uu.username AS updated_by_username
                                                                FROM users u
                                                                LEFT JOIN roles r ON u.role_id = r.id
                                                                LEFT JOIN users uc ON u.created_by = uc.id
                                                                LEFT JOIN users uu ON u.updated_by = uu.id";

                                                    // Prepare the statement
                                                    if ($stmt = $conn->prepare($query)) {

                                                        // Execute the statement
                                                        $stmt->execute();

                                                        // Get the result set
                                                        $result = $stmt->get_result();

                                                        // Check if rows were found
                                                        if ($result->num_rows > 0) {
                                                            // Loop through the results and display them
                                                            while ($row = $result->fetch_assoc()) {
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
                                                                echo "<td><img src='" . htmlspecialchars($row['profile_pic_path']) . "' alt='user-image' width='32' class='img-fluid avatar-sm rounded'></td>";
                                                                echo "<td>" . htmlspecialchars($row['role_name']) . "</td>";

                                                                // Convert status to Active/Inactive
                                                                $statusText = $row['is_active'] == 1 ? 'Active' : 'Not Active';
                                                                echo "<td>" . htmlspecialchars($statusText) . "</td>";

                                                                // Display the 'created by' username
                                                                echo "<td>" . htmlspecialchars($row['created_by_username']) . "</td>";

                                                                // Display the 'created at' date
                                                                echo "<td>" . htmlspecialchars(date('d-M-Y', strtotime($row['created_at']))) . "</td>";

                                                                // Edit button
                                                                echo "<td>";
                                                                echo "<a href='edit-user.php?id=" . urlencode($row['id']) . "' class='btn btn-warning'><i class='ri-pencil-line'></i></a>";
                                                                echo "  ";
                                                                // Delete button with confirmation
                                                                echo "<a href='delete-user.php?id=" . urlencode($row['id']) . "' class='btn btn-danger' onclick='return confirmDelete();' ><i class='ri-delete-bin-line'></i></a>";
                                                                echo "</td>";

                                                                echo "</tr>";
                                                            }
                                                        } else {
                                                            echo "<tr><td colspan='6'>No users found</td></tr>";
                                                        }

                                                        // Commit the transaction (optional if you're modifying data)
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
        function confirmDelete() {
            // Show a confirmation dialog
            var result = confirm("Are you sure you want to delete this user?");

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
        function displayImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('profilePicPreview').src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

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
                        document.getElementById('profilePicPreview').src = ''; // Clear the preview
                    } else {
                        errorElement.textContent = ''; // Clear any previous error message
                        displayImage(input);
                    }
                };
                img.src = URL.createObjectURL(file);
                // Display the image regardless of validation
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
    <!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script> -->
    <script>
        $(document).ready(function() {
            function showSessionMessage(type, message) {
                // This function can simulate adding messages to your session
                let alertHtml = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
                            ${message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                         </div>`;
                $("#session-messages").append(alertHtml); // Assuming you have a #session-messages div to append messages
            }

            function validateField(fieldName, fieldValue) {
                $.ajax({
                    url: 'ajax/user-validation.php',
                    type: 'POST',
                    data: {
                        [fieldName]: fieldValue
                    },
                    success: function(response) {
                        let data = JSON.parse(response);

                        if (data.status === 'error') {
                            // Show the relevant error message for the field
                            if (data.messages[fieldName]) {
                                showSessionMessage('danger', data.messages[fieldName]);
                            }
                        } else {
                            // If valid, you can remove any previous messages or just proceed
                            console.log(fieldName + ' is valid.');
                        }
                    }
                });
            }

            // CNIC validation on keyup
            $('#userCNIC').on('keyup', function() {
                validateField('cnic', $(this).val());
            });

            // Email validation on keyup
            $('#userEmail').on('keyup', function() {
                validateField('email', $(this).val());
            });

            // Username validation on keyup
            $('#userName').on('keyup', function() {
                validateField('username', $(this).val());
            });
        });
    </script>

</body>

</html>