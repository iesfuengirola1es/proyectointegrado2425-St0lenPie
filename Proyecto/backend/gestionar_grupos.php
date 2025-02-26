<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    die("Acceso no autorizado.");
}

$user_id = $_SESSION['user_id'];
$nombre_grupo = trim($_POST['nombre'] ?? '');

if (empty($nombre_grupo)) {
    die("error: El nombre del grupo no puede estar vacío.");
}

try {
    // Crear el grupo en la base de datos
    $stmt = $pdo->prepare("INSERT INTO empresa (nombre) VALUES (?)");
    $stmt->execute([$nombre_grupo]);
    $id_empresa = $pdo->lastInsertId();

    // Obtener el ID del rol "Administrador"
    $stmt = $pdo->prepare("SELECT id_rol FROM roles WHERE nombre = 'Administrador'");
    $stmt->execute();
    $id_rol_admin = $stmt->fetchColumn();

    if (!$id_rol_admin) {
        die("error: No se encontró el rol de Administrador.");
    }

    // Asignar el rol "Administrador" al usuario creador del grupo
    $stmt = $pdo->prepare("UPDATE usuarios SET id_empresa = ?, id_rol = ? WHERE id_usuario = ?");
    $stmt->execute([$id_empresa, $id_rol_admin, $user_id]);

    error_log("✅ Usuario ID $user_id asignado como Administrador del grupo ID $id_empresa");

    echo "success: Grupo creado y rol de Administrador asignado.";
} catch (PDOException $e) {
    error_log("❌ Error en la creación del grupo: " . $e->getMessage());
    echo "error: Error en la creación del grupo.";
}
?>
