<?php

/**
 * Módulo: Dashboard de Usuario
 * 
 * Este script permite obtener la lista de grupos a los que pertenece un usuario autenticado.
 * Un usuario puede aparecer en la lista si ha creado un grupo o si ha sido invitado a uno.
 *
 * Ejemplo de llamada:
 * -------------------
 * fetch('dashboard.php', {
 *     method: 'GET',
 *     headers: { 'Content-Type': 'application/json' }
 * }).then(response => response.json()).then(data => console.log(data));
 *
 * Argumentos:
 * -----------
 * Entrada:
 * - `$_SESSION['user_id']` (int) → ID del usuario autenticado. 
 *   - Es obligatorio para acceder a la información de los grupos.
 *
 * Salida:
 * - `{"grupos": [{"id_empresa": 1, "nombre": "Mi Empresa"}, ...]}` → Lista de grupos del usuario.
 * - `{"error": "Acceso no autorizado."}` → Si el usuario no está autenticado.
 * - `{"error": "Error al obtener grupos: <mensaje>"}` → Si ocurre un fallo en la base de datos.
 *
 * Módulos relacionados:
 * ---------------------
 * - `config.php` → Contiene la configuración de conexión a la base de datos.
 * - `usuarios_grupos` (tabla) → Relaciona usuarios con grupos.
 * - `empresa` (tabla) → Contiene los datos de los grupos empresariales.
 * - `usuarios` (tabla) → Almacena los usuarios de la plataforma.
 *
 * Flujo de datos interno:
 * -----------------------
 * 1. Se inicia la sesión para validar la autenticación del usuario.
 * 2. Se verifica que `$_SESSION['user_id']` esté definido.
 * 3. Se ejecuta una consulta SQL para obtener los grupos del usuario:
 *    - Se consultan los grupos donde el usuario es creador (`empresa.id_empresa`).
 *    - Se consultan los grupos donde el usuario ha sido invitado (`usuarios_grupos.id_empresa`).
 *    - Se eliminan duplicados con `DISTINCT`.
 * 4. Se devuelve un JSON con la lista de grupos o un mensaje de error si algo falla.
 */

session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    die(json_encode(["error" => "Acceso no autorizado."]));
}

$user_id = $_SESSION['user_id'];

try {
    // Obtener grupos donde el usuario es creador o donde ha sido invitado
    $stmt = $pdo->prepare("
        SELECT DISTINCT empresa.id_empresa, empresa.nombre 
        FROM empresa
        LEFT JOIN usuarios ON empresa.id_empresa = usuarios.id_empresa AND usuarios.id_usuario = ?
        LEFT JOIN usuarios_grupos ON empresa.id_empresa = usuarios_grupos.id_empresa AND usuarios_grupos.id_usuario = ?
        WHERE usuarios.id_usuario IS NOT NULL OR usuarios_grupos.id_usuario IS NOT NULL
    ");
    
    $stmt->execute([$user_id, $user_id]);
    $grupos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["grupos" => $grupos]);

} catch (PDOException $e) {
    echo json_encode(["error" => "Error al obtener grupos: " . $e->getMessage()]);
}
?>
