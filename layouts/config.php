<?php

#########################################   DB Credentials for local host   #########################################    

/* Database credentials. Assuming you are running MySQL
server with default setting (user 'root' with no password) */
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'msccpk_admin_panel');

/* Attempt to connect to MySQL database */
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn === false) {
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

#########################################   DB Credentials for Live Server   #########################################    

/* Database credentials. Assuming you are running MySQL
server with default setting (user 'root' with no password) */
// define('DB_SERVER', 'localhost');
// define('DB_USERNAME', 'msccpk_admin');
// define('DB_PASSWORD', 'VnmEN8gZt9zN');
// define('DB_NAME', 'msccpk_admin_panel');

/* Attempt to connect to MySQL database */
// $conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
// if ($conn === false) {
//     die("ERROR: Could not connect. " . mysqli_connect_error());
// }

#########################################   Email Credentials   #########################################    

$gmailid = ''; // YOUR gmail email
$gmailpassword = ''; // YOUR gmail password
$gmailusername = ''; // YOUR gmail User name
