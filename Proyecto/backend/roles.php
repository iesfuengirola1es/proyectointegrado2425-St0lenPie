<?php
session_start();
require 'config.php';
require 'verificar_permisos.php';

// Verificar si el usuario tiene permiso para modificar roles
if (!usuarioTienePermiso("modificar_roles")) {
    die("Acceso denegado: No tienes permiso para gestionar roles.");
}

try {
    // Obtener roles existentes
    $stmt = $pdo->query("SELECT id_rol, nombre FROM roles");
    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener permisos predefinidos
    $stmtPermisos = $pdo->query("SELECT id_permiso, nombre, descripcion FROM permisos");
    $permisos = $stmtPermisos->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al obtener datos: " . $e->getMessage());
}
?>

<h2>Gestión de Roles</h2>

<?php if (usuarioTienePermiso("crear_roles")): ?>
    <button onclick="mostrarFormularioRol()">➕ Crear Rol</button>
<?php endif; ?>

<table>
    <thead>
        <tr>
            <th>Nombre del Rol</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($roles as $rol): ?>
        <tr>
            <td><?= htmlspecialchars($rol['nombre']) ?></td>
            <td>
                <?php if ($rol['id_rol'] != 1 && $rol['id_rol'] != 2): // No mostrar botones para "Administrador" y "Usuario Nuevo" ?>
                    <button onclick="editarRol(<?= $rol['id_rol'] ?>)">✏️ Editar</button>
                    <button onclick="eliminarRol(<?= $rol['id_rol'] ?>)">🗑 Eliminar</button>
                <?php else: ?>
                    🔒 Rol Protegido
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>


<!-- Formulario para crear o editar roles -->
<div id="formularioRol" style="display: none;">
    <h3 id="tituloFormularioRol">Añadir Rol</h3>
    <input type="hidden" id="rolID">
    <input type="text" id="nombreRol" placeholder="Nombre del Rol">
    
    <h4>Seleccionar Permisos:</h4>
    <div id="listaPermisos">
        <?php foreach ($permisos as $permiso): ?>
            <label>
                <input type="checkbox" class="permisoCheckbox" value="<?= $permiso['id_permiso'] ?>"> 
                <?= htmlspecialchars($permiso['nombre']) ?> - <?= htmlspecialchars($permiso['descripcion']) ?>
            </label><br>
        <?php endforeach; ?>
    </div>
    
    <button onclick="guardarRol()">💾 Guardar</button>
    <button onclick="cerrarFormularioRol()">❌ Cancelar</button>
</div>
