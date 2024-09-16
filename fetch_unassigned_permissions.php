<?php
include 'layouts/session.php';
include 'layouts/config.php';

if (isset($_POST['roleId'])) {
    $roleId = $_POST['roleId'];

    try {
        // Start the transaction
        mysqli_begin_transaction($conn);

        // Query to fetch permissions that have NOT been assigned to the selected role
        $queryUnassignedPermissions = "
            SELECT p.id, p.permission_name 
            FROM permissions p
            WHERE p.status = 1
            AND p.id NOT IN (
                SELECT rp.permission_id 
                FROM role_permissions rp 
                WHERE rp.role_id = ?
            )
            ORDER BY p.id ASC;
        ";

        $stmt = mysqli_prepare($conn, $queryUnassignedPermissions);
        mysqli_stmt_bind_param($stmt, "i", $roleId);
        mysqli_stmt_execute($stmt);
        $resultPermissions = mysqli_stmt_get_result($stmt);

        if ($resultPermissions) {
            while ($row = mysqli_fetch_assoc($resultPermissions)) {
                echo '<option value="' . $row['id'] . '">' . $row['permission_name'] . '</option>';
            }
        } else {
            throw new Exception('Error fetching permissions: ' . mysqli_error($conn));
        }

        // Commit the transaction
        mysqli_commit($conn);
    } catch (Exception $e) {
        // Rollback the transaction on error
        mysqli_rollback($conn);
        echo '<option value="">Error: ' . $e->getMessage() . '</option>';
    }
}
