<?php

/**
 * Módulo: Gestión de Servicios
 * 
 * Este script permite a los usuarios con permisos adecuados visualizar, agregar, editar y eliminar servicios en un grupo empresarial.
 *
 * Ejemplo de llamada:
 * -------------------
 * fetch('servicios.php?id_empresa=1', {
 *     method: 'GET',
 *     headers: { 'Content-Type': 'application/json' }
 * }).then(response => response.text()).then(data => console.log(data));
 *
 * Argumentos:
 * -----------
 * Entrada:
 * - `$_SESSION['user_id']` (int) → ID del usuario autenticado. Obligatorio.
 * - `id_empresa` (int) → ID del grupo empresarial. Obligatorio.
 * - Se requiere que el usuario tenga al menos uno de los permisos: "crear_servicios", "editar_servicios" o "eliminar_servicios".
 *
 * Salida:
 * - Renderiza una tabla con la lista de servicios del grupo.
 * - Incluye opciones para agregar, editar y eliminar servicios si el usuario tiene los permisos correspondientes.
 * - Si el usuario no tiene permisos suficientes, muestra un mensaje de acceso denegado.
 * - Si ocurre un error en la base de datos, se muestra el mensaje `Error al obtener servicios: <mensaje>`.
 *
 * Módulos relacionados:
 * ---------------------
 * - `config.php` → Contiene la configuración de conexión a la base de datos.
 * - `verificar_permisos.php` → Contiene la función `usuarioTienePermiso()` para validar permisos.
 * - `servicios` (tabla) → Contiene la información de los servicios de cada empresa.
 *
 * Flujo de datos interno:
 * -----------------------
 * 1. Se inicia la sesión y se verifica que el usuario esté autenticado (`$_SESSION['user_id']`).
 * 2. Se valida que el usuario tenga al menos uno de los permisos necesarios para gestionar servicios.
 * 3. Se recibe y valida el parámetro `id_empresa` de la URL.
 * 4. Se consultan los servicios existentes en la base de datos.
 * 5. Se genera una tabla con los servicios registrados:
 *    - Muestra el nombre, la descripción y el precio de cada servicio.
 *    - Si el usuario tiene permisos, puede agregar, editar y eliminar servicios.
 * 6. Se incluye un formulario emergente para agregar o editar servicios.
 * 7. Se retornan mensajes de error si el usuario no tiene permisos o si ocurre un fallo en la base de datos.
 */

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
  <?php if (usuarioTienePermiso("crear_servicios")): ?>
<button class="btn-agregar" onclick="mostrarFormularioServicio()">➕ Añadir Servicio</button>
   <?php endif; ?>
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
              
               <?php if (usuarioTienePermiso("editar_servicios")): ?>
               
                    <button class="btn-editar"  onclick="editarServicio(<?= $servicio['id_servicio'] ?>)">✏️ Editar</button>
               <?php endif; ?>

               <?php if (usuarioTienePermiso("eliminar_servicios")): ?>
                    
                    <button class="btn-eliminar" onclick="eliminarServicio(<?= $servicio['id_servicio'] ?>)">🗑 Eliminar</button>
                <?php endif; ?>
               
              
               
                
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
    <div id="mensajeRespuesta" class="mensaje" style="display: none;"></div>
</div>
