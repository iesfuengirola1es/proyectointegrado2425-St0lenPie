<?php

/**
 * Módulo: Autenticación de Usuario
 * 
 * Este script permite a los usuarios autenticarse en el sistema mediante correo electrónico y contraseña.
 * Si la autenticación es exitosa, se inicia una sesión y se redirige al panel de control.
 *
 * Ejemplo de llamada:
 * -------------------
 * fetch('login.php', {
 *     method: 'POST',
 *     body: new URLSearchParams({ email: 'usuario@example.com', password: 'contraseña123' }),
 *     headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
 * }).then(response => response.text()).then(data => console.log(data));
 *
 * Argumentos:
 * -----------
 * Entrada:
 * - `email` (string) → Correo electrónico del usuario.
 * - `password` (string) → Contraseña del usuario.
 *
 * Salida:
 * - Si la autenticación es exitosa, redirige a `../frontend/dashboard.html`.
 * - Si hay un error, redirige a `../frontend/login.html` y guarda el mensaje de error en `$_SESSION['error']`.
 *
 * Módulos relacionados:
 * ---------------------
 * - `config.php` → Contiene la configuración de conexión a la base de datos.
 * - `usuarios` (tabla) → Contiene la información de los usuarios, incluyendo email y contraseña encriptada.
 *
 * Flujo de datos interno:
 * -----------------------
 * 1. Se inicia la sesión del usuario (`session_start()`).
 * 2. Se verifica si la solicitud es de tipo `POST`.
 * 3. Se recibe y sanitiza el correo electrónico y la contraseña ingresada por el usuario.
 * 4. Se consulta la base de datos para obtener el usuario con el email proporcionado.
 * 5. Si el usuario existe:
 *    - Se compara la contraseña ingresada con la almacenada en la base de datos usando `password_verify()`.
 *    - Si la contraseña es correcta, se guarda el ID del usuario en `$_SESSION['user_id']` y se redirige al dashboard.
 *    - Si la contraseña es incorrecta, se guarda un mensaje de error en `$_SESSION['error']` y se redirige a la página de login.
 * 6. Si el usuario no existe, se guarda un mensaje de error en `$_SESSION['error']` y se redirige a la página de login.
 * 7. Si hay un error de conexión con la base de datos, se captura la excepción y se muestra un mensaje de error.
 */

session_start();
require __DIR__ . '/../backend/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    try {
        // Preparar la consulta con PDO
        $stmt = $pdo->prepare("SELECT id_usuario, contraseña FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Verificar la contraseña
            if (password_verify($password, $user['contraseña'])) {
                $_SESSION['user_id'] = $user['id_usuario'];
                header("Location: ../frontend/dashboard.html");
                exit();
            } else {
                $_SESSION['error'] = "Contraseña incorrecta";
            }
        } else {
            $_SESSION['error'] = "Usuario no encontrado";
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error en la base de datos: " . $e->getMessage();
    }
    
    header("Location: ../frontend/login.html");
    exit();
}
?>
