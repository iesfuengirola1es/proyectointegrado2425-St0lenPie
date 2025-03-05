<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    die("Acceso no autorizado.");
}

$user_id = $_SESSION['user_id'];
$nombreGrupo = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';

if (empty($nombreGrupo)) {
    die("error: El nombre del grupo no puede estar vacío.");
}

try {
    // Verificar si el grupo ya existe
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM empresa WHERE nombre = ?");
    $stmt->execute([$nombreGrupo]);
    $existe = $stmt->fetchColumn();

    if ($existe > 0) {
        die("error: El nombre del grupo ya existe.");
    }

    // Insertar el grupo en la base de datos
    $stmt = $pdo->prepare("INSERT INTO empresa (nombre, clave_acceso,id_creador) VALUES (?, ?,?)");
    $claveAcceso = bin2hex(random_bytes(5));
    $stmt->execute([$nombreGrupo, $claveAcceso,$user_id]);

    $grupo_id = $pdo->lastInsertId();

    // Insertar relación en usuarios_grupos con el rol de creador
    $stmt = $pdo->prepare("INSERT INTO usuarios_grupos (id_usuario, id_empresa, rol) VALUES (?, ?, 1)");
    $stmt->execute([$user_id, $grupo_id]);

    echo "success: Grupo creado exitosamente.";
} catch (PDOException $e) {
    echo "error: Error al crear el grupo: " . $e->getMessage();
}
?>
