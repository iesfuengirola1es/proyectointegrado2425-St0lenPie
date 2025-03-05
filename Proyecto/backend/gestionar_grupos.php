<?php

/**
 * Módulo: Gestión de Grupos Empresariales
 * 
 * Este script permite a un usuario autenticado crear un grupo empresarial y asignarse el rol de Administrador.
 *
 * Ejemplo de llamada:
 * -------------------
 * fetch('gestionar_grupos.php', {
 *     method: 'POST',
 *     body: new URLSearchParams({ nombre: 'Nuevo Grupo' }),
 *     headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
 * }).then(response => response.text()).then(data => console.log(data));
 *
 * Argumentos:
 * -----------
 * Entrada:
 * - `POST['nombre']` (string) → Nombre del grupo a crear. No puede estar vacío.
 * - `$_SESSION['user_id']` (int) → ID del usuario autenticado que creará el grupo.
 *
 * Salida:
 * - `success: Grupo creado y rol de Administrador asignado.` → Si la creación es exitosa.
 * - `error: El nombre del grupo no puede estar vacío.` → Si el campo `nombre` está vacío.
 * - `error: No se encontró el rol de Administrador.` → Si el rol de Administrador no existe en la base de datos.
 * - `error: Error en la creación del grupo.` → Si ocurre un fallo en la base de datos.
 *
 * Módulos relacionados:
 * ---------------------
 * - `config.php` → Contiene la configuración de conexión a la base de datos.
 * - `empresa` (tabla) → Almacena la información de los grupos empresariales.
 * - `usuarios` (tabla) → Relaciona usuarios con empresas y roles.
 * - `roles` (tabla) → Contiene la lista de roles y sus permisos.
 *
 * Flujo de datos interno:
 * -----------------------
 * 1. Se inicia la sesión y se verifica la autenticación del usuario (`$_SESSION['user_id']`).
 * 2. Se recibe y limpia el nombre del grupo (`$_POST['nombre']`).
 * 3. Se valida que el nombre del grupo no esté vacío.
 * 4. Se inserta el nuevo grupo en la base de datos en la tabla `empresa`.
 * 5. Se obtiene el ID del rol "Administrador" desde la tabla `roles`.
 * 6. Se asigna el rol de Administrador al usuario creador del grupo en la tabla `usuarios`.
 * 7. Se registra el proceso en los logs y se devuelve un mensaje de éxito o error.
 */


session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    die("Acceso no autorizado.");
}

$user_id = $_SESSION['user_id'];
$nombre_grupo = trim($_POST['nombre'] ?? '');

if (empty($nombre_grupo)) {
    die("error: El nombre del grupo no puede estar vacío.");
}

try {
    // Crear el grupo en la base de datos
    $stmt = $pdo->prepare("INSERT INTO empresa (nombre) VALUES (?)");
    $stmt->execute([$nombre_grupo]);
    $id_empresa = $pdo->lastInsertId();

    // Obtener el ID del rol "Administrador"
    $stmt = $pdo->prepare("SELECT id_rol FROM roles WHERE nombre = 'Administrador'");
    $stmt->execute();
    $id_rol_admin = $stmt->fetchColumn();

    if (!$id_rol_admin) {
        die("error: No se encontró el rol de Administrador.");
    }

    // Asignar el rol "Administrador" al usuario creador del grupo
    $stmt = $pdo->prepare("UPDATE usuarios SET id_empresa = ?, id_rol = ? WHERE id_usuario = ?");
    $stmt->execute([$id_empresa, $id_rol_admin, $user_id]);

    error_log("✅ Usuario ID $user_id asignado como Administrador del grupo ID $id_empresa");

    echo "success: Grupo creado y rol de Administrador asignado.";
} catch (PDOException $e) {
    error_log("❌ Error en la creación del grupo: " . $e->getMessage());
    echo "error: Error en la creación del grupo.";
}
?>
