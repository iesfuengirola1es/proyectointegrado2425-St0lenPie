<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    die("Acceso no autorizado.");
}

/**
 * Verifica si el usuario tiene un permiso específico.
 *
 * @param string $permiso Nombre del permiso a verificar.
 * @return bool Devuelve `true` si el usuario tiene el permiso, `false` si no lo tiene.
 */
function usuarioTienePermiso($permiso) {
    global $pdo;

    $user_id = $_SESSION['user_id'];

    // Obtener el rol del usuario
    $stmt = $pdo->prepare("SELECT id_rol FROM usuarios WHERE id_usuario = ?");
    $stmt->execute([$user_id]);
    $id_rol = $stmt->fetchColumn();

    if (!$id_rol) {
        return false;
    }

    // Verificar si el rol tiene el permiso
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM roles_permisos 
        INNER JOIN permisos ON roles_permisos.id_permiso = permisos.id_permiso
        WHERE roles_permisos.id_rol = ? AND permisos.nombre = ?");
    $stmt->execute([$id_rol, $permiso]);

    return $stmt->fetchColumn() > 0;
}

/**
 * Obtiene todos los permisos de un usuario para su rol.
 *
 * @return array Devuelve una lista de permisos del usuario.
 */
function obtenerPermisosUsuario() {
    global $pdo;

    $user_id = $_SESSION['user_id'];

    // Obtener el rol del usuario
    $stmt = $pdo->prepare("SELECT id_rol FROM usuarios WHERE id_usuario = ?");
    $stmt->execute([$user_id]);
    $id_rol = $stmt->fetchColumn();

    if (!$id_rol) {
        return [];
    }

    // Obtener la lista de permisos asignados al rol del usuario
    $stmt = $pdo->prepare("
        SELECT permisos.nombre 
        FROM roles_permisos 
        INNER JOIN permisos ON roles_permisos.id_permiso = permisos.id_permiso
        WHERE roles_permisos.id_rol = ?");
    $stmt->execute([$id_rol]);

    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

/**
 * Verifica si el usuario es Administrador.
 *
 * @return bool Devuelve `true` si el usuario tiene el rol de Administrador, `false` si no.
 */
function esAdministrador() {
    global $pdo;
    $user_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM usuarios 
        WHERE id_usuario = ? 
        AND id_rol = (SELECT id_rol FROM roles WHERE nombre = 'Administrador')");
    $stmt->execute([$user_id]);

    return $stmt->fetchColumn() > 0;
}
?>
