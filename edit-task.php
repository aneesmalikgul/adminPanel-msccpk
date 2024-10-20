<?php
include 'layouts/session.php';
include 'layouts/config.php';
include 'layouts/functions.php';
include 'layouts/main.php';



// Check permissions
if (!hasPermission('view_tasks') || !hasPermission('edit_tasks')) {
    header('Location: index.php');
    exit();
}

$taskId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$taskDetails = null;

try {
    if ($taskId > 0) {
        // Fetch task details
        $conn->begin_transaction();
        $query = "SELECT id, task_title, task_description, project_id, priority, status, created_at FROM tasks WHERE id = ?";
        $stmt = $conn->prepare($query);

        if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error);

        $stmt->bind_param("i", $taskId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $taskDetails = $result->fetch_assoc();
        } else {
            throw new Exception('No task record found.');
        }
        $conn->commit();
    } else {
        throw new Exception('Invalid task ID.');
    }
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['message'] = ['type' => 'error', 'text' => $e->getMessage()];
}

// Initialize variables for projects
$projects = [];
$stmtProjects = null; // Initialize to null to avoid undefined variable warning

// Fetch projects
try {
    // Start transaction for fetching projects
    $conn->begin_transaction();

    // Query to fetch project details
    $queryProjects = "
        SELECT id, project_name 
        FROM projects 
        ORDER BY id ASC;
    ";

    $stmtProjects = $conn->prepare($queryProjects);

    if (!$stmtProjects) throw new Exception('Prepare projects failed: ' . $conn->error);

    $stmtProjects->execute();
    $resultProjects = $stmtProjects->get_result();
    while ($row = $resultProjects->fetch_assoc()) {
        $projects[] = $row;
    }
    $conn->commit();

    // Debugging output
    if (empty($projects)) {
        throw new Exception('No projects found.');
    }
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['message'] = ['type' => 'error', 'text' => $e->getMessage()];
    header('Location: add-tasks.php'); // Redirect on error
    exit;
}

// Check if $stmtProjects is defined before closing
if ($stmtProjects) {
    $stmtProjects->close();
}
?>


<head>
    <title>Edit Task | Mohsin Shaheen Construction Company</title>
    <?php include 'layouts/title-meta.php'; ?>
    <?php include 'layouts/head-css.php'; ?>
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
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Task Management</a></li>
                                        <li class="breadcrumb-item active">Edit Task</li>
                                    </ol>
                                </div>
                                <h4 class="page-title">Edit Task</h4>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <h2 class="text-center">Edit Task Details</h2>
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <form action="<?php echo htmlspecialchars("add-tasks.php?id=$taskId"); ?>" method="post" class="needs-validation" novalidate>
                                        <input type="hidden" name="taskId" value="<?php echo htmlspecialchars($taskId); ?>">
                                        <div class="row mb-3">
                                            <div class="col-lg-6">
                                                <div class="mb-3">
                                                    <label for="taskName" class="form-label">Task Name</label>
                                                    <input type="text" id="taskName" name="taskName" class="form-control" value="<?php echo htmlspecialchars($taskDetails['task_title']); ?>" required>
                                                    <div class="valid-feedback">Looks good!</div>
                                                    <div class="invalid-feedback">Please enter a task name.</div>
                                                </div>
                                            </div>

                                            <div class="col-lg-6">
                                                <div class="mb-3">
                                                    <label for="assignProject" class="form-label">Assigned Project</label>
                                                    <select id="assignProject" name="assignProject" class="form-select" required>
                                                        <option selected disabled value="">Select Project</option>
                                                        <?php foreach ($projects as $project): ?>
                                                            <option value="<?php echo htmlspecialchars($project['id']); ?>"
                                                                <?php echo ($taskDetails['project_id'] == $project['id']) ? 'selected' : ''; ?>>
                                                                <?php echo htmlspecialchars($project['project_name']); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>

                                                    <div class="valid-feedback">Looks good!</div>
                                                    <div class="invalid-feedback">Please select a project.</div>
                                                </div>
                                            </div>

                                            <div class="col-lg-6">
                                                <div class="mb-3">
                                                    <label for="taskPriority" class="form-label">Task Priority</label>
                                                    <select id="taskPriority" name="taskPriority" class="form-select" required>
                                                        <option selected disabled value="">Select Priority</option>
                                                        <option value="Low" <?php echo ($taskDetails['priority'] == 'Low') ? 'selected' : ''; ?>>Low</option>
                                                        <option value="Medium" <?php echo ($taskDetails['priority'] == 'Medium') ? 'selected' : ''; ?>>Medium</option>
                                                        <option value="High" <?php echo ($taskDetails['priority'] == 'High') ? 'selected' : ''; ?>>High</option>
                                                    </select>
                                                    <div class=" valid-feedback">Looks good!
                                                    </div>
                                                    <div class="invalid-feedback">Please select a priority.</div>
                                                </div>
                                            </div>

                                            <div class="col-lg-6">
                                                <div class="mb-3">
                                                    <label for="taskStatus" class="form-label">Task Status</label>
                                                    <select id="taskStatus" name="taskStatus" class="form-select" required>
                                                        <option selected disabled value="">Select Task Status</option>
                                                        <option value="1" <?php echo ($taskDetails['status'] == 1) ? 'selected' : ''; ?>>Active</option>
                                                        <option value="0" <?php echo ($taskDetails['status'] == 0) ? 'selected' : ''; ?>>Inactive</option>
                                                    </select>
                                                    <div class="valid-feedback">Looks good!</div>
                                                    <div class="invalid-feedback">Please select a status.</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-lg-12">
                                                <div class="mb-3">
                                                    <label for="taskDetails" class="form-label">Task Details</label>
                                                    <textarea id="taskDetails" name="taskDetails" class="form-control" rows="3" required><?php echo htmlspecialchars($taskDetails['task_description']); ?></textarea>
                                                    <div class="valid-feedback">Looks good!</div>
                                                    <div class="invalid-feedback">Please provide task details.</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-lg-12 text-center">
                                                <button type="submit" id="btnUpdateTask" name="btnUpdateTask" class="btn btn-primary">Update Task</button>
                                                <button type="button" class="btn btn-danger" id="btnUpdateAssignTask" name="btnUpdateAssignTask" onclick="window.location.href='add-tasks.php'">Cancel Update</button>

                                            </div>
                                        </div>
                                    </form>
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

    <script>
        <?php
        if (isset($_SESSION['message'])) {
            foreach ($_SESSION['message'] as $message) {
                echo "toastr." . $message['type'] . "('" . $message['text'] . "');";
            }
            unset($_SESSION['message']); // Clear messages after displaying
        }
        ?>
    </script>

</body>

</html>