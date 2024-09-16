<?php

// include 'config.php';
function get_home_url()
{
    // Determine the protocol
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    // Get the host
    $host = $_SERVER['HTTP_HOST'];
    // Get the base directory
    $baseDir = str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
    // Construct the home URL
    $homeUrl = $protocol . $host . $baseDir;
    echo $homeUrl;
}

function logMessage($message)
{
    error_log($message, 3, 'debug.log'); // Change 'debug.log' to the desired log file path
}

function hasPermission($permission_name)
{
    global $db;
    $role_id = $_SESSION['role_id'];

    $query = $db->prepare("SELECT COUNT(*) FROM role_permissions rp
                           INNER JOIN permissions p ON rp.permission_id = p.id
                           WHERE rp.role_id = ? AND p.permission_name = ?");
    $query->execute([$role_id, $permission_name]);

    return $query->fetchColumn() > 0;
}


function getUserRole()
{
    global $conn;

    // Ensure the session is started
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    // Check if role_id exists in the session
    if (isset($_SESSION['role_id'])) {
        $role_id = $_SESSION['role_id'];

        // Prepare the SQL query to get the role name based on the role_id
        $query = "SELECT role_name FROM roles WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $role_id);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if a role is found
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['role_name'];  // Return the role name
        } else {
            return "Unknown Role";  // Handle case where no role is found
        }
    } else {
        return "Role not set";  // Handle case where role_id is not in the session
    }
}

function displaySessionMessage()
{
    // Ensure $_SESSION['message'] is an array before accessing it
    if (isset($_SESSION['message']) && is_array($_SESSION['message'])) {
        foreach ($_SESSION['message'] as $message) {
            // Ensure message type and content are valid
            if (is_array($message) && isset($message['type'], $message['content'])) {
                $alertType = htmlspecialchars($message['type']);
                $content = htmlspecialchars($message['content']);

                // Output the Bootstrap alert
                echo "<div class='alert alert-{$alertType} alert-dismissible fade show' role='alert'>";
                echo "{$content}";
                echo "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>";
                echo "</div>";
            }
        }

        // Unset the session message after displaying it
        unset($_SESSION['message']);
    }
}
