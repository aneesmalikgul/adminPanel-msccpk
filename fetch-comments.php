<?php
include 'layouts/config.php';

// Get the status from the AJAX request
$status = isset($_GET['status']) ? $_GET['status'] : 'pending';

// Ensure the status is valid
$validStatuses = ['approved', 'pending', 'rejected'];
if (!in_array($status, $validStatuses)) {
    $status = 'pending';
}

$response = '';

// Prepare SQL query based on the selected filter
$query = "SELECT c.id, b.title AS blog_name, c.name, c.email, c.website, c.comment, c.status, c.created_at 
          FROM comments c 
          JOIN blog_posts b ON c.blog_post_id = b.id 
          WHERE c.status = ?";

if ($stmt = $conn->prepare($query)) {
    $stmt->bind_param('s', $status);
    $stmt->execute();

    // Bind the result variables
    $stmt->bind_result($id, $blog_name, $name, $email, $website, $comment, $status, $created_at);

    // Fetch values
    while ($stmt->fetch()) {
        $response .= "<tr>";
        $response .= "<td>" . htmlspecialchars($id) . "</td>";
        $response .= "<td>" . wordwrap(htmlspecialchars($blog_name), 30, "<br />\n", true) . "</td>";
        $response .= "<td>" . htmlspecialchars($name) . "</td>";
        $response .= "<td>" . htmlspecialchars($email) . "</td>";
        $response .= "<td>" . htmlspecialchars($website) . "</td>";
        $response .= "<td>" . wordwrap(htmlspecialchars($comment), 40, "<br />\n", true) . "</td>";
        $response .= "<td>" . htmlspecialchars($status) . "</td>";
        $response .= "<td>" . htmlspecialchars(date('d-M-Y', strtotime($created_at))) . "</td>";
        $response .= "<td>";
        $response .= "<a href='view-comment.php?id=" . urlencode($id) . "' class='btn btn-primary'><i class='ri-eye-line'></i></a>";
        $response .= "</td>";
        $response .= "</tr>";
    }

    $stmt->close();
} else {
    $response .= "<tr><td colspan='9'>Error preparing statement: " . htmlspecialchars($conn->error) . "</td></tr>";
}

mysqli_close($conn);

// Return the HTML response
echo $response;
