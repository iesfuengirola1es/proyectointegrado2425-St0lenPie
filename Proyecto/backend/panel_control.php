<?php

/**
 * Módulo: Acceso al Panel de Control
 * 
 * Este script valida si un usuario tiene permisos en una empresa específica antes de acceder al panel de control.
 * Si el usuario está autorizado, se le redirige al panel de control. De lo contrario, se muestra un mensaje de error.
 *
 * Ejemplo de llamada:
 * -------------------
 * fetch('panel_control.php?id_empresa=1', {
 *     method: 'GET',
 *     headers: { 'Content-Type': 'application/json' }
 * }).then(response => response.text()).then(data => console.log(data));
 *
 * Argumentos:
 * -----------
 * Entrada:
 * - `$_SESSION['user_id']` (int) → ID del usuario autenticado. Obligatorio.
 * - `id_empresa` (int) → ID de la empresa a la que se intenta acceder. Obligatorio.
 *
 * Salida:
 * - Redirección a `../frontend/panel_control.html?id_empresa=<id_empresa>` si el usuario tiene acceso.
 * - Si el usuario no tiene permisos en la empresa, se almacena un mensaje de error en `$_SESSION['error']`
 *   y se redirige a `../frontend/dashboard.html`.
 * - Si ocurre un error en la base de datos, también se redirige a `../frontend/dashboard.html` con el mensaje de error.
 *
 * Módulos relacionados:
 * ---------------------
 * - `config.php` → Contiene la configuración de conexión a la base de datos.
 * - `usuarios_grupos` (tabla) → Relaciona usuarios con empresas y sus roles.
 * - `empresa` (tabla) → Contiene la información de los grupos empresariales.
 *
 * Flujo de datos interno:
 * -----------------------
 * 1. Se inicia la sesión y se verifica que el usuario esté autenticado (`$_SESSION['user_id']`).
 * 2. Se recibe y valida el parámetro `id_empresa` de la URL.
 * 3. Se consulta la base de datos para verificar si el usuario pertenece a la empresa.
 * 4. Si el usuario tiene acceso:
 *    - Se guarda `id_empresa` en la sesión (`$_SESSION['id_empresa']`).
 *    - Se redirige al panel de control (`../frontend/panel_control.html?id_empresa=<id_empresa>`).
 * 5. Si el usuario no tiene acceso:
 *    - Se guarda un mensaje de error en `$_SESSION['error']`.
 *    - Se redirige al dashboard (`../frontend/dashboard.html`).
 * 6. Si ocurre un error en la base de datos:
 *    - Se captura la excepción y se almacena un mensaje de error en `$_SESSION['error']`.
 *    - Se redirige al dashboard.
 */

session_start();
require __DIR__ . '/../backend/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $user_id = $_SESSION['user_id'];
    $id_empresa = $_GET['id_empresa'];
    

    try {
        // Preparar la consulta con PDO
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios_grupos WHERE id_empresa = ? and id_usuario = ?");

        $stmt->execute([$id_empresa,$user_id]);

           
        $existe = $stmt->fetchColumn();

        if ($existe > 0) {
             $_SESSION['id_empresa'] = $id_empresa;
            header("Location: ../frontend/panel_control.html?id_empresa=".$id_empresa);
            exit();
        }else{
            $_SESSION['error'] = "Este usuario no tiene permisos para esta empresa";
           
        }


       
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error en la base de datos: " . $e->getMessage();
    }
    
    header("Location: ../frontend/dashboard.html");
    exit();
}
?>