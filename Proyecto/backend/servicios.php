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
    $stmt = $pdo->prepare("SELECT id_servicio, nombre, descripcion, precio FROM servicios WHERE id_empresa = ?");
    $stmt->execute([$grupo_id]);
    $servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al obtener servicios: " . $e->getMessage());
}
?>

<h2>Servicios</h2>
<button onclick="mostrarFormularioServicio()">➕ Añadir Servicio</button>

<table>
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
                <button onclick="editarServicio(<?= $servicio['id_servicio'] ?>)">✏️ Editar</button>
                <button onclick="eliminarServicio(<?= $servicio['id_servicio'] ?>)">🗑 Eliminar</button>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Formulario para agregar o editar servicios -->
<div id="formularioServicio" style="display: none;">
    <h3 id="tituloFormularioServicio">Añadir Servicio</h3>
    <input type="hidden" id="servicioID">
    <input type="text" id="nombreServicio" placeholder="Nombre">
    <input type="text" id="descripcionServicio" placeholder="Descripción">
    <input type="number" id="precioServicio" placeholder="Precio (€)" step="0.01">
    <button onclick="guardarServicio()">💾 Guardar</button>
    <button onclick="cerrarFormularioServicio()">❌ Cancelar</button>
</div>
