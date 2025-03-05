<?php

/**
 * Módulo: Gestión de Usuarios en un Grupo
 * 
 * Este script permite visualizar, agregar y eliminar usuarios dentro de un grupo empresarial.
 * Solo los usuarios con el permiso "gestionar_personas" pueden acceder a esta sección.
 *
 * Ejemplo de llamada:
 * -------------------
 * fetch('personas.php', {
 *     method: 'GET',
 *     headers: { 'Content-Type': 'application/json' }
 * }).then(response => response.text()).then(data => console.log(data));
 *
 * Argumentos:
 * -----------
 * Entrada:
 * - `$_SESSION['user_id']` (int) → ID del usuario autenticado. Obligatorio.
 * - `$_SESSION['id_empresa']` (int) → ID del grupo al que pertenece el usuario. Obligatorio.
 *
 * Salida:
 * - Renderiza una página con la lista de usuarios en el grupo y un formulario para agregar usuarios.
 * - Muestra un mensaje de error si el usuario no tiene permisos suficientes.
 * - Si hay un error en la base de datos, se muestra el mensaje `Error al obtener usuarios: <mensaje>`.
 *
 * Módulos relacionados:
 * ---------------------
 * - `config.php` → Contiene la configuración de conexión a la base de datos.
 * - `verificar_permisos.php` → Contiene la función `usuarioTienePermiso()` para validar permisos.
 * - `usuarios` (tabla) → Almacena la información de los usuarios.
 * - `usuarios_grupos` (tabla) → Relaciona usuarios con grupos y roles.
 * - `empresa` (tabla) → Contiene los datos de los grupos empresariales.
 * - `roles` (tabla) → Contiene la lista de roles disponibles.
 *
 * Flujo de datos interno:
 * -----------------------
 * 1. Se inicia la sesión y se verifica que el usuario esté autenticado (`$_SESSION['user_id']`).
 * 2. Se valida que el usuario tenga el permiso "gestionar_personas".
 * 3. Se obtiene el ID de la empresa del usuario (`$_SESSION['id_empresa']`).
 * 4. Se consultan los usuarios del grupo y sus roles en la base de datos.
 * 5. Se consultan los roles disponibles en la base de datos.
 * 6. Se obtiene el ID del creador del grupo para restringir modificaciones sobre su cuenta.
 * 7. Se genera una tabla con los usuarios del grupo:
 *    - Muestra su nombre, correo y rol.
 *    - Si el usuario tiene permisos, puede cambiar roles y eliminar usuarios (excepto el creador).
 * 8. Se incluye un formulario emergente para agregar nuevos usuarios al grupo.
 * 9. Se retornan mensajes de error si el usuario no tiene permisos o si ocurre un fallo en la base de datos.
 */

session_start();
require 'config.php';
require 'verificar_permisos.php';

if (!isset($_SESSION['user_id'])) {
    die("Acceso no autorizado.");
}

// Verificar si el usuario tiene permiso para gestionar personas
if (!usuarioTienePermiso("gestionar_personas")) {
    die("
    <div class='error-container'>
        <h2>🚫 Acceso Denegado</h2>
        <p>No tienes permiso para gestionar usuarios.</p>
    </div>
    <link rel='stylesheet' href='../frontend/styles.css'>
    ");
}

$grupo_id = $_SESSION['id_empresa'] ?? null;

if (!$grupo_id) {
    die("Error: Grupo no especificado.");
}

try {
    // Obtener usuarios del grupo
    $stmt = $pdo->prepare("SELECT ug.id_usuario, u.nombre, u.email, ug.rol id_rol FROM usuarios_grupos ug inner join usuarios u on ug.id_usuario=u.id_usuario WHERE ug.id_empresa = ?");
    $stmt->execute([$grupo_id]);
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener todos los roles disponibles
    $stmtRoles = $pdo->query("SELECT id_rol, nombre FROM roles");
    $roles = $stmtRoles->fetchAll(PDO::FETCH_ASSOC);

    // Obtener el creador del grupo
    $stmtCreador = $pdo->prepare("
        SELECT id_creador 
        FROM empresa 
        WHERE id_empresa = ? 
       
    ");
    $stmtCreador->execute([$grupo_id]);
    $creador = $stmtCreador->fetchColumn();
} catch (PDOException $e) {
    die("Error al obtener usuarios: " . $e->getMessage());
}
?>

<h2 class="titulo-seccion">👥 Personas en el Grupo</h2>
<button class="btn-agregar" onclick="mostrarFormularioAgregar()">➕ Agregar Usuario</button>

<table class="tabla-estilo">
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Correo</th>
            <th>Rol</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($usuarios as $usuario): ?>
        <tr>
            <td><?= htmlspecialchars($usuario['nombre']) ?></td>
            <td><?= htmlspecialchars($usuario['email']) ?></td>
            <td>
                 <?php if ($usuario['id_usuario'] != $creador): // No permitir eliminar al creador ?>
                <select class="select-rol" id="rol_<?= $usuario['id_usuario'] ?>">
                    <?php foreach ($roles as $rol): ?>
                        <option value="<?= $rol['id_rol'] ?>" <?= ($usuario['id_rol'] == $rol['id_rol']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($rol['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                 <?php endif; ?>
            </td>
            <td>
                <div class="acciones-usuario">
                    

                    <?php if ($usuario['id_usuario'] != $creador): // No permitir eliminar al creador ?>
                        <button class="btn-guardar" onclick="guardarCambioRol(<?= $usuario['id_usuario'] ?>)">💾 Guardar</button>
                        <button class="btn-eliminar" onclick="eliminarUsuario(<?= $usuario['id_usuario'] ?>)">🗑 Eliminar</button>
                    <?php else: ?>
                       <span class="rol-protegido">🔒 Usuario Creador Protegido</span>
                    <?php endif; ?>
                </div>
            </td>
            
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>



<!-- Formulario para agregar usuarios -->
<div id="formularioAgregar" class="form-emergente">
    <h3>Agregar Usuario</h3>
    <div class="input-group">
        <label for="buscarUsuario">Buscar usuario por email</label>
        <input type="text" id="buscarUsuario" placeholder="Ejemplo: usuario@email.com">

        <label for="usuarioSeleccionado">Seleccionar usuario</label>
        <select id="usuarioSeleccionado">
            <option value="">Seleccionar usuario...</option>
        </select>
    </div>
    <button class="btn-guardar" onclick="agregarUsuario()">📩 Agregar</button>
    <button class="btn-cancelar" onclick="cerrarFormularioAgregar()">❌ Cancelar</button>
    <div id="mensajeRespuesta" class="mensaje" style="display: none;"></div>
</div>