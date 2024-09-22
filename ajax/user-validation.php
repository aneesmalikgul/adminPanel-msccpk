<?php
include '../layouts/config.php';

$response = [
    'status' => 'success',
    'messages' => []
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['cnic'])) {
        $cnic = $_POST['cnic'];
        $query = "SELECT id FROM users WHERE cnic = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 's', $cnic);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if (mysqli_num_rows($result) > 0) {
            $response['status'] = 'error';
            $response['messages']['cnic'] = 'CNIC is already in use.';
        }
        mysqli_stmt_close($stmt);
    }

    if (isset($_POST['email'])) {
        $email = $_POST['email'];
        $query = "SELECT id FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if (mysqli_num_rows($result) > 0) {
            $response['status'] = 'error';
            $response['messages']['email'] = 'Email is already in use.';
        }
        mysqli_stmt_close($stmt);
    }

    if (isset($_POST['username'])) {
        $username = $_POST['username'];
        $query = "SELECT id FROM users WHERE username = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 's', $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if (mysqli_num_rows($result) > 0) {
            $response['status'] = 'error';
            $response['messages']['username'] = 'Username is already in use.';
        }
        mysqli_stmt_close($stmt);
    }
}

echo json_encode($response);
mysqli_close($conn);
