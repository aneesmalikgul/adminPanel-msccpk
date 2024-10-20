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
$projects = [];

try {
    // Start the transaction
    $conn->begin_transaction();

    // Query to fetch projects
    $queryProjects = "SELECT id, project_name FROM projects ORDER BY id ASC;";
    $stmtProjects = $conn->prepare($queryProjects);
    $stmtProjects->execute();
    $resultProjects = $stmtProjects->get_result();

    while ($row = $resultProjects->fetch_assoc()) {
        $projects[] = $row;
    }

    // Commit the transaction
    $conn->commit();
} catch (Exception $e) {
    // Rollback the transaction on error
    $conn->rollback();
    $_SESSION['message'] = ['type' => 'error', 'content' => $e->getMessage()];
}

// Close the statements
$stmtProjects->close();

// If form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btnSaveTask'])) {
    // Get POST data and sanitize
    $taskName = isset($_POST['taskName']) ? $_POST['taskName'] : '';
    $taskDetails = isset($_POST['taskDetails']) ? $_POST['taskDetails'] : '';
    $projectId = isset($_POST['assignProject']) ? (int)$_POST['assignProject'] : 0;
    $taskPriority = isset($_POST['taskPriority']) ? $_POST['taskPriority'] : '';
    $taskStatus = isset($_POST['taskStatus']) ? (int)$_POST['taskStatus'] : 1;
    $createdBy = $_SESSION['user_id'];
    $updatedBy = $_SESSION['user_id'];

    try {
        // Start the transaction
        $conn->begin_transaction();

        // Insert into tasks table
        $stmt = $conn->prepare("INSERT INTO tasks (	task_title, task_description, project_id, priority, status, created_by, updated_by) 
                                VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssissii', $taskName, $taskDetails, $projectId, $taskPriority, $taskStatus, $createdBy, $updatedBy);

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
    header('Location: add-tasks.php');
    exit();
}

// Code handling the update operation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btnUpdateTask'])) {
    // Get and sanitize POST data
    $taskId = isset($_POST['taskId']) ? intval($_POST['taskId']) : 0;
    $projectId = isset($_POST['assignProject']) ? intval($_POST['assignProject']) : 0;
    $taskTitle = isset($_POST['taskName']) ? trim($_POST['taskName']) : '';
    $taskDescription = isset($_POST['taskDetails']) ? trim($_POST['taskDetails']) : '';
    $priority = isset($_POST['taskPriority']) ? $_POST['taskPriority'] : 'Medium';
    $status = isset($_POST['taskStatus']) ? intval($_POST['taskStatus']) : 0;

    try {
        // Validate input
        if ($taskId <= 0 || $projectId <= 0 || empty($taskTitle)) {
            throw new Exception('Invalid input data.');
        }

        // Begin transaction
        $conn->begin_transaction();

        // Prepare the update query
        $query = "
            UPDATE tasks
            SET project_id = ?, task_title = ?, task_description = ?, priority = ?, status = ?
            WHERE id = ?;
        ";

        $stmt = $conn->prepare($query);

        if ($stmt === false) {
            throw new Exception('Prepare statement failed: ' . $conn->error);
        }

        // Bind parameters and execute statement
        $stmt->bind_param("isssii", $projectId, $taskTitle, $taskDescription, $priority, $status, $taskId);
        if (!$stmt->execute()) {
            throw new Exception('Execute statement failed: ' . $stmt->error);
        }

        // Commit transaction
        $conn->commit();

        // Set success message and redirect
        $_SESSION['message'][] = ['type' => 'success', 'content' => 'Task updated successfully.'];
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
        header('Location: add-tasks.php');
        exit();
    }
}

?>

<head>
    <title>New Task | Mohsin Shaheen Construction Company</title>
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
                                        <li class="breadcrumb-item active"> Add Task</li>
                                    </ol>
                                </div>
                                <h4 class="page-title">Add Task</h4>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <?php displaySessionMessage(); ?>
                            <h2 class="text-center">Create New Task</h2>
                            <div class="card">
                                <div class="card-body">
                                    <p class="text-muted fs-14"> </p>
                                    <div class="row">
                                        <div>
                                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="needs-validation" novalidate enctype="multipart/form-data">
                                                <div class="row mb-3">
                                                    <h3>Task Characteristics</h3>

                                                    <!-- Task Name Input -->
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="taskName" class="form-label">Task Name</label>
                                                            <input type="text" id="taskName" name="taskName" class="form-control" required placeholder="Enter the name of Task">
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback">Please fill this field.</div>
                                                        </div>
                                                    </div>


                                                    <!-- Assign to Project Dropdown -->
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="assignProject" class="form-label">Assign to Project</label>
                                                            <select id="assignProject" name="assignProject" class="form-select" required>
                                                                <option selected disabled value="">Select Project</option>
                                                                <?php foreach ($projects as $project): ?>
                                                                    <option value="<?php echo $project['id']; ?>"><?php echo $project['project_name']; ?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback">Please select a project.</div>
                                                        </div>
                                                    </div>

                                                    <!-- Task priority Dropdown -->
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="taskPriority" class="form-label">Task Priority</label>
                                                            <select id="taskPriority" name="taskPriority" class="form-select" required>
                                                                <option selected disabled value="">Select Prioriy</option>
                                                                <option value="Low">Low </option>
                                                                <option value="Medium">Medium</option>
                                                                <option value="High">High</option>
                                                            </select>
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback">Please select a task Priority.</div>
                                                        </div>
                                                    </div>
                                                    <!-- Task Status Dropdown -->
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="taskStatus" class="form-label">Task Status</label>
                                                            <select id="taskStatus" name="taskStatus" class="form-select" required>
                                                                <option selected disabled value="">Select Task Status</option>
                                                                <option value="1">Active</option>
                                                                <option value="0">Inactive</option>
                                                            </select>
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback">Please select a task status.</div>
                                                        </div>
                                                    </div>
                                                    <!-- Task Details Input -->
                                                    <div class="col-lg-12">
                                                        <div class="mb-3">
                                                            <label for="taskDetails" class="form-label">Task Details</label>
                                                            <textarea id="taskDetails" name="taskDetails" class="form-control" rows="3" required></textarea>
                                                            <div class="valid-feedback">Looks good!</div>
                                                            <div class="invalid-feedback">Please fill this field.</div>
                                                        </div>
                                                    </div>

                                                </div>

                                                <!-- Save Task Button -->
                                                <div class="row mb-3">
                                                    <div class="col-lg-12 text-center">
                                                        <button type="submit" id="btnSaveTask" name="btnSaveTask" class="btn btn-primary">Save Task</button>
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
                                                    <th>Task Title</th>
                                                    <th> Task Description</th>
                                                    <th> Task Priority</th>
                                                    <th>Project Name</th>
                                                    <th> Task Status</th>
                                                    <th>Created By</th>
                                                    <th>Created At</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                try {
                                                    // Start transaction
                                                    mysqli_begin_transaction($conn);

                                                    // Query to fetch tasks with project name and creator's username
                                                    $query = "SELECT t.id, t.task_title, t.task_description, t.priority, t.status, t.created_at, 
                             p.project_name, u.username AS created_by
                      FROM tasks t
                      LEFT JOIN projects p ON t.project_id = p.id
                      LEFT JOIN users u ON t.created_by = u.id
                      ORDER BY t.id ASC;";

                                                    // Execute the query
                                                    $result = mysqli_query($conn, $query);

                                                    // Check if the query execution was successful
                                                    if (!$result) {
                                                        throw new Exception("Error executing query: " . mysqli_error($conn));
                                                    }

                                                    // If the query is successful, loop through the results
                                                    if (mysqli_num_rows($result) > 0) {
                                                        while ($row = mysqli_fetch_assoc($result)) {
                                                            echo "<tr>";
                                                            echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                                                            echo "<td>" . htmlspecialchars($row['task_title']) . "</td>";
                                                            echo "<td>" . htmlspecialchars($row['task_description']) . "</td>";
                                                            echo "<td>" . htmlspecialchars(ucfirst($row['priority'])) . "</td>";
                                                            echo "<td>" . htmlspecialchars($row['project_name']) . "</td>";

                                                            // Convert status to Active/Inactive
                                                            $statusText = $row['status'] == 1 ? 'Active' : 'Not Active';
                                                            echo "<td>" . htmlspecialchars($statusText) . "</td>";

                                                            echo "<td>" . htmlspecialchars($row['created_by']) . "</td>";
                                                            echo "<td>" . htmlspecialchars(date('d-M-Y', strtotime($row['created_at']))) . "</td>";
                                                            echo "<td>";
                                                            // Edit button
                                                            echo "<a href='edit-task.php?id=" . urlencode($row['id']) . "' class='btn btn-warning'><i class='ri-pencil-line'></i></a>";
                                                            echo "  ";
                                                            // Delete button
                                                            echo "<a href='delete-task.php?id=" . urlencode($row['id']) . "' class='btn btn-danger' onclick='return confirmDelete();' ><i class='ri-delete-bin-line'></i></a>";
                                                            echo "</td>";
                                                            echo "</tr>";
                                                        }
                                                    } else {
                                                        echo "<tr><td colspan='9'>No tasks found</td></tr>";
                                                    }

                                                    // Commit transaction after fetching tasks
                                                    $conn->commit();
                                                } catch (Exception $e) {
                                                    // Rollback transaction in case of error
                                                    $conn->rollback();
                                                    $_SESSION['message'] = ['type' => 'error', 'content' => $e->getMessage()];
                                                }

                                                // Free result set only if the query was successful and $result is valid
                                                if (isset($result) && $result instanceof mysqli_result) {
                                                    mysqli_free_result($result);
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