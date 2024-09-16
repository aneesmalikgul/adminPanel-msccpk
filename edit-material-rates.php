<?php
include 'layouts/session.php';
include 'layouts/main.php';
include 'layouts/config.php';
include 'layouts/functions.php';

try {
    // Check if ID is provided
    if (isset($_GET['id']) && !empty($_GET['id'])) {
        $materialId = $_GET['id'];

        // Prepare and execute SQL statement using a parameterized query
        $sql = "SELECT material_name, description, unit_size, unit_price, material_image FROM material_rates WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $materialId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $materialName, $description, $unitSize, $unitPrice, $materialImage);

        if (mysqli_stmt_fetch($stmt)) {
            $materialData = array(
                'id' => $materialId,
                'material_name' => $materialName,
                'description' => $description,
                'unit_size' => $unitSize,
                'unit_price' => $unitPrice,
                'material_image' => $materialImage
            );
        } else {
            throw new Exception("Material not found.");
        }
    } else {
        throw new Exception("Material ID not provided.");
    }
} catch (Exception $e) {
    $_SESSION['message'][] = array("type" => "error", "content" => "Error: " . $e->getMessage());
    header("Location: material-rates.php");
    exit;
} finally {
    // Close the prepared statement and database connection
    if (isset($stmt)) mysqli_stmt_close($stmt);
    mysqli_close($conn);
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <title>Materials & Rates | Mohsin Shaheen Construction Company</title>
    <?php include 'layouts/title-meta.php'; ?>
    <?php include 'layouts/head-css.php'; ?>
    <style>
        .image-box {
            border: 1px solid #ced4da;
            padding: 10px;
            border-radius: 5px;
            margin-top: 20px;
            margin-bottom: 10px;
            display: inline-block;
        }

        .image-box img {
            max-width: 100%;
            height: auto;
            display: block;
        }
    </style>
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
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Materials</a></li>
                                        <li class="breadcrumb-item active">Materials & Rates</li>
                                    </ol>
                                </div>
                                <h4 class="page-title">Materials & Rates</h4>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <h2 class="text-center">Update Materials & Rates</h2>
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <p class="text-muted fs-14"> </p>
                                    <div class="row">
                                        <div>
                                            <form action="material-rates.php" method="post" class="needs-validation" novalidate enctype="multipart/form-data">
                                                <div class="row mb-3">
                                                    <h3>Material Information</h3>
                                                    <input type="hidden" name="materialId" value="<?php echo isset($materialData['id']) ? htmlspecialchars($materialData['id']) : ''; ?>">
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="materialName" class="form-label">Material Name</label>
                                                            <input type="text" id="materialName" name="materialName" class="form-control" required placeholder="Enter the name of Material" value="<?php echo isset($materialData['material_name']) ? htmlspecialchars($materialData['material_name']) : ''; ?>">
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback">Please fill this field.</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="materialDescription" class="form-label">Material Description</label>
                                                            <input type="text" id="materialDescription" name="materialDescription" class="form-control" required placeholder="Add Material Description" value="<?php echo isset($materialData['description']) ? htmlspecialchars($materialData['description']) : ''; ?>">
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback">Please fill this field.</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="unitSize" class="form-label">Unit Size</label>
                                                            <input type="text" id="unitSize" name="unitSize" class="form-control" required placeholder="Enter Unit Size of Material" value="<?php echo isset($materialData['unit_size']) ? htmlspecialchars($materialData['unit_size']) : ''; ?>">
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback">Please fill this field.</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="unitPrice" class="form-label">Unit Price</label>
                                                            <input type="text" id="unitPrice" name="unitPrice" class="form-control" required placeholder="Enter Unit Price" value="<?php echo isset($materialData['unit_price']) ? htmlspecialchars($materialData['unit_price']) : ''; ?>">
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback">Please fill this field.</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="materialImage" class="form-label">Material Image</label>
                                                            <input type="file" id="materialImage" name="materialImage" class="form-control">
                                                            <div class="image-box">
                                                                <?php if (isset($materialData['material_image']) && !empty($materialData['material_image'])) : ?>
                                                                    <img src="<?php echo htmlspecialchars($materialData['material_image']); ?>" alt="Material Image" class="img-fluid" style="height: 30px;">
                                                                <?php endif; ?>
                                                            </div>
                                                            <br>
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback">Please fill this field.</div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row mb-3">
                                                    <div class="col-lg-12 text-center">
                                                        <button type="submit" id="btnUpdateMaterialData" name="btnUpdateMaterialData" class="btn btn-primary">Update Material Data</button>
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
        $(document).ready(function() {
            <?php
            if (isset($_SESSION['message'])) {
                foreach ($_SESSION['message'] as $message) {
                    $type = $message['type'];
                    $content = $message['content'];
                    echo "toastr.$type('$content');";
                }
                unset($_SESSION['message']);
            }
            ?>
        });
    </script>
</body>

</html>