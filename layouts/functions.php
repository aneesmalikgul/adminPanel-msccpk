<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer files
require 'assets/vendor/PHPMailer/src/Exception.php';
require 'assets/vendor/PHPMailer/src/PHPMailer.php';
require 'assets/vendor/PHPMailer/src/SMTP.php';

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
    global $conn;
    $role_id = $_SESSION['role_id'];

    // Prepare the SQL statement
    $stmt = $conn->prepare("SELECT COUNT(*) FROM role_permissions rp
                            INNER JOIN permissions p ON rp.permission_id = p.id
                            WHERE rp.role_id = ? AND p.permission_name = ?");

    // Bind parameters and execute the statement
    $stmt->bind_param("is", $role_id, $permission_name);
    $stmt->execute();

    // Get the result
    $stmt->bind_result($count);
    $stmt->fetch();

    // Close the statement
    $stmt->close();

    // Return true if count > 0, meaning permission exists
    return $count > 0;
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
        if ($stmt = $conn->prepare($query)) {
            // Bind the parameter
            $stmt->bind_param("i", $role_id);

            // Execute the query
            $stmt->execute();

            // Bind the result variable
            $stmt->bind_result($role_name);

            // Fetch the result
            if ($stmt->fetch()) {
                $stmt->close();
                return $role_name;  // Return the role name
            } else {
                $stmt->close();
                return "Unknown Role";  // Handle case where no role is found
            }
        } else {
            return "Query Preparation Failed";  // Handle case where statement preparation fails
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
                echo "<div class='alert alert-{$alertType} alert-dismissible fade show' role='alert' id='session-alert'>";
                echo "{$content}";
                echo "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>";
                echo "</div>";
            }
        }

        // Unset the session message after displaying it
        unset($_SESSION['message']);

        // Add JavaScript to dismiss the alert after 3 seconds
        echo "<script>
                setTimeout(function() {
                    var alert = document.getElementById('session-alert');
                    if (alert) {
                        alert.classList.remove('show');
                        alert.classList.add('fade');
                        setTimeout(function() {
                            alert.remove();
                        }, 500); // Wait for the fade effect to finish
                    }
                }, 3000); // 3 seconds
              </script>";
    }
}

function checkUniqueField($conn, $field, $value, $userId = null)
{
    // Prepare the base query
    $query = "SELECT COUNT(*) as count FROM users WHERE {$field} = ?";

    // Add the condition for excluding the current user ID if provided
    if ($userId !== null) {
        $query .= " AND id != ?";
    }

    // Prepare the statement
    $stmt = $conn->prepare($query);

    // Bind parameters
    if ($userId !== null) {
        $stmt->bind_param("si", $value, $userId);
    } else {
        $stmt->bind_param("s", $value);
    }

    // Execute the query
    $stmt->execute();

    // Fetch result
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    // Return true if the count is greater than 0, indicating a duplicate
    return $row['count'] > 0;
}

// Function to fetch a specific column value from any table based on column name and ID
function rowInfo($conn, $tableName, $columnName, $id)
{
    // Start a transaction
    $conn->begin_transaction();

    try {
        // Use prepared statement to prevent SQL injection
        $query = "SELECT {$columnName} FROM {$tableName} WHERE id = ?";

        // Prepare the SQL query
        $stmt = $conn->prepare($query);

        // Bind the parameter (assuming ID is an integer)
        $stmt->bind_param("i", $id); // Adjust type based on ID datatype

        // Execute the query
        $stmt->execute();

        // Fetch result
        $result = $stmt->get_result();

        // Check if a row was found
        if ($result->num_rows > 0) {
            // Fetch the row as an associative array
            $row = $result->fetch_assoc();
            $value = $row[$columnName]; // Return the specific column value

            // Commit the transaction
            $conn->commit();

            return $value;
        } else {
            // Return null if no row was found
            $conn->commit(); // Commit if no errors occurred
            return null;
        }
    } catch (Exception $e) {
        // Rollback the transaction on error
        $conn->rollback();

        // Handle the error (optional: log it or rethrow)
        $_SESSION['message'][] = ["type" => "danger", "content" => "Error fetching data: " . $e->getMessage()];

        return null; // Or handle error appropriately
    } finally {
        // Close the statement
        if (isset($stmt) && $stmt) {
            $stmt->close();
        }
    }
}



function sendUserCreationEmail($userEmail, $userName, $userFullName, $userPassword)
{
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'mail.mscc.pk'; // SMTP host
        $mail->SMTPAuth   = true;
        $mail->Username   = 'admin@mscc.pk'; // SMTP username
        $mail->Password   = 'VnmEN8gZt9zN'; // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Encryption
        $mail->Port       = 587; // SMTP port

        // Recipients
        $mail->setFrom('info@mscc.pk', 'Mohsin Shaheen Construction Company');
        $mail->addAddress($userEmail, $userFullName); // Add recipient
        $mail->addReplyTo('info@mscc.pk'); // Reply-to address

        // Content
        $mail->isHTML(true); // Set email format to HTML
        $mail->Subject = 'Your Account Details for MSCC Dashboard';

        // Email body content
        $mail->Body = "
            <h1>Hello, {$userFullName}</h1>
            <p>Thank you for registering with Mohsin Shaheen Construction Company. Below are your account details:</p>
            <ul>
                <li><strong>Email:</strong> {$userEmail}</li>
                <li><strong>Username:</strong> {$userName}</li>
                <li><strong>Password:</strong> {$userPassword}</li>
            </ul>
            <p>You can log in to your account using the following link:</p>
            <p><a href='https://dashboard.mscc.pk/'>Login to MSCC Dashboard</a></p>
            <br>
            <p>If you did not register for this account, please contact our support team immediately.</p>
            <p>Best regards,<br>MSCC Team</p>
        ";

        // Send the email
        $mail->send();
        return true; // Email sent successfully
    } catch (Exception $e) {
        // return "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        return "Message could not be sent. ";
    }
}
