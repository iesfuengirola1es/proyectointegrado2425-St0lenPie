<?php
session_start();
require 'config.php';
require 'verificar_permisos.php';

if (!isset($_SESSION['user_id'])) {
    die("Acceso no autorizado.");
}

// Verificar si el usuario tiene al menos un permiso relacionado con servicios
if (!usuarioTienePermiso("crear_servicios") && 
    !usuarioTienePermiso("editar_servicios") && 
    !usuarioTienePermiso("eliminar_servicios")) {
    die("
    <div class='error-container'>
        <h2>🚫 Acceso Denegado</h2>
        <p>No tienes permiso para gestionar servicios.</p>
    </div>
    <link rel='stylesheet' href='../frontend/styles.css'>
    ");
}

$grupo_id = $_GET['id_empresa'] ?? null;

if (!$grupo_id) {
    die("Error: Grupo no especificado.");
}

try {
    $stmt = $pdo->prepare("SELECT id_servicio, nombre, descripcion, precio FROM servicios WHERE id_empresa = ?");
    $stmt->execute([$grupo_id]);
    $servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al obtener servicios: " . $e->getMessage());
}
?>

<h2 class="titulo-servicios">🛠 Servicios</h2>
<button class="btn-agregar" onclick="mostrarFormularioServicio()">➕ Añadir Servicio</button>

<!-- Tabla de servicios -->
<table class="tabla-servicios">
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Descripción</th>
            <th>Precio (€)</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($servicios as $servicio): ?>
        <tr>
            <td><?= htmlspecialchars($servicio['nombre']) ?></td>
            <td><?= htmlspecialchars($servicio['descripcion']) ?></td>
            <td><?= number_format($servicio['precio'], 2) ?> €</td>
            <td>
                <button class="btn-editar" onclick="editarServicio(<?= $servicio['id_servicio'] ?>)">✏️ Editar</button>
                <button class="btn-eliminar" onclick="eliminarServicio(<?= $servicio['id_servicio'] ?>)">🗑 Eliminar</button>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Formulario para agregar o editar servicios -->
<div id="formularioServicio" class="form-servicio">
    <h3 id="tituloFormularioServicio">Añadir Servicio</h3>
    <div class="input-group">
        <input type="hidden" id="servicioID">

        <label for="nombreServicio">Nombre del Servicio</label>
        <input type="text" id="nombreServicio" placeholder="Nombre del servicio">

        <label for="descripcionServicio">Descripción</label>
        <input type="text" id="descripcionServicio" placeholder="Descripción del servicio">

        <label for="precioServicio">Precio (€)</label>
        <input type="number" id="precioServicio" placeholder="Precio del servicio" step="0.01">
    </div>
    
    <button class="btn-guardar" onclick="guardarServicio()">💾 Guardar</button>
    <button class="btn-cancelar" onclick="cerrarFormularioServicio()">❌ Cancelar</button>
</div>
