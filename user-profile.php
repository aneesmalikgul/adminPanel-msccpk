<?php
<<<<<<< HEAD
include 'layouts/session.php';
include 'layouts/config.php';
include 'layouts/functions.php';
include 'layouts/main.php';
?>

=======

include 'layouts/session.php';
include 'layouts/main.php';
include 'layouts/config.php';
include 'layouts/functions.php';

// Check if the user has the necessary permissions
if (!hasPermission('view_user_profile') || !hasPermission('manage_user') || !hasPermission('edit_user')) {
    header('Location: index.php');
    exit();
}

$user = [];
$tasks = [];
$attendance = []; // Initialize an array to hold attendance records

if (isset($_GET['id'])) {
    $user_id = intval($_GET['id']); // Sanitize user input

    // Check if the connection is established
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    try {
        // Query to fetch attendance history for the specific user
        $queryAttendance = "
            SELECT 
                attendance_date, 
                check_in, 
                check_out, 
                attendance_status 
            FROM 
                attendance 
            WHERE 
                user_id = ? 
            ORDER BY 
                attendance_date DESC; 
        ";

        // Prepare and execute the attendance query
        $stmtAttendance = $conn->prepare($queryAttendance);
        if ($stmtAttendance) {
            $stmtAttendance->bind_param("i", $user_id);
            $stmtAttendance->execute();
            $resultAttendance = $stmtAttendance->get_result();

            // Fetch attendance records into the array
            if ($resultAttendance->num_rows > 0) {
                $attendance = $resultAttendance->fetch_all(MYSQLI_ASSOC); // Efficient fetching
            }
            $stmtAttendance->close(); // Close attendance statement
        }

        // Query to fetch the user details along with role name
        $queryUser = "
            SELECT users.*, roles.role_name 
            FROM users 
            JOIN roles ON users.role_id = roles.id
            WHERE users.id = ?;
        ";

        // Prepare and execute the user query
        $stmtUser = $conn->prepare($queryUser);
        $stmtUser->bind_param('i', $user_id);
        $stmtUser->execute();
        $resultUser = $stmtUser->get_result();

        if ($resultUser->num_rows > 0) {
            $user = $resultUser->fetch_assoc();
        } else {
            header("Location: manage-users.php");
            exit();
        }
        $stmtUser->close(); // Close user statement

        // Query to fetch tasks assigned to the user
        $queryTasks = "
    SELECT 
        t.id AS task_id, 
        tasks.task_title, 
        t.assigned_at, 
        t.status, 
        t.remarks, 
        assigned_by_user.username AS assigned_by_name, 
        assigned_to_user.username AS assigned_to_name,
        u.role_id AS user_role_id, 
        r.role_name AS user_role_name 
    FROM 
        task_assignments t 
    JOIN 
        users AS assigned_by_user ON t.assigned_by = assigned_by_user.id 
    JOIN 
        users AS assigned_to_user ON t.assigned_to = assigned_to_user.id 
    JOIN 
        tasks ON t.task_id = tasks.id 
    JOIN 
        users u ON u.id = ? -- logged-in user's ID
    JOIN 
        roles r ON u.role_id = r.id
    WHERE 
        -- Condition to check roles and limit tasks based on role
        (
            (u.role_id = 1 AND t.assigned_by = ?) -- Admin sees only tasks they assigned
            OR 
            (u.role_id IN (2, 3) AND t.assigned_to = ?) -- Users see only tasks assigned to them
        )
";

        // Prepare and execute the tasks query
        $stmtTasks = $conn->prepare($queryTasks);
        if ($stmtTasks) {
            $stmtTasks->bind_param("iii", $user_id, $user_id, $user_id);  // Bind the logged-in user's ID in multiple places
            $stmtTasks->execute();
            $resultTasks = $stmtTasks->get_result();  // Get the result set from the statement

            // Fetch tasks into the tasks array
            if ($resultTasks->num_rows > 0) {
                $tasks = $resultTasks->fetch_all(MYSQLI_ASSOC);  // Fetch all tasks efficiently
            }
            $stmtTasks->close();  // Close the statement
        }
    } catch (Exception $e) {
        // Handle exception and set the session message
        $_SESSION['message'] = ['type' => 'error', 'content' => $e->getMessage()];
    }
} else {
    header("Location: manage-users.php");
    exit();
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
    $updatedBy = $_SESSION['user_id']; // Logged-in user's ID
    $updatedAt = date('Y-m-d H:i:s'); // Current timestamp

    // Check if image is uploaded
    $imagePath = $user['profile_pic_path']; // Set existing image path
    $imageError = '';

    if ($_FILES['userProfilePic']['error'] === UPLOAD_ERR_OK) {
        // Validate image size
        list($width, $height) = getimagesize($_FILES['userProfilePic']['tmp_name']);
        if ($width != 500 || $height != 500) {
            $imageError = "Image must be exactly 500x500 pixels.";
        } else {
            // Define upload directory
            $uploadDir = 'assets/uploads/user-image/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Create a unique file name
            $imageName = "{$userCNIC}-{$userName}-" . date('YmdHis') . '.' . pathinfo($_FILES['userProfilePic']['name'], PATHINFO_EXTENSION);
            $imagePath = $uploadDir . $imageName;

            // Move uploaded file
            if (!move_uploaded_file($_FILES['userProfilePic']['tmp_name'], $imagePath)) {
                $imageError = "Failed to upload image.";
            }
        }
    }

    // Proceed only if no image error
    if (empty($imageError)) {
        // Check for unique constraints (email, username, CNIC)
        if (checkUniqueField($conn, 'email', $userEmail, $userId)) {
            $_SESSION['message'][] = ["type" => "danger", "content" => "Email already exists for another user."];
        } elseif (checkUniqueField($conn, 'username', $userName, $userId)) {
            $_SESSION['message'][] = ["type" => "danger", "content" => "Username already exists for another user."];
        } elseif (checkUniqueField($conn, 'cnic', $userCNIC, $userId)) {
            $_SESSION['message'][] = ["type" => "danger", "content" => "CNIC already exists for another user."];
        } else {
            // Start transaction
            $conn->begin_transaction();
            try {
                // Update query
                $updateQuery = "UPDATE users SET first_name=?, last_name=?, cnic=?, dob=?, email=?, username=?, contact_number=?, whatsapp_contact=?, address=?, updated_by=?, updated_at=?, profile_pic_path=? WHERE id=?";

                // Prepare the statement
                $stmt = $conn->prepare($updateQuery);
                $stmt->bind_param("ssssssssssssi", $userFirstName, $userLastName, $userCNIC, $userDOB, $userEmail, $userName, $contactNumber, $whatsAppContact, $userAddress, $updatedBy, $updatedAt, $imagePath, $userId);

                // Execute the statement
                if ($stmt->execute()) {
                    $conn->commit();
                    $_SESSION['message'][] = ["type" => "success", "content" => "User updated successfully!"];
                    header("Location: user-profile.php");
                    exit();
                } else {
                    throw new Exception("Failed to update user data.");
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


>>>>>>> kiran
<head>
    <title>Profile | Mohsin Shaheen Construction Company</title>
    <?php include 'layouts/title-meta.php'; ?>

    <!-- Select2 css -->
    <link href="assets/vendor/select2/css/select2.min.css" rel="stylesheet" type="text/css" />

    <?php include 'layouts/head-css.php'; ?>
    <style></style>

</head>

<body>
    <!-- Begin page -->
    <div class="wrapper">

        <?php include 'layouts/menu.php'; ?>

        <!-- ============================================================== -->
        <!-- Start Page Content here -->
        <!-- ============================================================== -->

        <div class="content-page">
            <div class="content">

                <!-- Start Content-->
                <div class="container-fluid">

                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box">
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">mscc.pk</a></li>
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Pages</a></li>
                                        <li class="breadcrumb-item active">Profile</li>
                                    </ol>
                                </div>
                                <h4 class="page-title">Profile</h4>
                            </div>
                        </div>
                    </div>


                    <div class="row">
                        <div class="col-xl-4 col-lg-5">
                            <div class="card text-center">
                                <div class="card-body">
<<<<<<< HEAD
                                    <img src="assets/images/users/avatar-1.jpg" class="rounded-circle avatar-lg img-thumbnail" alt="profile-image">

                                    <h4 class="mb-1 mt-2">Tosha Minner</h4>
                                    <p class="text-muted">Founder</p>

                                    <button type="button" class="btn btn-success btn-sm mb-2">Follow</button>
                                    <button type="button" class="btn btn-danger btn-sm mb-2">Message</button>

                                    <div class="text-start mt-3">
                                        <h4 class="fs-13 text-uppercase">About Me :</h4>
                                        <p class="text-muted mb-3">
                                            Hi I'm Tosha Minner,has been the industry's standard dummy text ever since the
                                            1500s, when an unknown printer took a galley of type.
                                        </p>
                                        <p class="text-muted mb-2"><strong>Full Name :</strong> <span class="ms-2">Tosha K. Minner</span></p>

                                        <p class="text-muted mb-2"><strong>Mobile :</strong><span class="ms-2">(123)
                                                123 1234</span></p>

                                        <p class="text-muted mb-2"><strong>Email :</strong> <span class="ms-2 ">user@email.domain</span></p>

                                        <p class="text-muted mb-1"><strong>Location :</strong> <span class="ms-2">USA</span></p>
=======
                                    <img src="<?php echo htmlspecialchars($user['profile_pic_path']); ?>" class="rounded-circle avatar-lg img-thumbnail" alt="profile-image">

                                    <h4 class="mb-1 mt-2"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h4>
                                    <!-- <p class="text-muted"></p> -->

                                    <button type="button" class="btn btn-success btn-sm mb-2"><?php $statusText = $user['is_active'] == 1 ? 'Active' : 'Not Active';
                                                                                                echo "<td>" . htmlspecialchars($statusText) . "</td>";  ?></button>
                                    <button type="button" class="btn btn-danger btn-sm mb-2"><?php echo htmlspecialchars(ucfirst(strtolower($user['role_name']))); ?>
                                    </button>

                                    <div class="text-start mt-3">
                                        <!-- <h4 class="fs-13 text-uppercase">Status :</h4>
                                        <p class="text-muted mb-3">
                                           
                                        </p> -->
                                        <p class="text-muted mb-2"><strong>Full Name :</strong> <span class="ms-2"> <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></span></p>
                                        <p class="text-muted mb-2"><strong>Date of Birth :</strong> <span class="ms-2"><?php echo htmlspecialchars($user['dob']); ?></span></p>
                                        <p class="text-muted mb-2"><strong>CNIC No :</strong> <span class="ms-2"><?php echo htmlspecialchars($user['cnic']); ?></span></p>
                                        <!-- <p class="text-muted mb-2"><strong>user Role:</strong> <span class="ms-2"></span></p> -->

                                        <p class="text-muted mb-2"><strong>Mobile :</strong><span class="ms-2"><?php echo htmlspecialchars($user['contact_number']); ?></span></p>

                                        <p class="text-muted mb-2"><strong>WhatsApp :</strong> <span class="ms-2 "><?php echo htmlspecialchars($user['whatsapp_contact']); ?></span></p>
                                        <p class="text-muted mb-2"><strong>Email :</strong> <span class="ms-2 "><?php echo htmlspecialchars($user['email']); ?></span></p>

                                        <p class="text-muted mb-1"><strong>Address :</strong> <span class="ms-2"><?php echo htmlspecialchars($user['address']); ?></span></p>

>>>>>>> kiran
                                    </div>

                                    <ul class="social-list list-inline mt-3 mb-0">
                                        <li class="list-inline-item">
                                            <a href="javascript: void(0);" class="social-list-item border-primary text-primary"><i class="ri-facebook-circle-fill"></i></a>
                                        </li>
                                        <li class="list-inline-item">
                                            <a href="javascript: void(0);" class="social-list-item border-danger text-danger"><i class="ri-google-fill"></i></a>
                                        </li>
                                        <li class="list-inline-item">
                                            <a href="javascript: void(0);" class="social-list-item border-info text-info"><i class="ri-twitter-fill"></i></a>
                                        </li>
                                        <li class="list-inline-item">
                                            <a href="javascript: void(0);" class="social-list-item border-secondary text-secondary"><i class="ri-github-fill"></i></a>
                                        </li>
                                    </ul>
                                </div> <!-- end card-body -->
                            </div> <!-- end card -->

                            <!-- Messages-->
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h4 class="header-title">Messages</h4>
                                        <div class="dropdown">
                                            <a href="#" class="dropdown-toggle arrow-none card-drop" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="ri-more-2-fill"></i>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <!-- item-->
                                                <a href="javascript:void(0);" class="dropdown-item">Settings</a>
                                                <!-- item-->
                                                <a href="javascript:void(0);" class="dropdown-item">Action</a>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="inbox-widget">
                                        <div class="inbox-item">
                                            <div class="inbox-item-img"><img src="assets/images/users/avatar-2.jpg" class="rounded-circle" alt=""></div>
                                            <p class="inbox-item-author">Tomaslau</p>
<<<<<<< HEAD
                                            <p class="inbox-item-text">I've finished it! See you so...</p>
=======
                                            <p class="inbox-item-text">I' ve finished it! See you so...</p>
>>>>>>> kiran
                                            <p class="inbox-item-date">
                                                <a href="#" class="btn btn-sm btn-link text-info fs-13"> Reply </a>
                                            </p>
                                        </div>
                                        <div class="inbox-item">
                                            <div class="inbox-item-img"><img src="assets/images/users/avatar-3.jpg" class="rounded-circle" alt=""></div>
                                            <p class="inbox-item-author">Stillnotdavid</p>
                                            <p class="inbox-item-text">This theme is awesome!</p>
                                            <p class="inbox-item-date">
                                                <a href="#" class="btn btn-sm btn-link text-info fs-13"> Reply </a>
                                            </p>
                                        </div>
                                        <div class="inbox-item">
                                            <div class="inbox-item-img"><img src="assets/images/users/avatar-4.jpg" class="rounded-circle" alt=""></div>
                                            <p class="inbox-item-author">Kurafire</p>
                                            <p class="inbox-item-text">Nice to meet you</p>
                                            <p class="inbox-item-date">
                                                <a href="#" class="btn btn-sm btn-link text-info fs-13"> Reply </a>
                                            </p>
                                        </div>

                                        <div class="inbox-item">
                                            <div class="inbox-item-img"><img src="assets/images/users/avatar-5.jpg" class="rounded-circle" alt=""></div>
                                            <p class="inbox-item-author">Shahedk</p>
                                            <p class="inbox-item-text">Hey! there I'm available...</p>
                                            <p class="inbox-item-date">
                                                <a href="#" class="btn btn-sm btn-link text-info fs-13"> Reply </a>
                                            </p>
                                        </div>
                                        <div class="inbox-item">
                                            <div class="inbox-item-img"><img src="assets/images/users/avatar-6.jpg" class="rounded-circle" alt=""></div>
                                            <p class="inbox-item-author">Adhamdannaway</p>
                                            <p class="inbox-item-text">This theme is awesome!</p>
                                            <p class="inbox-item-date">
                                                <a href="#" class="btn btn-sm btn-link text-info fs-13"> Reply </a>
                                            </p>
                                        </div>
                                    </div> <!-- end inbox-widget -->
                                </div> <!-- end card-body-->
                            </div> <!-- end card-->

                        </div> <!-- end col-->

                        <div class="col-xl-8 col-lg-7">
                            <!-- Chart-->
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="header-title mb-3">Orders & Revenue</h4>
                                    <div dir="ltr">
                                        <div style="height: 260px;" class="chartjs-chart">
                                            <canvas id="high-performing-product"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End Chart-->

                            <div class="card">
                                <div class="card-body">
<<<<<<< HEAD
                                    <ul class="nav nav-pills bg-nav-pills nav-justified mb-3">
=======
                                    <ul class="nav nav-pills bg-nav-pills nav-justified mb-5=4">
>>>>>>> kiran
                                        <li class="nav-item">
                                            <a href="#aboutme" data-bs-toggle="tab" aria-expanded="false" class="nav-link rounded-start rounded-0 active">
                                                About
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="#timeline" data-bs-toggle="tab" aria-expanded="true" class="nav-link rounded-0">
                                                Timeline
                                            </a>
                                        </li>
                                        <li class="nav-item">
<<<<<<< HEAD
                                            <a href="#settings" data-bs-toggle="tab" aria-expanded="false" class="nav-link rounded-end rounded-0">
=======
                                            <a href="#settings" data-bs-toggle="tab" aria-expanded="false" class="nav-link rounded-end rounded-0 settings">
>>>>>>> kiran
                                                Settings
                                            </a>
                                        </li>
                                    </ul>
                                    <div class="tab-content">
                                        <div class="tab-pane show active" id="aboutme">
<<<<<<< HEAD

                                            <h5 class="text-uppercase mb-3"><i class="ri-briefcase-line me-1"></i>
                                                Projects</h5>
=======
                                            <br>
                                            <h5 class="text-uppercase mb-3"><i class="ri-briefcase-line me-1"></i>
                                                Task History</h5>
>>>>>>> kiran
                                            <div class="table-responsive">
                                                <table class="table table-sm table-centered table-hover table-borderless mb-0">
                                                    <thead class="border-top border-bottom bg-light-subtle border-light">
                                                        <tr>
                                                            <th>#</th>
<<<<<<< HEAD
                                                            <th>Clients</th>
                                                            <th>Project Name</th>
                                                            <th>Start Date</th>
                                                            <th>Due Date</th>
=======
                                                            <th>Task name</th>
                                                            <th>Start Date</th>
                                                            <?php if (!empty($tasks) && $tasks[0]['user_role_id'] == 1): // If role_id is 1 (Admin) 
                                                            ?>
                                                                <th>Assign To</th>
                                                            <?php else: // For role_id 2 or 3 (Users) 
                                                            ?>
                                                                <th>Assign By</th>
                                                            <?php endif; ?>
                                                            <th>Remarks</th>
>>>>>>> kiran
                                                            <th>Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
<<<<<<< HEAD
                                                        <tr>
                                                            <td>1</td>
                                                            <td><img src="assets/images/users/avatar-2.jpg" alt="table-user" class="me-2 rounded-circle" height="24"> Halette Boivin</td>
                                                            <td>App design and development</td>
                                                            <td>01/01/2022</td>
                                                            <td>10/12/2023</td>
                                                            <td><span class="badge bg-info-subtle text-info">Work in Progress</span></td>
                                                        </tr>
                                                        <tr>
                                                            <td>2</td>
                                                            <td><img src="assets/images/users/avatar-3.jpg" alt="table-user" class="me-2 rounded-circle" height="24"> Durandana Jolicoeur</td>
                                                            <td>Coffee detail page - Main Page</td>
                                                            <td>21/07/2023</td>
                                                            <td>12/05/2024</td>
                                                            <td><span class="badge bg-danger-subtle text-danger">Pending</span></td>
                                                        </tr>
                                                        <tr>
                                                            <td>3</td>
                                                            <td><img src="assets/images/users/avatar-4.jpg" alt="table-user" class="me-2 rounded-circle" height="24"> Lucas Sabourin</td>
                                                            <td>Poster illustation design</td>
                                                            <td>18/03/2023</td>
                                                            <td>28/09/2023</td>
                                                            <td><span class="badge bg-success-subtle text-success">Done</span></td>
                                                        </tr>
                                                        <tr>
                                                            <td>4</td>
                                                            <td><img src="assets/images/users/avatar-6.jpg" alt="table-user" class="me-2 rounded-circle" height="24"> Donatien Brunelle</td>
                                                            <td>Drinking bottle graphics</td>
                                                            <td>02/10/2022</td>
                                                            <td>07/05/2023</td>
                                                            <td><span class="badge bg-info-subtle text-info">Work in Progress</span></td>
                                                        </tr>
                                                        <tr>
                                                            <td>5</td>
                                                            <td><img src="assets/images/users/avatar-5.jpg" alt="table-user" class="me-2 rounded-circle" height="24"> Karel Auberjo</td>
                                                            <td>Landing page design - Home</td>
                                                            <td>17/01/2022</td>
                                                            <td>25/05/2023</td>
                                                            <td><span class="badge bg-warning-subtle text-warning">Coming soon</span></td>
                                                        </tr>

                                                    </tbody>
                                                </table>
                                            </div>

                                            <h5 class="text-uppercase mt-4"><i class="ri-macbook-line me-1"></i>
=======
                                                        <?php
                                                        if (!empty($tasks)) {
                                                            foreach ($tasks as $index => $task) {
                                                                echo "<tr>";
                                                                echo "<td>" . htmlspecialchars($index + 1) . "</td>";
                                                                echo "<td>" . htmlspecialchars($task['task_title']) . "</td>";
                                                                echo "<td>" . htmlspecialchars(date('d-M-Y', strtotime($task['assigned_at']))) . "</td>";

                                                                if ($task['user_role_id'] == 1) {
                                                                    echo "<td>" . htmlspecialchars($task['assigned_to_name']) . "</td>";
                                                                } else {
                                                                    echo "<td>" . htmlspecialchars($task['assigned_by_name']) . "</td>";
                                                                }

                                                                echo "<td>" . htmlspecialchars($task['remarks']) . "</td>";
                                                                echo "<td>" . htmlspecialchars($task['status']) . "</td>";
                                                                echo "</tr>";
                                                            }
                                                        } else {
                                                            echo "<tr><td colspan='6'>No Task Assignments Found</td></tr>";
                                                        }
                                                        ?>
                                                    </tbody>
                                                </table>


                                                <hr>
                                                <table class="table table-sm table-centered table-hover table-borderless mb-0">
                                                    <br>
                                                    <br>
                                                    <h5 class="text-uppercase mb-3"><i class="ri-briefcase-line me-1"></i>
                                                        Attendance History</h5>

                                                    <thead class="border-top border-bottom bg-light-subtle border-light">
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Date</th>
                                                            <th>Check-In</th>
                                                            <th>Check-Out</th>
                                                            <th>Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        // Check if there are attendance records to display
                                                        if (!empty($attendance)) {
                                                            foreach ($attendance as $index => $record) {
                                                                echo "<tr>";
                                                                echo "<td>" . htmlspecialchars($index + 1) . "</td>"; // Display task index
                                                                echo "<td>" . htmlspecialchars(date('d-M-Y', strtotime($record['attendance_date']))) . "</td>";
                                                                echo "<td>" . htmlspecialchars($record['check_in']) . "</td>";
                                                                echo "<td>" . htmlspecialchars($record['check_out']) . "</td>";
                                                                echo "<td>" . htmlspecialchars($record['attendance_status']) . "</td>";
                                                                echo "</tr>";
                                                            }
                                                        } else {
                                                            echo "<tr><td colspan='4'>No Attendance Records Found</td></tr>";
                                                        }
                                                        ?>
                                                    </tbody>
                                                </table>



                                            </div>

                                            <!-- <h5 class="text-uppercase mt-4"><i class="ri-macbook-line me-1"></i>
>>>>>>> kiran
                                                Experience</h5>

                                            <div class="timeline-alt pb-0">
                                                <div class="timeline-item">
                                                    <i class="ri-record-circle-line text-bg-info timeline-icon"></i>
                                                    <div class="timeline-item-info">
                                                        <h5 class="mt-0 mb-1">Lead designer / Developer</h5>
                                                        <p class="fs-14">websitename.com <span class="ms-2 fs-12">Year: 2015 - 18</span></p>
                                                        <p class="text-muted mt-2 mb-0 pb-3">Everyone realizes why a new common language
                                                            would be desirable: one could refuse to pay expensive translators.
                                                            To achieve this, it would be necessary to have uniform grammar,
                                                            pronunciation and more common words.</p>
                                                    </div>
                                                </div>

                                                <div class="timeline-item">
                                                    <i class="ri-record-circle-line text-bg-primary timeline-icon"></i>
                                                    <div class="timeline-item-info">
                                                        <h5 class="mt-0 mb-1">Senior Graphic Designer</h5>
                                                        <p class="fs-14">Software Inc. <span class="ms-2 fs-12">Year: 2012 - 15</span></p>
                                                        <p class="text-muted mt-2 mb-0 pb-3">If several languages coalesce, the grammar
                                                            of the resulting language is more simple and regular than that of
                                                            the individual languages. The new common language will be more
                                                            simple and regular than the existing European languages.</p>

                                                    </div>
                                                </div>

                                                <div class="timeline-item">
                                                    <i class="ri-record-circle-line text-bg-info timeline-icon"></i>
                                                    <div class="timeline-item-info">
                                                        <h5 class="mt-0 mb-1">Graphic Designer</h5>
                                                        <p class="fs-14">Coderthemes Design LLP <span class="ms-2 fs-12">Year: 2010 - 12</span></p>
                                                        <p class="text-muted mt-2 mb-0 pb-2">The European languages are members of
                                                            the same family. Their separate existence is a myth. For science
                                                            music sport etc, Europe uses the same vocabulary. The languages
                                                            only differ in their grammar their pronunciation.</p>
                                                    </div>
                                                </div>

<<<<<<< HEAD
                                            </div>
=======
                                            </div> -->
>>>>>>> kiran
                                            <!-- end timeline -->

                                        </div> <!-- end tab-pane -->
                                        <!-- end about me section content -->

<<<<<<< HEAD
=======




>>>>>>> kiran
                                        <div class="tab-pane" id="timeline">

                                            <!-- comment box -->
                                            <div class="border rounded mt-2 mb-3">
                                                <form action="#" class="comment-area-box">
                                                    <textarea rows="3" class="form-control border-0 resize-none" placeholder="Write something...."></textarea>
                                                    <div class="p-2 bg-light d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <a href="#" class="btn btn-sm px-2 fs-16 btn-light"><i class="ri-contacts-book-2-line"></i></a>
                                                            <a href="#" class="btn btn-sm px-2 fs-16 btn-light"><i class="ri-map-pin-line"></i></a>
                                                            <a href="#" class="btn btn-sm px-2 fs-16 btn-light"><i class="ri-camera-3-line"></i></a>
                                                            <a href="#" class="btn btn-sm px-2 fs-16 btn-light"><i class="ri-emoji-sticker-line"></i></a>
                                                        </div>
                                                        <button type="submit" class="btn btn-sm btn-dark">Post</button>
                                                    </div>
                                                </form>
<<<<<<< HEAD
                                            </div> <!-- end .border-->
=======
                                            </div>
                                            <!-- end .border-->
>>>>>>> kiran
                                            <!-- end comment box -->

                                            <!-- Story Box-->
                                            <div class="border border-light rounded p-2 mb-3">
                                                <div class="d-flex">
                                                    <img class="me-2 rounded-circle" src="assets/images/users/avatar-4.jpg" alt="Generic placeholder image" height="32">
                                                    <div>
                                                        <h5 class="m-0">Thelma Fridley</h5>
                                                        <p class="text-muted"><small>about 1 hour ago</small></p>
                                                    </div>
                                                </div>
                                                <div class="fs-16 text-center fst-italic text-dark">
                                                    <i class="ri-double-quotes-l fs-20"></i> Cras sit amet nibh libero, in
                                                    gravida nulla. Nulla vel metus scelerisque ante sollicitudin. Cras
                                                    purus odio, vestibulum in vulputate at, tempus viverra turpis. Duis
                                                    sagittis ipsum. Praesent mauris. Fusce nec tellus sed augue semper
                                                    porta. Mauris massa.
                                                </div>

                                                <div class="mx-n2 p-2 mt-3 bg-light">
                                                    <div class="d-flex">
                                                        <img class="me-2 rounded-circle" src="assets/images/users/avatar-3.jpg" alt="Generic placeholder image" height="32">
                                                        <div>
                                                            <h5 class="mt-0">Jeremy Tomlinson <small class="text-muted">about 2 minuts ago</small></h5>
                                                            Nice work, makes me think of The Money Pit.

                                                            <br />
                                                            <a href="javascript: void(0);" class="text-muted fs-13 d-inline-block mt-2"><i class="ri-reply-line"></i> Reply</a>

                                                            <div class="d-flex mt-3">
                                                                <a class="pe-2" href="#">
                                                                    <img src="assets/images/users/avatar-4.jpg" class="rounded-circle" alt="Generic placeholder image" height="32">
                                                                </a>
                                                                <div>
                                                                    <h5 class="mt-0">Thelma Fridley <small class="text-muted">5 hours ago</small></h5>
                                                                    i'm in the middle of a timelapse animation myself! (Very different though.) Awesome stuff.
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="d-flex mt-2">
                                                        <a class="pe-2" href="#">
                                                            <img src="assets/images/users/avatar-1.jpg" class="rounded-circle" alt="Generic placeholder image" height="32">
                                                        </a>
                                                        <div class="w-100">
                                                            <input type="text" id="simpleinput" class="form-control border-0 form-control-sm" placeholder="Add comment">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="mt-2">
                                                    <a href="javascript: void(0);" class="btn btn-sm btn-link text-danger"><i class="ri-heart-line"></i> Like (28)</a>
                                                    <a href="javascript: void(0);" class="btn btn-sm btn-link text-muted"><i class="ri-share-line"></i> Share</a>
                                                </div>
                                            </div>

                                            <!-- Story Box-->
                                            <div class="border border-light rounded p-2 mb-3">
                                                <div class="d-flex">
                                                    <img class="me-2 rounded-circle" src="assets/images/users/avatar-3.jpg" alt="Generic placeholder image" height="32">
                                                    <div>
                                                        <h5 class="m-0">Jeremy Tomlinson</h5>
                                                        <p class="text-muted"><small>3 hours ago</small></p>
                                                    </div>
                                                </div>
                                                <p>Story based around the idea of time lapse, animation to post soon!</p>

                                                <img src="assets/images/small/small-1.jpg" alt="post-img" class="rounded me-1" height="60" />
                                                <img src="assets/images/small/small-2.jpg" alt="post-img" class="rounded me-1" height="60" />
                                                <img src="assets/images/small/small-3.jpg" alt="post-img" class="rounded" height="60" />

                                                <div class="mt-2">
                                                    <a href="javascript: void(0);" class="btn btn-sm btn-link text-muted"><i class="ri-reply-line"></i> Reply</a>
                                                    <a href="javascript: void(0);" class="btn btn-sm btn-link text-muted"><i class="ri-heart-line"></i> Like</a>
                                                    <a href="javascript: void(0);" class="btn btn-sm btn-link text-muted"><i class="ri-share-line"></i> Share</a>
                                                </div>
                                            </div>

                                            <!-- Story Box-->
                                            <div class="border border-light p-2 mb-3">
                                                <div class="d-flex">
                                                    <img class="me-2 rounded-circle" src="assets/images/users/avatar-6.jpg" alt="Generic placeholder image" height="32">
                                                    <div>
                                                        <h5 class="m-0">Martin Williamson</h5>
                                                        <p class="text-muted"><small>15 hours ago</small></p>
                                                    </div>
                                                </div>
                                                <p>The parallax is a little odd but O.o that house build is awesome!!</p>

                                                <iframe src='https://player.vimeo.com/video/87993762' height='300' class="img-fluid border-0"></iframe>
                                            </div>

                                            <div class="text-center">
                                                <a href="javascript:void(0);" class="text-danger"><i class="ri-loader-fill me-1"></i> Load more </a>
                                            </div>

                                        </div>
                                        <!-- end timeline content-->

                                        <div class="tab-pane" id="settings">
<<<<<<< HEAD
                                            <form>
                                                <h5 class="mb-4 text-uppercase"><i class="ri-contacts-book-2-line me-1"></i> Personal Info</h5>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label for="firstname" class="form-label">First Name</label>
                                                            <input type="text" class="form-control" id="firstname" placeholder="Enter first name">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label for="lastname" class="form-label">Last Name</label>
                                                            <input type="text" class="form-control" id="lastname" placeholder="Enter last name">
                                                        </div>
                                                    </div> <!-- end col -->
                                                </div> <!-- end row -->

                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="mb-3">
                                                            <label for="userbio" class="form-label">Bio</label>
                                                            <textarea class="form-control" id="userbio" rows="4" placeholder="Write something..."></textarea>
                                                        </div>
                                                    </div> <!-- end col -->
                                                </div> <!-- end row -->

                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label for="useremail" class="form-label">Email Address</label>
                                                            <input type="email" class="form-control" id="useremail" placeholder="Enter email">
                                                            <span class="form-text text-muted"><small>If you want to change email please <a href="javascript: void(0);">click</a> here.</small></span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label for="userpassword" class="form-label">Password</label>
                                                            <input type="password" class="form-control" id="userpassword" placeholder="Enter password">
                                                            <span class="form-text text-muted"><small>If you want to change password please <a href="javascript: void(0);">click</a> here.</small></span>
                                                        </div>
                                                    </div> <!-- end col -->
                                                </div> <!-- end row -->

                                                <h5 class="mb-3 text-uppercase bg-light p-2"><i class="ri-building-line me-1"></i> Company Info</h5>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label for="companyname" class="form-label">Company Name</label>
                                                            <input type="text" class="form-control" id="companyname" placeholder="Enter company name">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label for="cwebsite" class="form-label">Website</label>
                                                            <input type="text" class="form-control" id="cwebsite" placeholder="Enter website url">
                                                        </div>
                                                    </div> <!-- end col -->
                                                </div> <!-- end row -->

                                                <h5 class="mb-3 text-uppercase bg-light p-2"><i class="ri-global-line me-1"></i> Social</h5>
=======
                                            <form action="" method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
                                                <div class="row mb-3">
                                                    <h5 class="text-uppercase mb-3 mt-3"><i class="ri-briefcase-line me-1"></i>
                                                        User Information</h5>

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
                                                <div class="text-end">

                                                    <button type="submit" class="btn btn-success mt-2" id="btnUpdateUserData" name="btnUpdateUserData"><i class="ri-save-line"></i> Save</button>
                                                </div>
                                            </form>
                                        </div>


                                        <!-- <h5 class="mb-3 text-uppercase bg-light p-2"><i class="ri-global-line me-1"></i> Social</h5>
>>>>>>> kiran
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label for="social-fb" class="form-label">Facebook</label>
                                                            <div class="input-group">
                                                                <span class="input-group-text"><i class="ri-facebook-fill"></i></span>
                                                                <input type="text" class="form-control" id="social-fb" placeholder="Url">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label for="social-tw" class="form-label">Twitter</label>
                                                            <div class="input-group">
                                                                <span class="input-group-text"><i class="ri-twitter-line"></i></span>
                                                                <input type="text" class="form-control" id="social-tw" placeholder="Username">
                                                            </div>
                                                        </div>
<<<<<<< HEAD
                                                    </div> <!-- end col -->
                                                </div> <!-- end row -->
=======
                                                    </div> 
                                                </div> 
>>>>>>> kiran

                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label for="social-insta" class="form-label">Instagram</label>
                                                            <div class="input-group">
                                                                <span class="input-group-text"><i class="ri-instagram-line"></i></span>
                                                                <input type="text" class="form-control" id="social-insta" placeholder="Url">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label for="social-lin" class="form-label">Linkedin</label>
                                                            <div class="input-group">
                                                                <span class="input-group-text"><i class="ri-linkedin-fill"></i></span>
                                                                <input type="text" class="form-control" id="social-lin" placeholder="Url">
                                                            </div>
                                                        </div>
<<<<<<< HEAD
                                                    </div> <!-- end col -->
                                                </div> <!-- end row -->
=======
                                                    </div> 
                                                </div> 
>>>>>>> kiran

                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label for="social-sky" class="form-label">Skype</label>
                                                            <div class="input-group">
                                                                <span class="input-group-text"><i class="ri-skype-line"></i></span>
                                                                <input type="text" class="form-control" id="social-sky" placeholder="@username">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label for="social-gh" class="form-label">Github</label>
                                                            <div class="input-group">
                                                                <span class="input-group-text"><i class="ri-github-line"></i></span>
                                                                <input type="text" class="form-control" id="social-gh" placeholder="Username">
                                                            </div>
                                                        </div>
<<<<<<< HEAD
                                                    </div> <!-- end col -->
                                                </div> <!-- end row -->

                                                <div class="text-end">
                                                    <button type="submit" class="btn btn-success mt-2"><i class="ri-save-line"></i> Save</button>
                                                </div>
                                            </form>
                                        </div>
=======
                                                    </div> 
                                                </div>  

                                                <div class="text-end">

                                                    <button type="submit" class="btn btn-success mt-2" id="btnUpdateUserData" name="btnUpdateUserData"><i class="ri-save-line"></i> Save</button>
                                                </div>
                                            </form>
                                        </div>-->
>>>>>>> kiran
                                        <!-- end settings content-->

                                    </div> <!-- end tab-content -->
                                </div> <!-- end card body -->
                            </div> <!-- end card -->
                        </div> <!-- end col -->
                    </div>
                    <!-- end row-->

                </div>
<<<<<<< HEAD
                <!-- container -->

            </div>
            <!-- content -->

            <?php include 'layouts/footer.php'; ?>

        </div>

        <!-- ============================================================== -->
        <!-- End Page content -->
        <!-- ============================================================== -->
=======
            </div> <!-- container -->

            <?php include 'layouts/footer.php'; ?>
        </div>
        <!-- content -->


    </div>

    <!-- ============================================================== -->
    <!-- End Page content -->
    <!-- ============================================================== -->
>>>>>>> kiran

    </div>
    <!-- END wrapper -->

    <?php include 'layouts/right-sidebar.php'; ?>

    <?php include 'layouts/footer-scripts.php'; ?>

    <!-- Chart.js -->
    <script src="assets/vendor/chart.js/chart.min.js"></script>

    <!-- Profile Demo App js -->
    <script src="assets/js/pages/demo.profile.js"></script>

    <!-- App js -->
    <script src="assets/js/app.min.js"></script>

<<<<<<< HEAD
=======

>>>>>>> kiran
</body>

</html>