<?php
$host     = getenv('DB_HOST') ?: 'mysql';
$dbname   = getenv('DB_NAME') ?: 'channelling_db';
$username = getenv('DB_USER') ?: 'channelling_user';
$password = getenv('DB_PASS') ?: 'channelling_pass';

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>