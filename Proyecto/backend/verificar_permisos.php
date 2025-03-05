<?php

/**
 * Módulo: Verificación de Permisos de Usuario
 * 
 * Este script define funciones para verificar los permisos de un usuario en el sistema.
 * Se usa para validar si un usuario tiene acceso a determinadas funcionalidades según su rol.
 *
 * Ejemplo de llamada:
 * -------------------
 * if (usuarioTienePermiso("gestionar_personas")) {
 *     echo "El usuario tiene permiso para gestionar personas.";
 * } else {
 *     echo "Acceso denegado.";
 * }
 *
 * Argumentos:
 * -----------
 * Entrada:
 * - `$_SESSION['user_id']` (int) → ID del usuario autenticado. Obligatorio.
 * - `$_SESSION['id_empresa']` (int) → ID del grupo empresarial al que pertenece el usuario.
 *
 * Salida:
 * - `true` → Si el usuario tiene el permiso requerido.
 * - `false` → Si el usuario no tiene permisos suficientes.
 *
 * Módulos relacionados:
 * ---------------------
 * - `config.php` → Contiene la configuración de conexión a la base de datos.
 * - `usuarios_grupos` (tabla) → Relaciona usuarios con grupos y sus roles.
 * - `roles` (tabla) → Contiene la lista de roles disponibles en el sistema.
 * - `roles_permisos` (tabla) → Relaciona roles con permisos específicos.
 * - `permisos` (tabla) → Contiene la lista de permisos del sistema.
 *
 * Funciones disponibles:
 * -----------------------
 * 1. **usuarioTienePermiso($permiso)**
 *    - Verifica si el usuario tiene un permiso específico.
 *    - **Parámetro:** `$permiso` (string) → Nombre del permiso a validar.
 *    - **Salida:** `true` si el usuario tiene el permiso, `false` si no.
 *
 * 2. **obtenerPermisosUsuario()**
 *    - Obtiene todos los permisos asignados al rol del usuario.
 *    - **Salida:** Un array con los nombres de los permisos del usuario.
 *
 * 3. **esAdministrador()**
 *    - Verifica si el usuario tiene el rol de "Administrador".
 *    - **Salida:** `true` si el usuario es administrador, `false` si no.
 *
 * Flujo de datos interno:
 * -----------------------
 * 1. Se inicia la sesión y se verifica que el usuario esté autenticado (`$_SESSION['user_id']`).
 * 2. **`usuarioTienePermiso($permiso)`**:
 *    - Obtiene el rol del usuario en la empresa desde `usuarios_grupos`.
 *    - Consulta la tabla `roles_permisos` para verificar si el rol tiene el permiso solicitado.
 *    - Retorna `true` si el permiso está asignado al rol, `false` en caso contrario.
 * 3. **`obtenerPermisosUsuario()`**:
 *    - Obtiene la lista completa de permisos asociados al rol del usuario.
 * 4. **`esAdministrador()`**:
 *    - Consulta si el usuario tiene el rol de "Administrador" en la empresa actual.
 *    - Retorna `true` si es administrador, `false` en caso contrario.
 */

session_start();
require 'config.php';

if (!isset($_SESSION['user_id'] )) {
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
    $grupo_id = $_SESSION['id_empresa'];

    // Obtener el rol del usuario
    $stmt = $pdo->prepare("SELECT rol FROM usuarios_grupos WHERE id_usuario = ? AND id_empresa = ?");
    $stmt->execute([$user_id, $grupo_id]);
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
    $stmt = $pdo->prepare("SELECT rol FROM usuarios WHERE id_usuario = ?");
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
    $grupo_id = $_SESSION['id_empresa'];

    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM usuarios_grupos 
        WHERE id_usuario = ? and 
        AND id_empresa = ?
        AND rol = (SELECT id_rol FROM roles WHERE nombre = 'Administrador')");
    $stmt->execute([$user_id,$grupo_id]);

    return $stmt->fetchColumn() > 0;
}
?>
