<?php

/**
 * Módulo: Visualización de Permisos
 * 
 * Este script permite a los usuarios con el permiso adecuado visualizar una lista de permisos predefinidos en el sistema.
 *
 * Ejemplo de llamada:
 * -------------------
 * fetch('permisos.php', {
 *     method: 'GET',
 *     headers: { 'Content-Type': 'application/json' }
 * }).then(response => response.text()).then(data => console.log(data));
 *
 * Argumentos:
 * -----------
 * Entrada:
 * - `$_SESSION['user_id']` (int) → ID del usuario autenticado. Obligatorio.
 * - Se requiere que el usuario tenga el permiso "modificar_roles" para acceder.
 *
 * Salida:
 * - Renderiza una tabla HTML con la lista de permisos disponibles en el sistema.
 * - Si el usuario no tiene permisos suficientes, se muestra el mensaje `Acceso denegado: No tienes permiso para ver los permisos.`
 * - Si ocurre un error en la base de datos, se muestra `Error al obtener permisos: <mensaje>`.
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
 * 2. Se valida que el usuario tenga el permiso "modificar_roles" para visualizar los permisos.
 * 3. Se consulta la base de datos para obtener todos los permisos predefinidos.
 * 4. Se generan dinámicamente filas en una tabla HTML con los permisos y sus descripciones.
 * 5. En caso de error en la base de datos, se muestra un mensaje de error.
 */

session_start();
require 'config.php';
require 'verificar_permisos.php'; // Importar función para validar permisos

if (!isset($_SESSION['user_id'])) {
    die("Acceso no autorizado.");
}

// Verificar si el usuario tiene permiso para ver permisos
if (!usuarioTienePermiso("modificar_roles")) {
    die("Acceso denegado: No tienes permiso para ver los permisos.");
}

try {
    // Obtener todos los permisos predefinidos
    $stmt = $pdo->query("SELECT id_permiso, nombre, descripcion FROM permisos");
    $permisos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al obtener permisos: " . $e->getMessage());
}
?>

<h2>Permisos Disponibles</h2>

<table>
    <thead>
        <tr>
            <th>Nombre del Permiso</th>
            <th>Descripción</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($permisos as $permiso): ?>
        <tr>
            <td><?= htmlspecialchars($permiso['nombre']) ?></td>
            <td><?= htmlspecialchars($permiso['descripcion']) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
