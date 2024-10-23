<?php
include 'layouts/session.php';
include 'layouts/config.php';
include 'layouts/functions.php';
include 'layouts/main.php';


// Check if the user has the necessary permissions
if (!hasPermission('view_tasks') || !hasPermission('create_tasks')) {
    header('Location: index.php');
    exit();
}

// Fetch  projects for dropdowns
$tasks = [];
$assignTo = [];
$assignBy = [];
try {
    // Start the transaction
    $conn->begin_transaction();
    // Query to fetch task
    $queryTasks = "SELECT id, task_title FROM tasks ORDER BY id ASC;";
    $stmtTasks = $conn->prepare($queryTasks);
    $stmtTasks->execute();
    $resultTasks = $stmtTasks->get_result();
    while ($row = $resultTasks->fetch_assoc()) {
        $tasks[] = $row;
    }

    // Query to fetch users worker
    $queryAssignTo = "SELECT id, username,first_name,last_name FROM users WHERE role_id = 2 OR role_id = 3  ORDER BY id ASC;";
    $stmtAssignTo = $conn->prepare($queryAssignTo);
    $stmtAssignTo->execute();
    $resultAssignTo = $stmtAssignTo->get_result();

    while ($row = $resultAssignTo->fetch_assoc()) {
        $assignTo[] = $row;
    }
    // Query to fetch users/admin
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
    // Rollback the transaction on error
    $conn->rollback();
    $_SESSION['message'] = ['type' => 'error', 'content' => $e->getMessage()];
}

// Close the statements
$stmtTasks->close();
$stmtAssignTo->close();
$stmtAssignBy->close();
// If form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btnAssignTask'])) {
    // Get POST data and sanitize
    $taskName = isset($_POST['taskName']) ? $_POST['taskName'] : '';
    $assignTo = isset($_POST['assignTo']) ? $_POST['assignTo'] : '';
    $assignworker = isset($_POST['assignBy']) ? $_POST['assignBy'] : '';
    $assignDate = isset($_POST['assignAt']) ? $_POST['assignAt'] : '';
    $taskStatus = isset($_POST['taskStatus']) ? $_POST['taskStatus'] : ''; // Check if this is being sent
    $taskRemark = isset($_POST['taskRemark']) ? $_POST['taskRemark'] : '';
    $updatedBy = $_SESSION['user_id'];
    $updateAT = date('Y-m-d H:i:s');

    // Debugging line to check taskStatus value
    if (empty($taskStatus)) {
        $_SESSION['message'][] = ['type' => 'error', 'content' => 'Task status is empty!'];
        header('Location: assign-tasks.php');
        exit();
    }

    try {
        // Start the transaction
        $conn->begin_transaction();

        // Prepare the SQL statement
        $stmt = $conn->prepare("INSERT INTO task_assignments (task_id, assigned_to, assigned_by, assigned_at, updated_at, updated_by, status, remarks) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

        // Assuming taskStatus is a string in the database
        $stmt->bind_param('iiisssss', $taskName, $assignTo, $assignworker, $assignDate, $updateAT, $updatedBy, $taskStatus, $taskRemark);

        // Execute the statement
        if ($stmt->execute()) {
            $_SESSION['message'][] = ['type' => 'success', 'content' => 'Task assigned successfully!'];
        } else {
            throw new Exception('Error inserting data: ' . $stmt->error);
        }

        // Commit the transaction
        $conn->commit();
    } catch (Exception $e) {
        // Rollback the transaction on error
        $conn->rollback();
        $_SESSION['message'][] = ['type' => 'error', 'content' => $e->getMessage()];
    }

    // Redirect to the tasks page with a message
    header('Location: assign-tasks.php');
    exit();
}
// Code handling the update operation for task assignment
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btnUpdateAssignTask'])) {
    // Get and sanitize POST data
    $taskAssignmentId = isset($_POST['taskAssignmentId']) ? intval($_POST['taskAssignmentId']) : 0;
    $taskName = isset($_POST['taskName']) ? intval($_POST['taskName']) : 0;
    $assignTo = isset($_POST['assignTo']) ? intval($_POST['assignTo']) : 0;
    $assignBy = isset($_POST['assignBy']) ? intval($_POST['assignBy']) : 0;
    $assignAt = isset($_POST['assignAt']) ? $_POST['assignAt'] : null;
    $taskStatus = isset($_POST['taskStatus']) ? $_POST['taskStatus'] : '';
    $taskRemark = isset($_POST['taskRemark']) ? htmlspecialchars($_POST['taskRemark']) : '';

    try {
        // Validate input
        if ($taskAssignmentId <= 0 || $taskName <= 0 || $assignTo <= 0 || $assignBy <= 0 || empty($assignAt) || empty($taskStatus)) {
            throw new Exception('Invalid input data.');
        }

        // Begin transaction
        $conn->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);

        // Prepare the update query
        $query = "
            UPDATE task_assignments
            SET task_id = ?, assigned_to = ?, assigned_by = ?, assigned_at = ?, status = ?, remarks = ?
            WHERE id = ?;
        ";
        $stmt = $conn->prepare($query);

        if ($stmt === false) {
            throw new Exception('Prepare statement failed: ' . $conn->error);
        }

        // Bind parameters and execute statement
        $stmt->bind_param("iiisssi", $taskName, $assignTo, $assignBy, $assignAt, $taskStatus, $taskRemark, $taskAssignmentId);
        if (!$stmt->execute()) {
            throw new Exception('Execute statement failed: ' . $stmt->error);
        }

        // Commit transaction
        $conn->commit();

        // Set success message and redirect
        $_SESSION['message'][] = ['type' => 'success', 'content' => 'Task assignment updated successfully.'];
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();

        // Set error message
        $_SESSION['message'][] = ['type' => 'error', 'content' => $e->getMessage()];
    } finally {
        // Ensure statement and connection are closed
        if (isset($stmt)) {
            $stmt->close();
        }

        // Redirect to ensure page reload
        header('Location: assign-tasks.php');
        exit();
    }
}

?>

<head>
    <title>Assign Task | Mohsin Shaheen Construction Company</title>
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
                    <!-- Display session messages here -->
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box">
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">mscc.pk</a></li>
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Task Management</a></li>
                                        <li class="breadcrumb-item active"> Assign Task</li>
                                    </ol>
                                </div>
                                <h4 class="page-title">Assign Task</h4>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <?php displaySessionMessage(); ?>
                            <h2 class="text-center">Assign New Task</h2>
                            <div class="card">
                                <div class="card-body">
                                    <p class="text-muted fs-14"> </p>
                                    <div class="row">
                                        <div>
                                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="needs-validation" novalidate enctype="multipart/form-data">
                                                <div class="row mb-3">
                                                    <h3> Task Distribution</h3>
                                                    <!-- Task Name Input -->
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="taskName" class="form-label">Task Name</label>
                                                            <select id="taskName" name="taskName" class="form-select" required>
                                                                <option selected disabled value="">Select Task</option>
                                                                <?php foreach ($tasks as $task): ?>
                                                                    <option value="<?php echo $task['id']; ?>"><?php echo $task['task_title']; ?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback">Please fill this field.</div>
                                                        </div>
                                                    </div>
                                                    <!-- Assign to Project Dropdown -->
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="assignTo" class="form-label">Assign To</label>
                                                            <select id="assignTo" name="assignTo" class="form-select" required>
                                                                <option selected disabled value="">Select Worker</option>
                                                                <?php foreach ($assignTo as $worker): ?>
                                                                    <option value="<?php echo $worker['id']; ?>"><?php echo $worker['first_name'] . ' ' . $worker['last_name']; ?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback">Please fill this field.</div>
                                                        </div>
                                                    </div>
                                                    <!-- Assign BY Project Dropdown -->
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="assignBy" class="form-label">Assign By</label>
                                                            <select id="assignBy" name="assignBy" class="form-select" required>
                                                                <option selected disabled value="">Select Assign By</option>
                                                                <?php foreach ($assignBy as $admin): ?>
                                                                    <option value="<?php echo $admin['id']; ?>"><?php echo $admin['username']; ?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback">Please fill this field.</div>
                                                        </div>
                                                    </div>

                                                    <!-- Assign Date Input -->
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="assignAt" class="form-label">Assign At</label>
                                                            <input type="date" class="form-control" id="assignAt" name="assignAt" required>
                                                        </div>
                                                    </div>

                                                    <!-- Task Status Dropdown -->
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="taskStatus" class="form-label">Task Status</label>
                                                            <select id="taskStatus" name="taskStatus" class="form-select" required>
                                                                <option selected disabled value="">Select Task Status</option>
                                                                <option value="assigned">Assigned</option>
                                                                <option value="in_Progress">In Progress</option>
                                                                <option value="completed">Completed</option>
                                                                <option value="on_Hold">On Hold</option>
                                                                <option value="cancelled">Cancelled</option>
                                                            </select>
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback">Please select a task status.</div>
                                                        </div>
                                                    </div>

                                                    <!-- Task Remark Input -->
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="taskRemark" class="form-label">Remark</label>
                                                            <input type="text" id="taskRemark" name="taskRemark" class="form-control" required placeholder="Enter remark about the task">
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback">Please fill this field.</div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Save Task Button -->
                                                <div class="row mb-3">
                                                    <div class="col-lg-12 text-center">
                                                        <button type="submit" id="btnSaveTask" name="btnAssignTask" class="btn btn-primary">Assign Task</button>
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
                                                    <th>Task Name</th>
                                                    <th>Assigned To</th>
                                                    <th>Assigned By</th>
                                                    <th>Status</th>
                                                    <th>Updated By</th>
                                                    <th>Updated At</th>
                                                    <th>Remarks</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                try {
                                                    // Start the transaction
                                                    $conn->begin_transaction();

                                                    // Define the query to fetch task assignments with task name, assigned to, assigned by, and updated by
                                                    $query = "
                SELECT ta.id, ta.status, ta.remarks, ta.updated_at,
                       t.task_title AS task_name,
                       u1.username AS assigned_to,
                       u2.username AS assigned_by,
                       u3.username AS updated_by
                FROM task_assignments ta
                LEFT JOIN tasks t ON t.id = ta.task_id
                LEFT JOIN users u1 ON u1.id = ta.assigned_to
                LEFT JOIN users u2 ON u2.id = ta.assigned_by
                LEFT JOIN users u3 ON u3.id = ta.updated_by
                ORDER BY ta.id ASC;
            ";

                                                    // Prepare the statement
                                                    $stmt = $conn->prepare($query);
                                                    if (!$stmt) {
                                                        throw new Exception('Prepare statement failed: ' . $conn->error);
                                                    }

                                                    // Execute the query
                                                    $stmt->execute();
                                                    $result = $stmt->get_result();

                                                    // Check if the query returned results
                                                    if ($result && $result->num_rows > 0) {
                                                        // Loop through the results
                                                        while ($row = $result->fetch_assoc()) {
                                                            echo "<tr>";
                                                            echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                                                            echo "<td>" . htmlspecialchars($row['task_name']) . "</td>";
                                                            echo "<td>" . htmlspecialchars($row['assigned_to']) . "</td>";
                                                            echo "<td>" . htmlspecialchars($row['assigned_by']) . "</td>";

                                                            // Convert status for better display
                                                            echo "<td>" . htmlspecialchars(ucwords(str_replace('_', ' ', $row['status']))) . "</td>";

                                                            echo "<td>" . htmlspecialchars($row['updated_by']) . "</td>";
                                                            echo "<td>" . htmlspecialchars(date('d-M-Y', strtotime($row['updated_at']))) . "</td>";
                                                            echo "<td>" . htmlspecialchars($row['remarks']) . "</td>";
                                                            echo "<td>";
                                                            // Edit button
                                                            echo "<a href='edit-assign-task.php?id=" . urlencode($row['id']) . "' class='btn btn-warning'><i class='ri-pencil-line'></i></a>";
                                                            echo "  ";
                                                            // Delete button
                                                            echo "<a href='delete-assign-tasks.php?id=" . urlencode($row['id']) . "' class='btn btn-danger' onclick='return confirmDelete();'><i class='ri-delete-bin-line'></i></a>";
                                                            echo "</td>";
                                                            echo "</tr>";
                                                        }
                                                    } else {
                                                        echo "<tr><td colspan='9'>No task assignments found</td></tr>";
                                                    }

                                                    // Commit the transaction
                                                    $conn->commit();
                                                } catch (Exception $e) {
                                                    // Rollback in case of error
                                                    $conn->rollback();

                                                    // Display or log the error
                                                    echo "<tr><td colspan='9'>Error: " . $e->getMessage() . "</td></tr>";
                                                } finally {
                                                    // Close the statement
                                                    if (isset($stmt)) {
                                                        $stmt->close();
                                                    }
                                                }
                                                ?>
                                            </tbody>
                                        </table>


                                    </div> <!-- table-responsive-sm -->
                                </div> <!-- card-body -->
                            </div> <!-- card -->
                        </div> <!-- col-xl-12 -->
                    </div> <!-- row -->
                </div> <!-- container-fluid -->
            </div> <!-- content -->
        </div> <!-- content-page -->
    </div> <!-- wrapper -->
    <?php include 'layouts/footer.php'; ?>
    <?php include 'layouts/footer-scripts.php'; ?>

    <script>
        // Function to confirm deletion
        function confirmDelete() {
            return confirm('Are you sure you want to delete this Task?');
        }

        // JavaScript validation for form
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                var forms = document.getElementsByClassName('needs-validation');
                var validation = Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();
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