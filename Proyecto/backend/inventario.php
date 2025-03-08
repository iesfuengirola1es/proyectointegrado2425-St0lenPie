<?php

/**
 * Módulo: Gestión del Inventario
 * 
 * Este script genera la interfaz de gestión del inventario para una empresa.
 * Permite a los usuarios con permisos adecuados visualizar, agregar, editar y eliminar productos,
 * además de gestionar unidades vendidas y recibir alertas de stock bajo.
 *
 * Ejemplo de llamada:
 * -------------------
 * fetch('inventario.php', {
 *     method: 'GET',
 *     headers: { 'Content-Type': 'application/json' }
 * }).then(response => response.text()).then(data => console.log(data));
 *
 * Argumentos:
 * -----------
 * Entrada:
 * - `$_SESSION['user_id']` (int) → ID del usuario autenticado, requerido para validar permisos.
 * - `$_SESSION['id_empresa']` (int) → ID del grupo al que pertenece el usuario, necesario para cargar el inventario.
 *
 * Salida:
 * - Renderiza una página con la tabla de productos y controles de gestión.
 * - Incluye alertas de stock bajo si existen productos con stock menor o igual al nivel mínimo.
 * - Muestra opciones de edición y eliminación solo si el usuario tiene los permisos correspondientes.
 * - Muestra un formulario para agregar o editar productos si el usuario tiene permiso.
 * - Incluye un sistema de actualización de unidades vendidas para cada producto.
 *
 * Módulos relacionados:
 * ---------------------
 * - `config.php` → Contiene la configuración de conexión a la base de datos.
 * - `verificar_permisos.php` → Contiene la función `usuarioTienePermiso()` para validar permisos.
 * - `productos` (tabla) → Contiene la información de los productos.
 * - `usuarios_grupos` (tabla) → Relaciona usuarios con grupos y roles.
 * - `empresa` (tabla) → Contiene los datos de los grupos empresariales.
 *
 * Flujo de datos interno:
 * -----------------------
 * 1. Se inicia la sesión y se verifica que el usuario esté autenticado (`$_SESSION['user_id']`).
 * 2. Se valida que el usuario tenga al menos un permiso de gestión de inventario.
 * 3. Se obtiene el ID de la empresa del usuario (`$_SESSION['id_empresa']`).
 * 4. Se consultan los productos de la empresa desde la base de datos.
 * 5. Se consultan los productos con stock bajo y se generan alertas si es necesario.
 * 6. Se obtienen los usuarios del grupo y sus roles.
 * 7. Se renderiza una tabla de inventario con opciones de:
 *    - Visualización de productos.
 *    - Edición de productos (si el usuario tiene permiso).
 *    - Eliminación de productos (si el usuario tiene permiso).
 *    - Actualización de unidades vendidas.
 * 8. Se muestra un formulario para agregar o editar productos.
 * 9. Se incluye una sección de alertas de stock bajo.
 */

session_start();
require 'config.php';
require 'verificar_permisos.php';

if (!isset($_SESSION['user_id'])) {
    die("Acceso no autorizado.");
}

// Verificar si el usuario tiene al menos un permiso de inventario
if (!usuarioTienePermiso("crear_articulos") && 
    !usuarioTienePermiso("editar_articulos") && 
    !usuarioTienePermiso("eliminar_articulos") && 
    !usuarioTienePermiso("gestionar_unidades")) {
    die("<div class='error-container'>
        <h2>🚫 Acceso Denegado</h2>
        <p>No tienes permiso para gestionar el inventario.</p>
    </div>
    <link rel='stylesheet' href='../frontend/styles.css'>
    ");
}

$grupo_id = $_SESSION['id_empresa'] ?? null;

if (!$grupo_id) {
    die("Error: Grupo no especificado.");
}

try {
    // Obtener productos del grupo
    $stmt = $pdo->prepare("SELECT id_producto, nombre, descripcion, precio, stock, nivel_minimo, unidades_vendidas FROM productos WHERE id_empresa = ?");
    $stmt->execute([$grupo_id]);
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener productos con stock bajo
    $stmtAlertas = $pdo->prepare("SELECT nombre FROM productos WHERE id_empresa = ? AND (stock) <= nivel_minimo");
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

<h2 class="titulo-inventario">📦 Inventario</h2>

<?php if (usuarioTienePermiso("crear_articulos")): ?>
    <button class="btn-agregar" onclick="mostrarFormulario()">➕ Añadir Producto</button>
<?php endif; ?>

<table class="tabla-inventario">
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
                    <button class="btn-guardar" onclick="actualizarUnidadesVendidas(<?= $producto['id_producto'] ?>)">Actualizar</button>
                <?php else: ?>
                    <?= htmlspecialchars($producto['unidades_vendidas']) ?>
                <?php endif; ?>
            </td>
            <td>
                <?php if (usuarioTienePermiso("editar_articulos")): ?>
                    <button class="btn-editar" onclick="editarProducto(<?= $producto['id_producto'] ?>)">✏️ Editar</button>
                <?php endif; ?>
                <?php if (usuarioTienePermiso("eliminar_articulos")): ?>
                    <button class="btn-eliminar" onclick="eliminarProducto(<?= $producto['id_producto'] ?>)">🗑 Eliminar</button>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>


<!-- Panel de alertas -->
<div class="alertas-container">
    <h3 class="titulo-alertas">📢 Alertas de Stock Bajo</h3>
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
<div id="formularioProducto" class="form-producto">
    <h3 id="tituloFormulario">Añadir Producto</h3>
    <div class="input-group">
        <input type="hidden" id="productoID">

        <label for="nombreProducto">Nombre del Producto</label>
        <input type="text" id="nombreProducto" placeholder="Nombre del Producto">
        <label for="descripcionProducto">Descripción</label>
        <input type="text" id="descripcionProducto" placeholder="Descripción">
        <label for="precioProducto">Precio (€)</label>
        <input type="number" id="precioProducto" placeholder="Precio (€)" step="0.01">
        <label for="stockProducto">Stock</label>
        <input type="number" id="stockProducto" placeholder="Stock">
        <label for="nivelMinimoProducto">Nivel Mínimo</label>
        <input type="number" id="nivelMinimoProducto" placeholder="Nivel Mínimo">
        <label for="unidadesVendidasProducto">Unidades Vendidas</label>
        <input type="number" id="unidadesVendidasProducto" placeholder="Unidades Vendidas" min="0">
    </div>
    <button class="btn-guardar"  id="botonGuardarProducto" onclick="guardarProducto()">💾 Guardar</button>
    <button class="btn-cancelar" onclick="cerrarFormulario()">❌ Cancelar</button>
    <!-- Contenedor para mostrar mensajes -->
<div id="mensajeRespuestaEditar" class="mensaje" style="display: none;"></div>
</div>
