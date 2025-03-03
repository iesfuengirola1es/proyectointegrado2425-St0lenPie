<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    die("Acceso no autorizado.");
}

$grupo_id = $_GET['id'] ?? null;

if (!$grupo_id) {
    die("Error: Grupo no especificado.");
}

try {
    // Obtener usuarios del grupo
    $stmt = $pdo->prepare("SELECT id_usuario, nombre, email, id_rol FROM usuarios WHERE id_empresa = ?");
    $stmt->execute([$grupo_id]);
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener todos los roles disponibles
    $stmtRoles = $pdo->query("SELECT id_rol, nombre FROM roles");
    $roles = $stmtRoles->fetchAll(PDO::FETCH_ASSOC);

    // Obtener el creador del grupo
    $stmtCreador = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE id_empresa = ? ORDER BY id_usuario ASC LIMIT 1");
    $stmtCreador->execute([$grupo_id]);
    $creador = $stmtCreador->fetchColumn();
} catch (PDOException $e) {
    die("Error al obtener usuarios: " . $e->getMessage());
}
?>

<h2 class="titulo-seccion">Personas en el Grupo</h2>
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
                <select class="select-rol" id="rol_<?= $usuario['id_usuario'] ?>">
                    <?php foreach ($roles as $rol): ?>
                        <option value="<?= $rol['id_rol'] ?>" <?= ($usuario['id_rol'] == $rol['id_rol']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($rol['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td>
            <div class="acciones-usuario">
                <button class="btn-guardar" onclick="guardarCambioRol(<?= $usuario['id_usuario'] ?>)">💾 Guardar</button>
                <button class="btn-eliminar" onclick="eliminarUsuario(<?= $usuario['id_usuario'] ?>)">🗑 Eliminar</button>
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
</div>