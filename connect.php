<?php
$host = "sql311.infinityfree.com";
$username = "if0_41473015";
$password = "BbH0vlC3Ep";
$dbname = "if0_41473015_pharmacy_db";

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error){
    die("connection failed:".$conn->connect_error);
}
?>