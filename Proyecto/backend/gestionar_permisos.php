<?php

/**
 * Módulo: Gestión de Permisos de Usuario
 * 
 * Este script permite listar los permisos disponibles en el sistema, siempre que el usuario tenga el rol adecuado.
 *
 * Ejemplo de llamada:
 * -------------------
 * fetch('gestionar_permisos.php', {
 *     method: 'GET',
 *     body: new URLSearchParams({ accion: 'listar_permisos' }),
 *     headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
 * }).then(response => response.json()).then(data => console.log(data));
 *
 * Argumentos:
 * -----------
 * Entrada:
 * - `accion` (string) → Acción a realizar. Actualmente, solo admite "listar_permisos".
 * - `$_SESSION['user_id']` (int) → ID del usuario autenticado. Se usa para verificar permisos.
 *
 * Salida:
 * - `[{"id_permiso": 1, "nombre": "Editar Usuarios", "descripcion": "Permiso para modificar usuarios"}, ...]`
 *   → Lista de permisos en formato JSON.
 * - `"error: No tienes permiso para ver los permisos."` → Si el usuario no tiene permiso.
 * - `"error: Acción no válida."` → Si se recibe una acción inválida.
 * - `"error: Error en la operación: <mensaje>"` → Si ocurre un fallo en la base de datos.
 *
 * Módulos relacionados:
 * ---------------------
 * - `config.php` → Contiene la configuración de conexión a la base de datos.
 * - `verificar_permisos.php` → Contiene la función `usuarioTienePermiso()` para validar permisos.
 * - `permisos` (tabla) → Contiene la lista de permisos del sistema.
 *
 * Flujo de datos interno:
 * -----------------------
 * 1. Se inicia la sesión y se verifica que el usuario esté autenticado (`$_SESSION['user_id']`).
 * 2. Se recibe y valida la acción `accion` enviada en la petición.
 * 3. Si la acción es "listar_permisos":
 *    - Se verifica si el usuario tiene permiso para modificar roles (`usuarioTienePermiso("modificar_roles")`).
 *    - Si el usuario tiene permiso, se obtiene la lista de permisos desde la tabla `permisos`.
 *    - Se devuelve la lista en formato JSON.
 * 4. Si la acción no es válida, se devuelve un mensaje de error.
 */


session_start();
require 'config.php';
require 'verificar_permisos.php'; // Importar función para validar permisos

if (!isset($_SESSION['user_id'])) {
    die("Acceso no autorizado.");
}

$accion = $_POST['accion'] ?? $_GET['accion'] ?? null;

try {
    if ($accion === "listar_permisos") {
        // Verificar si el usuario tiene permiso para ver permisos
        if (!usuarioTienePermiso("modificar_roles")) {
            die("error: No tienes permiso para ver los permisos.");
        }

        // Obtener todos los permisos disponibles en el sistema
        $stmt = $pdo->query("SELECT id_permiso, nombre, descripcion FROM permisos");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } else {
        echo "error: Acción no válida.";
    }
} catch (PDOException $e) {
    echo "error: Error en la operación: " . $e->getMessage();
}
?>
