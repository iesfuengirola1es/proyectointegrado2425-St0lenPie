<?php

/**
 * Módulo: Eliminación de Grupo Empresarial
 * 
 * Este script permite a un usuario autenticado eliminar un grupo empresarial en la base de datos.
 * Solo el creador del grupo tiene permisos para eliminarlo.
 *
 * Ejemplo de llamada:
 * -------------------
 * fetch('eliminar_grupo.php', {
 *     method: 'POST',
 *     body: new URLSearchParams({ id_empresa: 1 }),
 *     headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
 * }).then(response => response.text()).then(data => console.log(data));
 *
 * Argumentos:
 * -----------
 * Entrada:
 * - `POST['id_empresa']` (int) → ID del grupo a eliminar. Obligatorio.
 * - `$_SESSION['user_id']` (int) → ID del usuario autenticado. Solo el creador puede eliminar el grupo.
 *
 * Salida:
 * - `success: Grupo eliminado correctamente.` → Si la eliminación es exitosa.
 * - `error: No tienes permisos para eliminar este grupo.` → Si el usuario no es el creador.
 * - `error: ID de empresa no proporcionado.` → Si no se envió un `id_empresa` válido.
 * - `error: Error al eliminar el grupo: <mensaje>` → Si ocurre un fallo en la base de datos.
 *
 * Módulos relacionados:
 * ---------------------
 * - `config.php` → Contiene la configuración de conexión a la base de datos.
 * - `usuarios_grupos` (tabla) → Relaciona usuarios con grupos.
 * - `empresa` (tabla) → Contiene los datos de los grupos empresariales.
 *
 * Flujo de datos interno:
 * -----------------------
 * 1. Se inicia la sesión para validar la autenticación del usuario.
 * 2. Se verifica que `$_SESSION['user_id']` esté definido.
 * 3. Se recibe y valida `POST['id_empresa']`.
 * 4. Se consulta en la base de datos si el usuario tiene el rol de "creador" en el grupo.
 * 5. Si el usuario no es el creador, se devuelve un error de permisos.
 * 6. Se eliminan todas las relaciones del grupo en la tabla `usuarios_grupos`.
 * 7. Se elimina el grupo de la tabla `empresa`.
 * 8. Se devuelve un mensaje de éxito o error según el resultado.
 */


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
