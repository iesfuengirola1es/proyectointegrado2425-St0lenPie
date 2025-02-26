<?php
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
