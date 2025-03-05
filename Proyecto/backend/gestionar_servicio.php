<?php

/**
 * Módulo: Gestión de Servicios
 * 
 * Este script permite la gestión de servicios en la base de datos. Los usuarios con permisos adecuados pueden crear,
 * editar, eliminar y obtener información sobre servicios de una empresa.
 *
 * Ejemplo de llamada:
 * -------------------
 * fetch('gestionar_servicio.php', {
 *     method: 'POST',
 *     body: new URLSearchParams({ accion: 'crear', nombre: 'Mantenimiento', descripcion: 'Servicio técnico', precio: 100, id_empresa: 1 }),
 *     headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
 * }).then(response => response.text()).then(data => console.log(data));
 *
 * Argumentos:
 * -----------
 * Entrada:
 * - `accion` (string) → Acción a realizar. Posibles valores:
 *   - "crear" → Agregar un nuevo servicio.
 *   - "editar" → Modificar un servicio existente.
 *   - "eliminar" → Eliminar un servicio de la base de datos.
 *   - "obtener" → Obtener información de un servicio específico.
 * - `id_servicio` (int) → ID del servicio a gestionar (requerido para "editar", "eliminar" y "obtener").
 * - `nombre` (string) → Nombre del servicio. Obligatorio para "crear" y "editar".
 * - `descripcion` (string) → Descripción del servicio (opcional).
 * - `precio` (float) → Precio del servicio. Obligatorio para "crear" y "editar".
 * - `id_empresa` (int) → ID de la empresa a la que pertenece el servicio. Se obtiene de la sesión.
 * - `$_SESSION['user_id']` (int) → ID del usuario autenticado, requerido para validar permisos.
 *
 * Salida:
 * - `success: Servicio agregado exitosamente.` → Si el servicio se crea correctamente.
 * - `success: Servicio actualizado correctamente.` → Si el servicio se edita correctamente.
 * - `success: Servicio eliminado correctamente.` → Si el servicio se elimina correctamente.
 * - `{"id_servicio": 1, "nombre": "Mantenimiento", "descripcion": "Servicio técnico", "precio": 100, "id_empresa": 1}` 
 *   → Si se obtiene un servicio en formato JSON.
 * - `error: Todos los campos son obligatorios.` → Si falta información en "crear" o "editar".
 * - `error: Ya existe un servicio con este nombre en esta empresa.` → Si el nombre ya está en uso.
 * - `error: No tienes permiso para realizar esta acción.` → Si el usuario no tiene permisos suficientes.
 * - `error: Servicio no encontrado.` → Si no se encuentra el servicio al obtenerlo.
 * - `error: Acción no válida.` → Si la acción recibida no es reconocida.
 * - `error: Error en la operación: <mensaje>` → Si ocurre un fallo en la base de datos.
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
 * 2. Se recibe y valida la acción `accion` enviada en la petición.
 * 3. Dependiendo de la acción recibida:
 *    - **"crear"**:
 *       - Verifica permisos.
 *       - Comprueba que el nombre del servicio no esté duplicado en la misma empresa.
 *       - Inserta el nuevo servicio en la base de datos.
 *    - **"editar"**:
 *       - Verifica permisos.
 *       - Comprueba que el nuevo nombre no esté duplicado en la empresa.
 *       - Actualiza los datos del servicio en la base de datos.
 *    - **"eliminar"**:
 *       - Verifica permisos.
 *       - Elimina el servicio de la base de datos.
 *    - **"obtener"**:
 *       - Recupera y devuelve la información del servicio en formato JSON.
 * 4. Se retornan mensajes en formato JSON indicando éxito o error en la operación.
 */

session_start();
require 'config.php';
require 'verificar_permisos.php';

if (!isset($_SESSION['user_id'])) {
    die("Acceso no autorizado.");
}

$accion = $_POST['accion'] ?? $_GET['accion'] ?? null;
$id_servicio = $_POST['id'] ?? $_GET['id'] ?? null;
$nombre = trim($_POST['nombre'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$precio = $_POST['precio'] ?? null;
$id_empresa = $_SESSION['id_empresa'] ?? null;

try {
    if ($accion === "crear" || $accion === "editar") {
        if (empty($nombre) || empty($precio)) {
             die("error: Todos los campos son obligatorios.");
        }

        // **Validación de nombre duplicado**
        if ($accion === "crear") {
           

            $stmt = $pdo->prepare("SELECT COUNT(*) FROM servicios WHERE nombre = ? AND id_empresa = ?");
            $stmt->execute([$nombre, $id_empresa]);
        } else {
           
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM servicios WHERE nombre = ? AND id_empresa = ? AND id_servicio != ?");
            $stmt->execute([$nombre, $id_empresa, $id_servicio]);
        }

        $existe = $stmt->fetchColumn();

        if ($existe > 0) {
            die("error: Ya existe un servicio con este nombre en esta empresa.");
        }
    }

    if ($accion === "crear") {
         if (!usuarioTienePermiso("crear_servicios")) {
                die("error: No tienes permiso para crear servicios.");
            }
        $stmt = $pdo->prepare("INSERT INTO servicios (nombre, descripcion, precio, id_empresa) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nombre, $descripcion, $precio, $id_empresa]);
        echo "success: Servicio agregado exitosamente.";


    } elseif ($accion === "editar" && $id_servicio) {
         if (!usuarioTienePermiso("editar_servicios")) {
                die("error: No tienes permiso para editar servicios.");
            }
        $stmt = $pdo->prepare("UPDATE servicios SET nombre=?, descripcion=?, precio=? WHERE id_servicio=?");
        $stmt->execute([$nombre, $descripcion, $precio, $id_servicio]);
        echo "success: Servicio actualizado correctamente.";


    } elseif ($accion === "eliminar" && $id_servicio) {
         if (!usuarioTienePermiso("eliminar_servicios")) {
                die("error: No tienes permiso para eliminar servicios.");
            }
        $stmt = $pdo->prepare("DELETE FROM servicios WHERE id_servicio = ?");
        $stmt->execute([$id_servicio]);
        echo "success: Servicio eliminado correctamente.";


    } elseif ($accion === "obtener" && $id_servicio) {
        $stmt = $pdo->prepare("SELECT * FROM servicios WHERE id_servicio = ?");
        $stmt->execute([$id_servicio]);
        $servicio = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($servicio) {
            echo json_encode($servicio);
        } else {
            echo "error: Servicio no encontrado.";
        }
    } else {
        echo "error: Acción no válida.";
    }
} catch (PDOException $e) {
    echo "error: Error en la operación: " . $e->getMessage();
}
?>
