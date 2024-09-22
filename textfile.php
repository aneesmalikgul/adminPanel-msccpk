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
        $checkQuery = "SELECT COUNT(*) FROM users WHERE email = ? OR username = ? OR cnic = ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param("ssi", $userEmail, $userName, $userCNIC);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0) {
            $_SESSION['message'][] = ["type" => "danger", "content" => "Email, Username, or CNIC already exists."];
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
?>


<head>
    <title>Manage Users | Mohsin Shaheen Construction Company</title>
    <?php // include 'layouts/title-meta.php'; 
    ?>

    <!-- Select2 css -->
    <!-- <link href="assets/vendor/select2/css/select2.min.css" rel="stylesheet" type="text/css" /> -->

    <?php // include 'layouts/head-css.php'; 
    ?>
    <style></style>

</head>

<body></body>

<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="needs-validation" novalidate enctype="multipart/form-data">
    <div class="row mb-3">
        <h3>User Information</h3>

        <div class="col-lg-6">
            <div class="mb-3">
                <label for="userFirstName" class="form-label">First Name *</label>
                <input type="text" id="userFirstName" name="userFirstName" class="form-control" required placeholder="Enter first name of the user">
                <div class="valid-feedback">Looks good!</div>
                <div class="invalid-feedback">Please fill this field.</div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="mb-3">
                <label for="userLastName" class="form-label">Last Name *</label>
                <input type="text" id="userLastName" name="userLastName" class="form-control" required placeholder="Enter last name of the user">
                <div class="valid-feedback">Looks good!</div>
                <div class="invalid-feedback">Please fill this field.</div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="mb-3">
                <label for="userCNIC" class="form-label">CNIC No. *</label>
                <input type="tel" id="userCNIC" name="userCNIC" class="form-control" required placeholder="Enter CNIC of the user">
                <div class="valid-feedback">Looks good!</div>
                <div class="invalid-feedback">Please fill this field.</div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="mb-3">
                <label for="userDOB" class="form-label">Date of Birth *</label>
                <input type="date" id="userDOB" name="userDOB" class="form-control" required>
                <div class="valid-feedback">Looks good!</div>
                <div class="invalid-feedback">Please fill this field.</div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="mb-3">
                <label for="userEmail" class="form-label">Email *</label>
                <input type="email" id="userEmail" name="userEmail" class="form-control" required placeholder="Enter email of the user">
                <div class="valid-feedback">Looks good!</div>
                <div class="invalid-feedback">Please fill this field.</div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="mb-3">
                <label for="userName" class="form-label">Username *</label>
                <input type="text" id="userName" name="userName" class="form-control" required placeholder="Enter username of the user">
                <div class="valid-feedback">Looks good!</div>
                <div class="invalid-feedback">Please fill this field.</div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="mb-3">
                <label for="contactNumber" class="form-label">Contact Number *</label>
                <input type="tel" id="contactNumber" name="contactNumber" class="form-control" required placeholder="Enter Contact Number of the user">
                <div class="valid-feedback">Looks good!</div>
                <div class="invalid-feedback">Please fill this field.</div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="mb-3">
                <label for="whatsAppContact" class="form-label">WhatsApp Contact Number *</label>
                <input type="tel" id="whatsAppContact" name="whatsAppContact" class="form-control" required placeholder="Enter WhatsApp Contact of the user">
                <div class="valid-feedback">Looks good!</div>
                <div class="invalid-feedback">Please fill this field.</div>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="mb-3">
                <label for="userAddress" class="form-label">Address *</label>
                <input type="text" id="userAddress" name="userAddress" class="form-control" required placeholder="Enter address of the user">
                <div class="valid-feedback">Looks good!</div>
                <div class="invalid-feedback">Please fill this field.</div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="mb-3">
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
            <div class="mb-3">
                <label for="userStatus" class="form-label">User Status *</label>
                <select id="userStatus" name="userStatus" class="form-select select2" data-toggle="select2" required>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
                <div class="valid-feedback">Looks good!</div>
                <div class="invalid-feedback">Please select a status.</div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="mb-3">
                <label for="userProfilePic" class="form-label">Profile Picture *</label>
                <input type="file" id="userProfilePic" name="userProfilePic" class="form-control" onchange="validateImage(this)" required>
                <div class="valid-feedback">Looks good!</div>
                <div class="invalid-feedback" id="imageError">Please fill this field.</div>
            </div>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-lg-12 text-center">
            <button type="submit" id="btnSaveUserData" name="btnSaveUserData" class="btn btn-primary">Save User</button>
        </div>
    </div>
</form>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
    $(document).ready(function() {
        function validateField(fieldName, fieldValue) {
            $.ajax({
                url: 'ajax/user-validation.php',
                type: 'POST',
                data: {
                    [fieldName]: fieldValue
                },
                success: function(response) {
                    let data = JSON.parse(response);

                    if (data[fieldName]) {
                        $('#' + fieldName).addClass('is-invalid');
                        $('#' + fieldName).siblings('.invalid-feedback').text(data[fieldName]);
                        $('#' + fieldName).siblings('.valid-feedback').hide();
                    } else {
                        $('#' + fieldName).removeClass('is-invalid');
                        $('#' + fieldName).siblings('.invalid-feedback').text('Please fill this field.');
                        $('#' + fieldName).siblings('.valid-feedback').show();
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