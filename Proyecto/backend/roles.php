<?php

/**
 * Módulo: Gestión de Roles y Permisos
 * 
 * Este script permite a los administradores visualizar, crear, editar y eliminar roles en el sistema.
 * Solo los usuarios con el permiso "modificar_roles" pueden acceder a esta sección.
 *
 * Ejemplo de llamada:
 * -------------------
 * fetch('roles.php', {
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
 * - Renderiza una página con una lista de roles y sus permisos.
 * - Incluye opciones para editar y eliminar roles, excepto aquellos protegidos como "Administrador" y "Usuario Nuevo".
 * - Si el usuario no tiene permisos suficientes, muestra un mensaje de acceso denegado.
 * - Si ocurre un error en la base de datos, se muestra el mensaje `Error al obtener datos: <mensaje>`.
 *
 * Módulos relacionados:
 * ---------------------
 * - `config.php` → Contiene la configuración de conexión a la base de datos.
 * - `verificar_permisos.php` → Contiene la función `usuarioTienePermiso()` para validar permisos.
 * - `roles` (tabla) → Contiene la lista de roles disponibles en el sistema.
 * - `permisos` (tabla) → Contiene la lista de permisos del sistema.
 * - `roles_permisos` (tabla) → Relaciona roles con permisos específicos.
 *
 * Flujo de datos interno:
 * -----------------------
 * 1. Se inicia la sesión y se verifica que el usuario esté autenticado (`$_SESSION['user_id']`).
 * 2. Se valida que el usuario tenga el permiso "modificar_roles" para acceder a la gestión de roles.
 * 3. Se consultan los roles existentes en la base de datos.
 * 4. Se consultan los permisos disponibles en la base de datos.
 * 5. Se genera una tabla con los roles registrados:
 *    - Muestra el nombre del rol.
 *    - Si el usuario tiene permisos, puede editar y eliminar roles (excepto los protegidos).
 * 6. Se incluye un formulario emergente para crear o editar roles con selección de permisos.
 * 7. Se retornan mensajes de error si el usuario no tiene permisos o si ocurre un fallo en la base de datos.
 */

session_start();
require 'config.php';
require 'verificar_permisos.php';

// Verificar si el usuario tiene permiso para modificar roles
if (!usuarioTienePermiso("modificar_roles")) {
    die("
    <div class='error-container'>
        <h2>🚫 Acceso Denegado</h2>
        <p>No tienes permiso para gestionar roles.</p>
    </div>
    <link rel='stylesheet' href='../frontend/styles.css'>
    ");
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

<h2 class="titulo-seccion">🎭 Gestión de Roles</h2>

<?php if (usuarioTienePermiso("crear_roles")): ?>
    <button class="btn-agregar" onclick="mostrarFormularioRol()">➕ Crear Rol</button>
<?php endif; ?>

<!-- Tabla de roles -->
<table class="tabla-estilo">
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
                    <button class="btn-editar" onclick="editarRol(<?= $rol['id_rol'] ?>)">✏️ Editar</button>
                    <button class="btn-eliminar" onclick="eliminarRol(<?= $rol['id_rol'] ?>)">🗑 Eliminar</button>
                <?php else: ?>
                    <span class="rol-protegido">🔒 Rol Protegido</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Formulario para crear o editar roles -->
<div id="formularioRol" class="form-emergente">
    <h3 id="tituloFormularioRol">Añadir Rol</h3>
    <div class="input-group">
        <input type="hidden" id="rolID">

        <label for="nombreRol">Nombre del Rol</label>
        <input type="text" id="nombreRol" placeholder="Ejemplo: Gerente">

        <h4>Seleccionar Permisos:</h4>
        <div id="listaPermisos">
            <?php foreach ($permisos as $permiso): ?>
                <label class="permiso-label">
                    <input type="checkbox" class="permisoCheckbox" value="<?= $permiso['id_permiso'] ?>"> 
                    <?= htmlspecialchars($permiso['nombre']) ?> - <?= htmlspecialchars($permiso['descripcion']) ?>
                </label><br>
            <?php endforeach; ?>
        </div>
    </div>
    
    <button class="btn-guardar" onclick="guardarRol()">💾 Guardar</button>
    <button class="btn-cancelar" onclick="cerrarFormularioRol()">❌ Cancelar</button>
</div>
