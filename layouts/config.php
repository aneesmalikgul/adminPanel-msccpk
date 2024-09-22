<?php

#########################################   DB Credentials for local host   #########################################    

/* Database credentials. Assuming you are running MySQL
server with default setting (user 'root' with no password) */
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'msccpk_admin_panel');

/* Attempt to connect to MySQL database using MySQLi object-oriented style */
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("ERROR: Could not connect. " . $conn->connect_error);
}

#########################################   DB Credentials for Live Server   #########################################    

/* Database credentials.
/* Database credentials for live server */
// define('DB_SERVER', 'localhost');
// define('DB_USERNAME', 'msccpk_admin');
// define('DB_PASSWORD', 'VnmEN8gZt9zN');
// define('DB_NAME', 'msccpk_admin_panel');

/* Attempt to connect to MySQL database using MySQLi object-oriented style */
// $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
// if ($conn->connect_error) {
//     die("ERROR: Could not connect. " . $conn->connect_error);
// }
#########################################   Email Credentials   #########################################    

$gmailid = ''; // YOUR gmail email
$gmailpassword = ''; // YOUR gmail password
$gmailusername = ''; // YOUR gmail User name
