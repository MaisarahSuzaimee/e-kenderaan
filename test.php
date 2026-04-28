<?php
// Database credentials
$host = "localhost";
$user = "teknikal_db_ekenderaan";
$password = "q^=TFAt&Z3I4u{Qc";
$dbname = "teknikal_db_ekenderaan";

// Connect to MySQL
$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 1️⃣ Create table 'test'
// $createTableSQL = "
// CREATE TABLE IF NOT EXISTS test (
//     id INT AUTO_INCREMENT PRIMARY KEY,
//     name VARCHAR(100) NOT NULL
// ) ENGINE=InnoDB;
// ";

// if ($conn->query($createTableSQL) === TRUE) {
//     echo "✅ Table 'test' created successfully.<br>";
// } else {
//     echo "❌ Error creating table: " . $conn->error . "<br>";
// }

// 2️⃣ Show all tables
$result = $conn->query("SHOW TABLES");

if ($result->num_rows > 0) {
    echo "<h2>Tables in database '$dbname':</h2><ul>";
    while ($row = $result->fetch_row()) {
        echo "<li>" . $row[0] . "</li>";
    }
    echo "</ul>";
} else {
    echo "No tables found in database '$dbname'.";
}

$table = "test";
$column = $conn->query("SHOW COLUMNS FROM `$table`");

if ($column->num_rows > 0) {
        echo "<h2>columns in table '$table':</h2><ul>";
         while ($row = $column->fetch_row()) {
        echo "<li>" . $row[0] . "</li>";
    }
    echo "</ul>";
} else {
    echo "No column found in table '$table'.";
}

// Close connection
$conn->close();
?>