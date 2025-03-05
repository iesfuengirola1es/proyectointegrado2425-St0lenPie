<?php

/**
 * Módulo: Creación de Grupo Empresarial
 * --------------------------------------
 * Este script permite a un usuario autenticado crear un nuevo grupo empresarial en la base de datos.
 * Genera una clave de acceso única y asigna automáticamente al usuario creador el rol de administrador dentro del grupo.
 *
 * Ejemplo de llamada:
 * -------------------
 * fetch('crear_grupo.php', {
 *     method: 'POST',
 *     body: new URLSearchParams({ nombre: 'Mi Empresa' })
 * })
 * .then(response => response.text())
 * .then(data => console.log(data));
 *
 * Argumentos de entrada:
 * ----------------------
 * - `nombre` (POST): Nombre del grupo empresarial que se desea crear. No puede estar vacío.
 *
 * Argumentos de salida:
 * ----------------------
 * - Mensaje de éxito o error:
 *   - `"success: Grupo creado exitosamente."`
 *   - `"error: <mensaje de error>"` en caso de fallos en la creación del grupo.
 *
 * Módulos relacionados:
 * ---------------------
 * - `config.php`: Contiene la configuración de la base de datos y la conexión PDO.
 * - `usuarios_grupos`: Tabla en la base de datos donde se registran los usuarios dentro de grupos con sus respectivos roles.
 * - `empresa`: Tabla en la base de datos donde se almacenan los datos de los grupos empresariales.
 *
 * Flujo de datos:
 * ---------------
 * 1. Inicia la sesión del usuario (`session_start()`).
 * 2. Verifica si el usuario está autenticado. Si no, termina la ejecución con un mensaje de error.
 * 3. Obtiene y valida el `nombre` del grupo desde la solicitud `POST`. Si está vacío, se detiene la ejecución con un mensaje de error.
 * 4. Comprueba en la base de datos si el nombre del grupo ya existe.
 * 5. Si el nombre no existe:
 *    - Se genera una clave de acceso aleatoria.
 *    - Se inserta el nuevo grupo en la tabla `empresa` con la clave de acceso.
 *    - Se recupera el ID del grupo recién creado.
 *    - Se asocia al usuario creador al grupo en `usuarios_grupos` con rol de administrador.
 * 6. Devuelve un mensaje de éxito o de error en caso de fallo en la base de datos.
 */

session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    die("Acceso no autorizado.");
}

$user_id = $_SESSION['user_id'];
$nombreGrupo = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';

if (empty($nombreGrupo)) {
    die("error: El nombre del grupo no puede estar vacío.");
}

try {
    // Verificar si el grupo ya existe
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM empresa WHERE nombre = ?");
    $stmt->execute([$nombreGrupo]);
    $existe = $stmt->fetchColumn();

    if ($existe > 0) {
        die("error: El nombre del grupo ya existe.");
    }

    // Insertar el grupo en la base de datos
    $stmt = $pdo->prepare("INSERT INTO empresa (nombre, clave_acceso,id_creador) VALUES (?, ?,?)");
    $claveAcceso = bin2hex(random_bytes(5));
    $stmt->execute([$nombreGrupo, $claveAcceso,$user_id]);

    $grupo_id = $pdo->lastInsertId();

    // Insertar relación en usuarios_grupos con el rol de creador
    $stmt = $pdo->prepare("INSERT INTO usuarios_grupos (id_usuario, id_empresa, rol) VALUES (?, ?, 1)");
    $stmt->execute([$user_id, $grupo_id]);

    echo "success: Grupo creado exitosamente.";
} catch (PDOException $e) {
    echo "error: Error al crear el grupo: " . $e->getMessage();
}
?>
