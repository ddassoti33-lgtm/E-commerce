<?php
$host = "localhost";
$dbname = "tp_php";
$user = "root";
$pass = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
} catch(Exception $e) {
    die("Erreur DB");
}
?>