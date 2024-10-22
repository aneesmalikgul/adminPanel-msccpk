<?php
include 'layouts/session.php';
include 'layouts/main.php';
include 'layouts/config.php';
include 'layouts/functions.php';

// Verify Database Connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Projects | Mohsin Shaheen Construction Company</title>
    <?php include 'layouts/title-meta.php'; ?>
    <?php include 'layouts/head-css.php'; ?>
    <style></style>

</head>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btnSaveProject'])) {
    // Collect form data
    $projectName = mysqli_real_escape_string($conn, $_POST['projectName']);
    $projectCategory = mysqli_real_escape_string($conn, $_POST['projectCategory']);
    $clientName = mysqli_real_escape_string($conn, $_POST['clientName']);
    $projectStartDate = mysqli_real_escape_string($conn, $_POST['projectStartDate']);
    $projectEndingDate = mysqli_real_escape_string($conn, $_POST['projectEndingDate']);
    $projectDescription = mysqli_real_escape_string($conn, $_POST['projectDescription']);
    $createdBy = $_SESSION['username'];
    $createdAt = date('Y-m-d H:i:s');

    $targetDir = "assets/images/project_images/";
    $uploadOk = 1;
    $imagePaths = [
        'frontImage' => null,
        'referenceImage1' => null,
        'referenceImage2' => null
    ];

    // Check if directory exists, if not create it
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    // Function to handle image upload
    function handleImageUpload($inputName, $targetDir, &$imagePaths, &$uploadOk)
    {
        $imageFile = $_FILES[$inputName]['name'];
        $targetFile = $targetDir . basename($imageFile);
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        // Check if image file is an actual image or fake image
        $check = getimagesize($_FILES[$inputName]["tmp_name"]);
        if ($check !== false) {
            $uploadOk = 1;
        } else {
            $_SESSION['message'][] = array("type" => "error", "content" => "File is not an image.");
            $uploadOk = 0;
        }

        // Allow certain file formats
        if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            $_SESSION['message'][] = array("type" => "error", "content" => "Sorry, only JPG, JPEG, PNG & GIF files are allowed.");
            $uploadOk = 0;
        }

        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {
            $_SESSION['message'][] = array("type" => "error", "content" => "Sorry, your file was not uploaded.");
        } else {
            // If everything is ok, try to upload file
            if (move_uploaded_file($_FILES[$inputName]["tmp_name"], $targetFile)) {
                $imagePaths[$inputName] = $targetFile;
            } else {
                $_SESSION['message'][] = array("type" => "error", "content" => "Sorry, there was an error uploading your file.");
            }
        }
    }

    // Handle front image upload (required)
    handleImageUpload('frontImage', $targetDir, $imagePaths, $uploadOk);

    // Handle optional image uploads
    if (!empty($_FILES['referenceImage1']['name'])) {
        handleImageUpload('referenceImage1', $targetDir, $imagePaths, $uploadOk);
    }

    if (!empty($_FILES['referenceImage2']['name'])) {
        handleImageUpload('referenceImage2', $targetDir, $imagePaths, $uploadOk);
    }

    // Check if front image is uploaded successfully
    if ($imagePaths['frontImage'] === null) {
        $_SESSION['message'][] = array("type" => "error", "content" => "Front image is required.");
        header("Location: projects.php");
        exit();
    }

    // Insert data into the database
    mysqli_begin_transaction($conn, MYSQLI_TRANS_START_READ_WRITE);
    $stmt = null; // Initialize $stmt variable

    try {
        $frontImagePath = $imagePaths['frontImage'];
        $referenceImagePath1 = $imagePaths['referenceImage1'] ?? null;
        $referenceImagePath2 = $imagePaths['referenceImage2'] ?? null;

        $query = "INSERT INTO projects (project_name, project_category, client_name, start_date, end_date, front_image, description, reference_image_1, reference_image_2, created_by, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);

        if ($stmt === false) {
            throw new Exception("Prepare statement failed: " . mysqli_error($conn));
        }

        if (!mysqli_stmt_bind_param($stmt, "sssssssssss", $projectName, $projectCategory, $clientName, $projectStartDate, $projectEndingDate, $frontImagePath, $projectDescription, $referenceImagePath1, $referenceImagePath2, $createdBy, $createdAt)) {
            throw new Exception("Binding parameters failed: " . mysqli_stmt_error($stmt));
        }

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Statement execution failed: " . mysqli_stmt_error($stmt));
        }

        mysqli_commit($conn);
        $_SESSION['message'][] = array("type" => "success", "content" => "Project data saved successfully!");
        header("Location: projects.php");
        exit();
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $_SESSION['message'][] = array("type" => "error", "content" => $e->getMessage());
    } finally {
        // Close statement if it's set
        // if ($stmt !== null) {
        //     mysqli_stmt_close($stmt);
        // }
        mysqli_close($conn);
        header("location: projects.php");
    }
}
?>

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
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Projects</a></li>
                                        <li class="breadcrumb-item active">Project</li>
                                    </ol>
                                </div>
                                <h4 class="page-title">Project</h4>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <?php displaySessionMessage(); ?>

                            <h2 class="text-center">Add New Project</h2>
                            <div class="card">
                                <div class="card-body">
                                    <p class="text-muted fs-14"> </p>
                                    <div class="row">
                                        <div>
                                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="needs-validation" novalidate enctype="multipart/form-data">
                                                <div class="row mb-3">
                                                    <h3>Project Information</h3>
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="projectName" class="form-label">Project Name</label>
                                                            <input type="text" id="projectName" name="projectName" class="form-control" required placeholder="Enter the name of Project">
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback">Please fill this field.</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="projectCategory" class="form-label">Project Category</label>
                                                            <input type="text" id="projectCategory" name="projectCategory" class="form-control" required placeholder="Select the Category of project.">
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback">Please fill this field.</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="clientName" class="form-label">Client Name</label>
                                                            <input type="text" id="clientName" name="clientName" class="form-control" required placeholder="Enter Unit Size of Material">
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback">Please fill this field.</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="projectStartDate" class="form-label">Project Start Date</label>
                                                            <input type="date" id="projectStartDate" name="projectStartDate" class="form-control" required placeholder="Enter name of client">
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback">Please fill this field.</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="projectEndingDate" class="form-label">Project Ending Date</label>
                                                            <input type="date" id="projectEndingDate" name="projectEndingDate" class="form-control" required placeholder="Enter name of client">
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback">Please fill this field.</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="frontImage" class="form-label">Project Front Display Image</label>
                                                            <input type="file" id="frontImage" name="frontImage" class="form-control" required>
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback">Please fill this field.</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-12">
                                                        <div class="mb-3">
                                                            <label for="projectDescription" class="form-label">Add Project Description</label>
                                                            <textarea class="form-control" name="projectDescription" id="projectDescription" rows="5" placeholder="Project Description" required></textarea>
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback">Please fill this field.</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="referenceImage1" class="form-label">Reference Image 1</label>
                                                            <input type="file" id="referenceImage1" name="referenceImage1" class="form-control">
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback">Please fill this field.</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="referenceImage2" class="form-label">Reference Image 2</label>
                                                            <input type="file" id="referenceImage2" name="referenceImage2" class="form-control">
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback">Please fill this field.</div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row mb-3">
                                                    <div class="col-lg-12 text-center">
                                                        <button type="submit" id="btnSaveProject" name="btnSaveProject" class="btn btn-primary ">Save Project</button>
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
                                    <h4 class="header-title">All Saved Projects</h4>
                                    <p class="text-muted fs-14"></p>
                                    <div class="table-responsive-sm">
                                        <table id="scroll-horizontal-datatable" class="table table-striped w-100 nowrap">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Project Name</th>
                                                    <th>Project Category</th>
                                                    <th>Client Name</th>
                                                    <th>Start Date</th>
                                                    <th>End Date</th>
                                                    <th>Front Display Image</th>
                                                    <th>Reference Image 1</th>
                                                    <th>Reference Image 2</th>
                                                    <th>Created By</th>
                                                    <th>Created At</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $query = "SELECT * FROM projects WHERE is_active = 'Y'";
                                                $result = mysqli_query($conn, $query);

                                                if ($result) {
                                                    while ($row = mysqli_fetch_assoc($result)) {
                                                        echo "<tr>";
                                                        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                                                        echo "<td>" . htmlspecialchars($row['project_name']) . "</td>";
                                                        echo "<td>" . htmlspecialchars($row['project_category']) . "</td>";
                                                        echo "<td>" . htmlspecialchars($row['client_name']) . "</td>";
                                                        echo "<td>" . htmlspecialchars(date('d-M-Y', strtotime($row['start_date']))) . "</td>";
                                                        echo "<td>" . htmlspecialchars(date('d-M-Y', strtotime($row['end_date']))) . "</td>";
                                                        // Front Display Image
                                                        echo "<td>";
                                                        if (!empty($row['front_image']) && file_exists($row['front_image'])) {
                                                            echo "<img src='" . htmlspecialchars($row['front_image']) . "' alt='Project Image' height='30px'>";
                                                        } else {
                                                            echo "No Image Uploaded.";
                                                        }
                                                        echo "</td>";
                                                        // Reference Image 1
                                                        echo "<td>";
                                                        if (!empty($row['reference_image_1']) && file_exists($row['reference_image_1'])) {
                                                            echo "<img src='" . htmlspecialchars($row['reference_image_1']) . "' alt='Project Image' height='30px'>";
                                                        } else {
                                                            echo "No Image Uploaded.";
                                                        }
                                                        echo "</td>";
                                                        // Reference Image 2
                                                        echo "<td>";
                                                        if (!empty($row['reference_image_2']) && file_exists($row['reference_image_2'])) {
                                                            echo "<img src='" . htmlspecialchars($row['reference_image_2']) . "' alt='Project Image' height='30px'>";
                                                        } else {
                                                            echo "No Image Uploaded.";
                                                        }
                                                        echo "</td>";
                                                        echo "<td>" . htmlspecialchars($row['created_by']) . "</td>";
                                                        echo "<td>" . htmlspecialchars(date('d-M-Y', strtotime($row['created_at']))) . "</td>";
                                                        echo "<td>";
                                                        // Edit button
                                                        echo "<a href='edit-project.php?id=" . urlencode($row['id']) . "' class='btn btn-warning'><i class='ri-pencil-line'></i></a>";
                                                        echo "  ";
                                                        // Delete button
                                                        echo "<a href='delete-project.php?id=" . urlencode($row['id']) . "' class='btn btn-danger' onclick='return confirmDelete()'><i class='ri-delete-bin-line'></i></a>";
                                                        echo "</td>";
                                                        echo "</tr>";
                                                    }
                                                }
                                                // else {
                                                //     echo "<tr><td colspan='10'>No Projects Found</td></tr>";
                                                // }

                                                mysqli_close($conn);
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