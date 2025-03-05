<?php

/**
 * Módulo: Cierre de Sesión
 * 
 * Este script permite a los usuarios cerrar sesión en el sistema, eliminando todos los datos de la sesión actual.
 *
 * Ejemplo de llamada:
 * -------------------
 * fetch('logout.php', {
 *     method: 'POST'
 * }).then(response => response.json()).then(data => console.log(data));
 *
 * Argumentos:
 * -----------
 * Entrada:
 * - No requiere parámetros.
 *
 * Salida:
 * - `{"success": true}` → Si la sesión se cierra correctamente.
 *
 * Módulos relacionados:
 * ---------------------
 * - `login.php` → Permite a los usuarios autenticarse en el sistema.
 *
 * Flujo de datos interno:
 * -----------------------
 * 1. Se inicia la sesión del usuario (`session_start()`).
 * 2. Se eliminan todas las variables de sesión con `session_unset()`.
 * 3. Se destruye la sesión completamente con `session_destroy()`.
 * 4. Se devuelve una respuesta en formato JSON con `success: true` para indicar que la sesión se ha cerrado.
 */

session_start();
session_unset();
session_destroy();
echo json_encode(["success" => true]);
exit();
?>
