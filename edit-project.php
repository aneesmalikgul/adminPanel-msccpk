<?php
include 'layouts/session.php';
include 'layouts/main.php';
include 'layouts/config.php';
include 'layouts/functions.php';

// Check if ID is present in the URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['message'][] = array("type" => "error", "content" => "ID not found.");
    header("Location: projects.php");
    exit();
}

$id = mysqli_real_escape_string($conn, $_GET['id']);
$project = null;
try {
    // Fetch project details
    $query = "SELECT * FROM projects WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    mysqli_stmt_bind_result($stmt, $id, $project_name, $project_category, $client_name, $start_date, $end_date, $projectDescription, $front_image,  $reference_image_1, $reference_image_2, $created_by, $created_at, $updated_by, $updated_at, $is_active);
    if (mysqli_stmt_fetch($stmt)) {
        $project = array(
            'id' => $id,
            'project_name' => $project_name,
            'project_category' => $project_category,
            'client_name' => $client_name,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'description' => $projectDescription,
            'frontImage' => $front_image,
            'referenceImage1' => $reference_image_1,
            'referenceImage2' => $reference_image_2,
            'created_by' => $created_by,
            'created_at' => $created_at,
            'updated_by' => $updated_by,
            'updated_at' => $updated_at,
            'is_active' => $is_active
        );
    } else {
        $_SESSION['message'][] = array("type" => "error", "content" => "Record not found.");
        header("Location: projects.php");
        exit();
    }

    mysqli_stmt_close($stmt);
} catch (Exception $e) {
    $_SESSION['message'][] = array("type" => "error", "content" => $e->getMessage());
    header("Location: projects.php");
    exit();
}

// Handle form submission for updating the project
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btnUpdateProject'])) {
    $projectName = mysqli_real_escape_string($conn, $_POST['projectName']);
    $projectCategory = mysqli_real_escape_string($conn, $_POST['projectCategory']);
    $clientName = mysqli_real_escape_string($conn, $_POST['clientName']);
    $projectStartDate = mysqli_real_escape_string($conn, $_POST['projectStartDate']);
    $projectEndingDate = mysqli_real_escape_string($conn, $_POST['projectEndingDate']);
    $projectDescription = mysqli_real_escape_string($conn, $_POST['projectDescription']);
    $updatedBy = $_SESSION['username'];
    $updatedAt = date('Y-m-d H:i:s');

    $targetDir = "assets/images/project_images/";
    $imageFields = [
        'frontImage' => $project['frontImage'],
        'referenceImage1' => $project['referenceImage1'],
        'referenceImage2' => $project['referenceImage2']
    ];

    // Check if directory exists, if not create it
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    // Function to handle image upload
    function handleImageUpload($inputName, $targetDir, &$imageFields, &$uploadOk)
    {
        if (!empty($_FILES[$inputName]['name'])) {
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
                return;
            }

            // Allow certain file formats
            if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
                $_SESSION['message'][] = array("type" => "error", "content" => "Sorry, only JPG, JPEG, PNG & GIF files are allowed.");
                $uploadOk = 0;
                return;
            }

            // Check if $uploadOk is set to 0 by an error
            if ($uploadOk == 0) {
                $_SESSION['message'][] = array("type" => "error", "content" => "Sorry, your file was not uploaded.");
            } else {
                // If everything is ok, try to upload file
                if (move_uploaded_file($_FILES[$inputName]["tmp_name"], $targetFile)) {
                    $imageFields[$inputName] = $targetFile;
                } else {
                    $_SESSION['message'][] = array("type" => "error", "content" => "Sorry, there was an error uploading your file.");
                }
            }
        }
    }

    // Handle image uploads
    handleImageUpload('frontImage', $targetDir, $imageFields, $uploadOk);
    handleImageUpload('referenceImage1', $targetDir, $imageFields, $uploadOk);
    handleImageUpload('referenceImage2', $targetDir, $imageFields, $uploadOk);

    // Check if front image is uploaded successfully
    if ($imageFields['frontImage'] === null) {
        $_SESSION['message'][] = array("type" => "error", "content" => "Front image is required.");
        header("Location: edit_project.php?id=" . $id);
        exit();
    }

    // Update data in the database
    mysqli_begin_transaction($conn, MYSQLI_TRANS_START_READ_WRITE);
    try {
        $query = "UPDATE projects SET project_name = ?, project_category = ?, client_name = ?, start_date = ?, end_date = ?, front_image = ?, description = ?, reference_image_1 = ?, reference_image_2 = ?, updated_by = ?, updated_at = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "sssssssssssi", $projectName, $projectCategory, $clientName, $projectStartDate, $projectEndingDate, $imageFields['frontImage'], $projectDescription, $imageFields['referenceImage1'], $imageFields['referenceImage2'], $updatedBy, $updatedAt, $id);

        if (mysqli_stmt_execute($stmt)) {
            mysqli_commit($conn);
            $_SESSION['message'][] = array("type" => "success", "content" => "Project updated successfully!");
            header("Location: projects.php");
            exit();
        } else {
            throw new Exception("Database update failed: " . mysqli_error($conn));
        }
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $_SESSION['message'][] = array("type" => "error", "content" => $e->getMessage());
    } finally {
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        header("Location: edit_project.php?id=" . $id);
        exit();
    }
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
                                        <li class="breadcrumb-item active">Edit Project</li>
                                    </ol>
                                </div>
                                <h4 class="page-title">Edit Project</h4>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <h2 class="text-center">Edit Project</h2>
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <p class="text-muted fs-14"> </p>
                                    <div class="row">
                                        <div>
                                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . $id; ?>" method="post" class="needs-validation" novalidate enctype="multipart/form-data">
                                                <div class="row mb-3">
                                                    <h3>Project Information</h3>
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="projectName" class="form-label">Project Name</label>
                                                            <input type="text" id="projectName" name="projectName" class="form-control" value="<?php echo htmlspecialchars($project['project_name']); ?>" required placeholder="Enter the name of Project">
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback">Please fill this field.</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="projectCategory" class="form-label">Project Category</label>
                                                            <input type="text" id="projectCategory" name="projectCategory" class="form-control" value="<?php echo htmlspecialchars($project['project_category']); ?>" required placeholder="Select the Category of project.">
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback">Please fill this field.</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="clientName" class="form-label">Client Name</label>
                                                            <input type="text" id="clientName" name="clientName" class="form-control" value="<?php echo htmlspecialchars($project['client_name']); ?>" required placeholder="Enter Unit Size of Material">
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback">Please fill this field.</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="projectStartDate" class="form-label">Project Start Date</label>
                                                            <input type="date" id="projectStartDate" name="projectStartDate" class="form-control" value="<?php echo htmlspecialchars($project['start_date']); ?>" required placeholder="Enter name of client">
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback">Please fill this field.</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="projectEndingDate" class="form-label">Project Ending Date</label>
                                                            <input type="date" id="projectEndingDate" name="projectEndingDate" class="form-control" value="<?php echo htmlspecialchars($project['end_date']); ?>" required placeholder="Enter name of client">
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback">Please fill this field.</div>
                                                        </div>
                                                    </div>
                                                    <!-- <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="projectImage" class="form-label">Project Image</label>
                                                            <input type="file" id="projectImage" name="projectImage" class="form-control">
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback">Please fill this field.</div>
                                                        </div>
                                                    </div> -->
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="frontImage" class="form-label">Project Image</label>

                                                            <input type="file" id="frontImage" name="frontImage" class="form-control" onchange="previewImage('frontImage', 'frontImagePreview')">
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback">Please fill this field.</div>
                                                            <br>
                                                            <?php if (!empty($project['frontImage'])) : ?>
                                                                <img id="frontImagePreview" src="<?php echo htmlspecialchars($project['frontImage']); ?>" alt="Project Image" style="max-width: 100px; display: block; margin-bottom: 10px;">
                                                            <?php else : ?>
                                                                <img id="frontImagePreview" src="#" alt="Project Image" style="max-width: 100px; display: none; margin-bottom: 10px;">
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>

                                                    <div class="col-lg-12">
                                                        <div class="mb-3">
                                                            <label for="projectDescription" class="form-label">Project Description</label>
                                                            <textarea name="projectDescription" id="projectDescription" class="form-control" placeholder="Add Project Description"><?php echo htmlspecialchars($project['description']); ?></textarea>
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback">Please fill this field.</div>
                                                        </div>
                                                    </div>

                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="referenceImage1" class="form-label">Project Image</label>

                                                            <input type="file" id="referenceImage1" name="referenceImage1" class="form-control" onchange="previewImage('referenceImage1', 'referenceImage1Preview')">
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback">Please fill this field.</div>
                                                            <br>
                                                            <?php if (!empty($project['referenceImage1'])) : ?>
                                                                <img id="referenceImage1Preview" src="<?php echo htmlspecialchars($project['referenceImage1']); ?>" alt="Project Image" style="max-width: 100px; display: block; margin-bottom: 10px;">
                                                            <?php else : ?>
                                                                <img id="referenceImage1Preview" src="#" alt="Project Image" style="max-width: 100px; display: none; margin-bottom: 10px;">
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>

                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="referenceImage2" class="form-label">Project Image</label>

                                                            <input type="file" id="referenceImage2" name="referenceImage2" class="form-control" onchange="previewImage('referenceImage2', 'referenceImage2Preview')">
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback">Please fill this field.</div>
                                                            <br>
                                                            <?php if (!empty($project['referenceImage2'])) : ?>
                                                                <img id="referenceImage2Preview" src="<?php echo htmlspecialchars($project['referenceImage2']); ?>" alt="Project Image" style="max-width: 100px; display: block; margin-bottom: 10px;">
                                                            <?php else : ?>
                                                                <img id="referenceImage2Preview" src="#" alt="Project Image" style="max-width: 100px; display: none; margin-bottom: 10px;">
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row mb-3">
                                                    <div class="col-lg-12 text-center">
                                                        <button type="submit" id="btnUpdateProject" name="btnUpdateProject" class="btn btn-primary ">Update Order</button>
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

        function previewImage(inputId, previewId) {
            var file = document.getElementById(inputId).files[0];
            var reader = new FileReader();

            reader.onloadend = function() {
                document.getElementById(previewId).src = reader.result;
                document.getElementById(previewId).style.display = 'block';
            }

            if (file) {
                reader.readAsDataURL(file);
            } else {
                document.getElementById(previewId).src = "";
                document.getElementById(previewId).style.display = 'none';
            }
        }
    </script>


</body>

</html>