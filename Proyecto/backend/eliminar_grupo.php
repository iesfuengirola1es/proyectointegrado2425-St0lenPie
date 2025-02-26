<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    die("Acceso no autorizado.");
}

$id_empresa = $_POST['id_empresa'] ?? null;
$user_id = $_SESSION['user_id'];

if (!$id_empresa) {
    die("error: ID de empresa no proporcionado.");
}

try {
    // Verificar si el usuario es el creador del grupo antes de eliminarlo
    $stmt = $pdo->prepare("SELECT rol FROM usuarios_grupos WHERE id_empresa = ? AND id_usuario = ?");
    $stmt->execute([$id_empresa, $user_id]);
    $rol = $stmt->fetchColumn();

    if ($rol !== 'creador') {
        die("error: No tienes permisos para eliminar este grupo.");
    }

    // Eliminar todas las relaciones del grupo en usuarios_grupos
    $stmt = $pdo->prepare("DELETE FROM usuarios_grupos WHERE id_empresa = ?");
    $stmt->execute([$id_empresa]);

    // Eliminar el grupo de la base de datos
    $stmt = $pdo->prepare("DELETE FROM empresa WHERE id_empresa = ?");
    $stmt->execute([$id_empresa]);

    echo "success: Grupo eliminado correctamente.";
} catch (PDOException $e) {
    echo "error: Error al eliminar el grupo: " . $e->getMessage();
}
?>
