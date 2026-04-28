<?php
// Database connection details
$host = "localhost";
$dbname = "teknikal_db_ekenderaan";
$username = "teknikal_db_ekenderaan";
$password = "q^=TFAt&Z3I4u{Qc";
$port = "3306";

try {
    // Establish a database connection using PDO
   $pdo = new PDO(
    "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8",
    $username,
    $password
);

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("PDO Connection failed: " . $e->getMessage());
}

// Establish a database connection using MySQLi
$conn = new mysqli($host, $username, $password, $dbname, $port);

// Check the MySQLi connection
if ($conn->connect_error) {
    die("MySQLi Connection failed: " . $conn->connect_error);
}
?>
