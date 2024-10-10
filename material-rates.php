<?php
include 'layouts/session.php';
include 'layouts/main.php';
include 'layouts/config.php';
include 'layouts/functions.php';

if (!hasPermission('view_material_rate') || !hasPermission('manage_material_rate')) {
    header('location: index.php');
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Materials & Rates | Mohsin Shaheen Construction Company</title>
    <?php include 'layouts/title-meta.php'; ?>
    <?php include 'layouts/head-css.php'; ?>
    <style></style>
    <script>
        function confirmDelete() {
            return confirm("Are you sure you want to delete this material?");
        }
    </script>

</head>

<?php

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["btnSaveMaterialData"])) {
    $materialName = trim($_POST['materialName']);
    $materialDescription = trim($_POST['materialDescription']);
    $unitSize = trim($_POST['unitSize']);
    $unitPrice = trim($_POST['unitPrice']);
    $createdBy = $_SESSION['username'];


    $imageFileName = "";
    $uploadDir = 'assets/uploads/material_images/';
    $targetFilePath = '';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    if (isset($_FILES['materialImage']) && $_FILES['materialImage']['error'] == 0) {
        $imageFileName = basename($_FILES['materialImage']['name']);
        $targetFilePath = $uploadDir . $imageFileName;
        move_uploaded_file($_FILES['materialImage']['tmp_name'], $targetFilePath);
    }

    try {
        $sql = "INSERT INTO material_rates (material_name, description, unit_size, unit_price, material_image, created_by, is_active)
                VALUES ('$materialName', '$materialDescription', '$unitSize', '$unitPrice', '$targetFilePath', '$createdBy', 'Y')";

        if (mysqli_query($conn, $sql)) {
            $_SESSION['message'][] = array("type" => "success", "content" => "Material Data saved successfully!");
        } else {
            throw new Exception("Failed to save the data. " . mysqli_error($conn));
        }
    } catch (Exception $e) {
        $_SESSION['message'][] = array("type" => "error", "content" => "Error: " . $e->getMessage());
    } finally {
        mysqli_close($conn);
        header("location: material-rates.php");
        exit(); // Ensure script execution stops after header redirection
    }
}


// Debugging function to log messages
// function logMessage($message)
// {
//     error_log($message, 3, 'debug.log'); // Change 'debug.log' to the desired log file path
// }

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["btnUpdateMaterialData"])) {
    $materialId = trim($_POST['materialId']);
    $materialName = trim($_POST['materialName']);
    $materialDescription = trim($_POST['materialDescription']);
    $unitSize = trim($_POST['unitSize']);
    $unitPrice = trim($_POST['unitPrice']);
    $updatedBy = $_SESSION['username'];


    // Verify that $materialId is not empty
    if (empty($materialId)) {
        $_SESSION['message'][] = array("type" => "error", "content" => "Material ID is empty.");
        header("location: material-rates.php");
        exit(); // Stop further execution
    }

    $imageFileName = "";
    $uploadDir = 'assets/uploads/material_images/';
    $targetFilePath = '';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    if (isset($_FILES['materialImage']) && $_FILES['materialImage']['error'] == 0) {
        $imageFileName = basename($_FILES['materialImage']['name']);
        $targetFilePath = $uploadDir . $imageFileName;
        move_uploaded_file($_FILES['materialImage']['tmp_name'], $targetFilePath);

        $sql = "UPDATE material_rates SET 
                    material_name = '$materialName', 
                    description = '$materialDescription', 
                    unit_size = '$unitSize', 
                    unit_price = '$unitPrice', 
                    material_image = '$targetFilePath',  
                    updated_by = '$updatedBy' 
                WHERE id = '$materialId'";
    } else {
        $sql = "UPDATE material_rates SET 
                    material_name = '$materialName', 
                    description = '$materialDescription', 
                    unit_size = '$unitSize', 
                    unit_price = '$unitPrice',  
                    updated_by = '$updatedBy' 
                WHERE id = '$materialId'";
    }

    logMessage("SQL Query: $sql"); // Log the SQL query

    try {
        if (mysqli_query($conn, $sql)) {
            $_SESSION['message'][] = array("type" => "success", "content" => "Material Data updated successfully!");
        } else {
            $error = mysqli_error($conn);
            logMessage("SQL Error: $error"); // Log the SQL error
            throw new Exception("Failed to update the data. " . $error);
        }
    } catch (Exception $e) {
        $_SESSION['message'][] = array("type" => "error", "content" => "Error: " . $e->getMessage());
    } finally {
        mysqli_close($conn);
        header("location: material-rates.php");
        exit(); // Ensure script execution stops after header redirection
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
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Materials</a></li>
                                        <li class="breadcrumb-item active">Materials & Rates</li>
                                    </ol>
                                </div>
                                <h4 class="page-title">Materials & Rates</h4>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <?php displaySessionMessage(); ?>
                        <h2 class="text-center">Add Materials & Rates</h2>
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <p class="text-muted fs-14"> </p>
                                    <div class="row">
                                        <div>
                                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="needs-validation" novalidate enctype="multipart/form-data">
                                                <div class="row mb-3">
                                                    <h3>Material Information</h3>
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="materialName" class="form-label">Material Name</label>
                                                            <input type="text" id="materialName" name="materialName" class="form-control" required placeholder="Enter the name of Material">
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback">Please fill this field.</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="materialDescription" class="form-label">Material Description</label>
                                                            <input type="text" id="materialDescription" name="materialDescription" class="form-control" required placeholder="Add Material Description">
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback">Please fill this field.</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="unitSize" class="form-label">Unit Size</label>
                                                            <input type="text" id="unitSize" name="unitSize" class="form-control" required placeholder="Enter Unit Size of Material">
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback">Please fill this field.</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="unitPrice" class="form-label">Unit Price</label>
                                                            <input type="text" id="unitPrice" name="unitPrice" class="form-control" required placeholder="Enter Unit Price">
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback">Please fill this field.</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="materialImage" class="form-label">Material Image</label>
                                                            <input type="file" id="materialImage" name="materialImage" class="form-control">
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback">Please fill this field.</div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row mb-3">
                                                    <div class="col-lg-12 text-center">
                                                        <button type="submit" id="btnSaveMaterialData" name="btnSaveMaterialData" class="btn btn-primary ">Save Order</button>
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
                                    <h4 class="header-title">All Saved Materials</h4>
                                    <p class="text-muted fs-14"></p>
                                    <div class="table-responsive-sm">
                                        <table id="scroll-horizontal-datatable" class="table table-striped w-100 nowrap">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Material Name</th>
                                                    <th>Description</th>
                                                    <th>Unit Size</th>
                                                    <th>Unit Price</th>
                                                    <th>Material Image</th>
                                                    <th>Created By</th>
                                                    <th>Created At</th>
                                                    <?php if (hasPermission('edit_material_rate') || hasPermission('delete_material_rate')): ?>
                                                        <th>Actions</th>
                                                    <?php endif; ?>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $query = "SELECT * FROM material_rates WHERE is_active = 'Y'";
                                                $result = mysqli_query($conn, $query);

                                                if ($result) {
                                                    while ($row = mysqli_fetch_assoc($result)) {
                                                        echo "<tr>";
                                                        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                                                        echo "<td>" . htmlspecialchars($row['material_name']) . "</td>";
                                                        echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                                                        echo "<td>" . htmlspecialchars($row['unit_size']) . "</td>";
                                                        echo "<td>" . htmlspecialchars($row['unit_price']) . "</td>";
                                                        echo "<td>";
                                                        if (!empty($row['material_image']) && file_exists($row['material_image'])) {
                                                            echo "<img src='" . htmlspecialchars($row['material_image']) . "' alt='Material Image' height='30px'>";
                                                        } else {
                                                            echo "No Image Uploaded.";
                                                        }
                                                        echo "</td>";
                                                        echo "<td>" . htmlspecialchars($row['created_by']) . "</td>";
                                                        echo "<td>" . htmlspecialchars(date('d-M-Y', strtotime($row['created_at']))) . "</td>";
                                                        if (hasPermission('edit_material_rate') || hasPermission('delete_material_rate')) {
                                                            echo "<td>";
                                                            if (hasPermission('edit_material_rate')) {
                                                                // Edit button
                                                                echo "<a href='edit-material-rates.php?id=" . urlencode($row['id']) . "' class='btn btn-warning'><i class='ri-pencil-line'></i></a>";
                                                                echo "  ";
                                                            }
                                                            if (hasPermission('delete_material_rate')) {
                                                                // Delete button
                                                                echo "<a href='delete-material-rate.php?id=" . urlencode($row['id']) . "' class='btn btn-danger' onclick='return confirmDelete()'><i class='ri-delete-bin-line'></i></a>";
                                                            }
                                                            echo "</td>";
                                                        }
                                                        echo "</tr>";
                                                    }
                                                } else {
                                                    echo "<tr><td colspan='9'>No Materials Found</td></tr>";
                                                }

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