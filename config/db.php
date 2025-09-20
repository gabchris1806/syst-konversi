<?php
$host = "localhost";
$user = "root"; // default user XAMPP
$pass = ""; 
$db   = "db_konversi";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>
