<?php
session_start();
require 'config.php';
require 'verificar_permisos.php';

if (!isset($_SESSION['user_id'])) {
    die("Acceso no autorizado.");
}

$grupo_id = $_GET['id'] ?? null;

if (!$grupo_id) {
    die("Error: Grupo no especificado.");
}

try {
    // Obtener productos del grupo
    $stmt = $pdo->prepare("SELECT id_producto, nombre, descripcion, precio, stock, nivel_minimo, unidades_vendidas FROM productos WHERE id_empresa = ?");
    $stmt->execute([$grupo_id]);
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener productos con stock bajo
    $stmtAlertas = $pdo->prepare("SELECT nombre FROM productos WHERE id_empresa = ? AND stock <= nivel_minimo");
    $stmtAlertas->execute([$grupo_id]);
    $alertas = $stmtAlertas->fetchAll(PDO::FETCH_ASSOC);

    // Obtener usuarios del grupo
    $stmtUsuarios = $pdo->prepare("
        SELECT u.id_usuario, u.nombre, ug.rol 
        FROM usuarios u
        INNER JOIN usuarios_grupos ug ON u.id_usuario = ug.id_usuario
        WHERE ug.id_empresa = ?
    ");
    $stmtUsuarios->execute([$grupo_id]);
    $usuarios = $stmtUsuarios->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error al obtener datos: " . $e->getMessage());
}
?>

<h2>Inventario</h2>

<?php if (usuarioTienePermiso("crear_articulos")): ?>
    <button id="btnAgregarProducto" onclick="mostrarFormulario()">➕ Añadir Producto</button>
<?php endif; ?>

<table>
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Descripción</th>
            <th>Precio (€)</th>
            <th>Stock</th>
            <th>Nivel Mínimo</th>
            <th>Unidades Vendidas</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($productos as $producto): ?>
        <tr>
            <td><?= htmlspecialchars($producto['nombre']) ?></td>
            <td><?= htmlspecialchars($producto['descripcion']) ?></td>
            <td><?= number_format($producto['precio'], 2) ?> €</td>
            <td><?= htmlspecialchars($producto['stock']) ?></td>
            <td><?= htmlspecialchars($producto['nivel_minimo']) ?></td>
            <td>
                <?php if (usuarioTienePermiso("gestionar_unidades")): ?>
                    <input type="number" id="unidadesVendidas_<?= $producto['id_producto'] ?>" 
                           value="<?= $producto['unidades_vendidas'] ?>" min="0">
                    <button onclick="actualizarUnidadesVendidas(<?= $producto['id_producto'] ?>)">Actualizar</button>
                <?php else: ?>
                    <?= htmlspecialchars($producto['unidades_vendidas']) ?>
                <?php endif; ?>
            </td>
            <td>
                <?php if (usuarioTienePermiso("editar_articulos")): ?>
                    <button onclick="editarProducto(<?= $producto['id_producto'] ?>)">✏️ Editar</button>
                <?php endif; ?>
                <?php if (usuarioTienePermiso("eliminar_articulos")): ?>
                    <button onclick="eliminarProducto(<?= $producto['id_producto'] ?>)">🗑 Eliminar</button>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>


<h3>📢 Alertas de Stock Bajo</h3>
<div class="alertas-container">
    <?php if (!empty($alertas)): ?>
        <ul>
            <?php foreach ($alertas as $alerta): ?>
                <li>⚠️ El artículo <strong><?= htmlspecialchars($alerta['nombre']) ?></strong> está bajo en stock!</li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No hay alertas de stock bajo en este momento.</p>
    <?php endif; ?>
</div>

<!-- Contenedor para mostrar mensajes -->
<div id="mensajeRespuesta" class="mensaje" style="display: none;"></div>

<!-- Formulario para agregar o editar productos -->
<div id="formularioProducto" style="display: none;">
    <h3 id="tituloFormulario">Añadir Producto</h3>
    <input type="hidden" id="productoID">
    <input type="text" id="nombreProducto" placeholder="Nombre">
    <input type="text" id="descripcionProducto" placeholder="Descripción">
    <input type="number" id="precioProducto" placeholder="Precio (€)" step="0.01">
    <input type="number" id="stockProducto" placeholder="Stock">
    <input type="number" id="nivelMinimoProducto" placeholder="Nivel Mínimo">
    <input type="number" id="unidadesVendidasProducto" placeholder="Unidades Vendidas" min="0">
    <button id="botonGuardarProducto">💾 Guardar</button>
    <button onclick="cerrarFormulario()">❌ Cancelar</button>
</div>
