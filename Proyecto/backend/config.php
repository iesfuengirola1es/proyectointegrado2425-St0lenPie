<?php
$db_host = "127.0.0.1";
$db_name = "ericproject2025";
$db_user = "myproyectod0";
$db_pass = "|NY7V6WD"; 

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "";
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>
