<?php

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'smart_energy';

try {
    $conn = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "connected!";
} catch(PDOException $e) {
    echo "connection failed: " . $e->getMessage();
}

?>
