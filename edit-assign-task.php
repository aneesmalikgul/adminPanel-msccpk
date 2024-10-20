<?php
include 'layouts/session.php';
include 'layouts/config.php';
include 'layouts/functions.php';
include 'layouts/main.php';

// Check permissions
if (!hasPermission('edit_tasks') || !hasPermission('view_tasks') || !hasPermission('assign_tasks')) {
    header('Location: index.php');
    exit();
}

// Fetch task assignment details 
$taskAssignmentId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$taskAssignment = null;

try {
    if ($taskAssignmentId > 0) {
        // Start the transaction
        $conn->begin_transaction();

        // Query to fetch task assignment details
        $query = "
    SELECT ta.id, ta.task_id, ta.assigned_to, ta.assigned_by, ta.assigned_at, ta.status, ta.remarks,
           t.task_title, u.username AS assigned_to_username, ua.username AS assigned_by_username
    FROM task_assignments ta
    LEFT JOIN tasks t ON t.id = ta.task_id
    LEFT JOIN users u ON u.id = ta.assigned_to
    LEFT JOIN users ua ON ua.id = ta.assigned_by
    WHERE ta.id = ?
";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }

        // Bind the taskAssignmentId
        $stmt->bind_param("i", $taskAssignmentId);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if the result is found
        if ($result && $result->num_rows > 0) {
            $taskAssignment = $result->fetch_assoc();
            // Commit the transaction only if successful
            $conn->commit();
        } else {
            throw new Exception('No task assignment record found.');
        }
    } else {
        throw new Exception('Invalid task assignment ID.');
    }
} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    error_log($e->getMessage()); // Log the error for debugging
    $_SESSION['message'] = ['type' => 'error', 'text' => $e->getMessage()];
    header('Location: assign-tasks.php'); // Redirect on error
    exit;
}

// Fetch tasks and users for dropdowns
$tasks = [];
$assignTo = [];
$assignBy = [];

try {
    // Start the transaction
    $conn->begin_transaction();

    // Query to fetch tasks
    $queryTasks = "SELECT id, task_title FROM tasks ORDER BY id ASC;";
    $stmtTasks = $conn->prepare($queryTasks);
    $stmtTasks->execute();
    $resultTasks = $stmtTasks->get_result();
    while ($row = $resultTasks->fetch_assoc()) {
        $tasks[] = $row;
    }

    // Query to fetch workers for assignment
    $queryAssignTo = "SELECT id, username FROM users WHERE role_id = 2 OR role_id = 3 ORDER BY id ASC;";
    $stmtAssignTo = $conn->prepare($queryAssignTo);
    $stmtAssignTo->execute();
    $resultAssignTo = $stmtAssignTo->get_result();
    while ($row = $resultAssignTo->fetch_assoc()) {
        $assignTo[] = $row;
    }

    // Query to fetch admins for assigning tasks
    $queryAssignBy = "SELECT id, username FROM users WHERE role_id = 1 ORDER BY id ASC;";
    $stmtAssignBy = $conn->prepare($queryAssignBy);
    $stmtAssignBy->execute();
    $resultAssignBy = $stmtAssignBy->get_result();
    while ($row = $resultAssignBy->fetch_assoc()) {
        $assignBy[] = $row;
    }

    // Commit the transaction
    $conn->commit();
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['message'] = ['type' => 'error', 'text' => $e->getMessage()];
}

// Close statement objects
$stmtTasks->close();
$stmtAssignTo->close();
$stmtAssignBy->close();
?>

<head>
    <title>Edit Task Assignment | Task Management System</title>
    <?php include 'layouts/title-meta.php'; ?>
    <link href="assets/vendor/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
    <?php include 'layouts/head-css.php'; ?>
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
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Edit Task Management</a></li>
                                        <li class="breadcrumb-item active"> Edit Assign Task</li>
                                    </ol>
                                </div>
                                <h4 class="page-title">Edit Assign Task</h4>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <?php displaySessionMessage(); ?>
                            <h2 class="text-center">Edit Assign Task</h2>
                            <div class="card">
                                <div class="card-body">
                                    <p class="text-muted fs-14"> </p>
                                    <div class="row">
                                        <div>
                                            <form action="assign-tasks.php" method="post" class="needs-validation" novalidate enctype="multipart/form-data">
                                                <input type="hidden" name="taskAssignmentId" value="<?php echo htmlspecialchars($taskAssignmentId); ?>">

                                                <div class="row mb-3">
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">

                                                            <label for="taskName" class="form-label">Task Name</label>
                                                            <select id="taskName" name="taskName" class="form-select select2" required>
                                                                <option selected disabled value="">Select Task</option>
                                                                <?php foreach ($tasks as $task): ?>
                                                                    <option value="<?php echo htmlspecialchars($task['id']); ?>" <?php echo ($taskAssignment && $task['id'] == $taskAssignment['task_id']) ? 'selected' : ''; ?>>
                                                                        <?php echo htmlspecialchars($task['task_title']); ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-lg-6">
                                                        <div class="mb-3">

                                                            <label for="assignTo" class="form-label">Assign To</label>
                                                            <select id="assignTo" name="assignTo" class="form-select select2" required>
                                                                <option selected disabled value="">Select Worker</option>
                                                                <?php foreach ($assignTo as $worker): ?>
                                                                    <option value="<?php echo htmlspecialchars($worker['id']); ?>" <?php echo ($taskAssignment && $taskAssignment['assigned_to'] == $worker['id']) ? 'selected' : ''; ?>>
                                                                        <?php echo htmlspecialchars($worker['username']); ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-lg-6">
                                                        <div class="mb-3">

                                                            <label for="assignBy" class="form-label">Assign By</label>
                                                            <select id="assignBy" name="assignBy" class="form-select select2" required>
                                                                <option selected disabled value="">Select Assign By</option>
                                                                <?php foreach ($assignBy as $admin): ?>
                                                                    <option value="<?php echo htmlspecialchars($admin['id']); ?>" <?php echo ($taskAssignment && $taskAssignment['assigned_by'] == $admin['id']) ? 'selected' : ''; ?>>
                                                                        <?php echo htmlspecialchars($admin['username']); ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-lg-6">
                                                        <div class="mb-3">

                                                            <label for="assignAt" class="form-label">Assign Date</label>
                                                            <input type="date" class="form-control" id="assignAt" name="assignAt" value="<?php echo htmlspecialchars(date('Y-m-d', strtotime($taskAssignment['assigned_at']))); ?>" required>
                                                        </div>
                                                    </div>

                                                    <div class="col-lg-6">
                                                        <div class="mb-3">

                                                            <label for="taskStatus" class="form-label">Task Status</label>
                                                            <select id="taskStatus" name="taskStatus" class="form-select select2" required>
                                                                <option selected disabled value="">Select Status</option>
                                                                <option value="Assigned" <?php echo ($taskAssignment['status'] == 'Assigned') ? 'selected' : ''; ?>>Assigned</option>
                                                                <option value="In Progress" <?php echo ($taskAssignment['status'] == 'In Progress') ? 'selected' : ''; ?>>In Progress</option>
                                                                <option value="Completed" <?php echo ($taskAssignment['status'] == 'Completed') ? 'selected' : ''; ?>>Completed</option>
                                                                <option value="On Hold" <?php echo ($taskAssignment['status'] == 'On Hold') ? 'selected' : ''; ?>>On Hold</option>
                                                                <option value="Cancelled" <?php echo ($taskAssignment['status'] == 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                                            </select>

                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">

                                                            <label for="taskRemark" class="form-label">Remark</label>
                                                            <input type="text" id="taskRemark" name="taskRemark" class="form-control"
                                                                value="<?php echo htmlspecialchars($taskAssignment['remarks']); ?>"
                                                                required placeholder="Enter remark about the task">
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback">Please fill this field.</div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="text-center">
                                                    <button type="submit" class="btn btn-primary" id="btnUpdateAssignTask" name="btnUpdateAssignTask">Update Assignment</button>
                                                    <button type="button" class="btn btn-danger" id="btnUpdateAssignTask" name="btnUpdateAssignTask" onclick="window.location.href='assign-tasks.php'">Cancel Update</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div> <!-- container-fluid -->
            </div> <!-- content -->
        </div> <!-- content-page -->
    </div> <!-- wrapper -->
    <?php include 'layouts/footer.php'; ?>
    <?php include 'layouts/footer-scripts.php'; ?>


</body>

</html>