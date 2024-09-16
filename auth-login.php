<?php
session_start();
include 'layouts/config.php';
include 'layouts/functions.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $_SESSION['message'] = array("type" => "error", "content" => "Email and Password are required.");
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    try {
        // Enable error reporting
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        // Prepare the SQL statement
        $stmt = mysqli_prepare($conn, "SELECT id, first_name, last_name, username, password_hash, role_id FROM users WHERE email = ?");
        if ($stmt === false) {
            throw new Exception("Prepare statement failed: " . mysqli_error($conn));
        }

        // Bind parameters
        mysqli_stmt_bind_param($stmt, "s", $email);

        // Execute the statement
        mysqli_stmt_execute($stmt);

        // Bind the result
        mysqli_stmt_bind_result($stmt, $id, $first_name, $last_name, $username, $hashed_password, $role);

        // Fetch the result
        if (mysqli_stmt_fetch($stmt)) {
            // Verify the password
            if (password_verify($password, $hashed_password)) {
                // if ($password === $hashed_password) {
                // Store data in session variables
                $_SESSION["loggedin"] = true;
                $_SESSION["user_id"] = $id;
                $_SESSION["first_name"] = $first_name;
                $_SESSION["last_name"] = $last_name;
                $_SESSION["username"] = $username;
                $_SESSION["role_id"] = $role;
                $_SESSION["email"] = $email;

                // Set success message
                $_SESSION['message'][] = ["type" => "success", "content" => "Login successful!"];
                header("Location: index.php");
                exit();
            } else {
                $_SESSION['message'][] = ["type" => "danger", "content" => "Invalid email or password."];
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            }
        } else {
            $_SESSION['message'][] = ["type" => "danger", "content" => "Invalid email or password."];
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    } catch (Exception $e) {
        $_SESSION['message'][] = ["type" => "danger", "content" => "Error: " . $e->getMessage()];
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } finally {
        // Close the statement and the connection
        if (isset($stmt)) {
            mysqli_stmt_close($stmt);
        }
        if (isset($conn)) {
            mysqli_close($conn);
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <title>Log In | Mohsin Shaheed Construction Company</title>
    <?php include 'layouts/title-meta.php'; ?>
    <?php include 'layouts/head-css.php'; ?>

</head>

<body class="authentication-bg position-relative">

    <?php include 'layouts/background.php'; ?>

    <div class="account-pages pt-2 pt-sm-5 pb-4 pb-sm-5 position-relative">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xxl-4 col-lg-5">
                    <div class="card">

                        <!-- Logo -->
                        <div class="card-header text-center" style="padding-top: 40px;">
                            <a href="index.php">
                                <span><img src="assets/images/logo.png" alt="logo" height="70"></span>
                            </a>
                        </div>

                        <div class="card-body">

                            <div class="text-center w-75 m-auto">
                                <h4 class="text-dark-50 text-center pb-0 fw-bold">Sign In</h4>
                                <p class="text-muted mb-3">Enter your email address and password to access admin panel.</p>
                            </div>
                            <?php displaySessionMessage(); ?>

                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                                <div class="mb-2">
                                    <label for="emailaddress" class="form-label">Email address</label>
                                    <input class="form-control" type="email" id="emailaddress" name="email" required="" placeholder="Enter your email">
                                </div>

                                <div class="mb-2">
                                    <a href="auth-recoverpw.php" class="text-muted float-end fs-12">Forgot your password?</a>
                                    <label for="password" class="form-label">Password</label>
                                    <div class="input-group input-group-merge">
                                        <input type="password" id="password" class="form-control" name="password" placeholder="Enter your password">
                                        <div class="input-group-text" data-password="false">
                                            <span class="password-eye"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-2 mb-2">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="checkbox-signin" checked>
                                        <label class="form-check-label" for="checkbox-signin">Remember me</label>
                                    </div>
                                </div>

                                <div class="mb-2 mb-0 text-center">
                                    <button class="btn btn-primary" type="submit"> Log In </button>
                                </div>

                            </form>
                        </div> <!-- end card-body -->
                    </div>
                    <!-- end card -->

                    <div class="row mt-2">
                        <div class="col-12 text-center">
                            <p class="text-muted bg-body">Don't have an account? <a href="auth-register.php" class="text-muted ms-1 link-offset-3 text-decoration-underline"><b>Sign Up</b></a></p>
                        </div> <!-- end col -->
                    </div>
                    <!-- end row -->

                </div> <!-- end col -->
            </div>
            <!-- end row -->
        </div>
        <!-- end container -->
    </div>
    <!-- end page -->

    <footer class="footer footer-alt fw-medium">
        <span class="bg-body">
            <script>
                document.write(new Date().getFullYear())
            </script> © Mohsin Shaheen Construction Company - <a href="https://themillionairesoft.com/">The Millionaire Soft.</a>
        </span>
    </footer>
    <?php include 'layouts/footer-scripts.php'; ?>

    <!-- App js -->
    <script src="assets/js/app.min.js"></script>

</body>

</html>