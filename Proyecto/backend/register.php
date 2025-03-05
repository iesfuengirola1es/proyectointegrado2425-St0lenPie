<?php

/**
 * Módulo: Registro de Usuarios
 * 
 * Este script permite a los nuevos usuarios registrarse en el sistema proporcionando su nombre, correo electrónico y contraseña.
 * Si el registro es exitoso, el usuario es redirigido a la página de inicio de sesión.
 *
 * Ejemplo de llamada:
 * -------------------
 * fetch('register.php', {
 *     method: 'POST',
 *     body: new URLSearchParams({ name: 'UsuarioEjemplo', email: 'usuario@example.com', password: 'contraseña123' }),
 *     headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
 * }).then(response => response.text()).then(data => console.log(data));
 *
 * Argumentos:
 * -----------
 * Entrada:
 * - `name` (string) → Nombre del usuario. Obligatorio.
 * - `email` (string) → Correo electrónico del usuario. Obligatorio y debe ser único en la base de datos.
 * - `password` (string) → Contraseña del usuario. Obligatorio.
 *
 * Salida:
 * - Redirección a `../frontend/login.html` si el registro es exitoso.
 * - `"Todos los campos son obligatorios."` → Si falta algún dato en el formulario.
 * - `"El email ya está registrado."` → Si el email ya existe en la base de datos.
 * - `"Error al registrar el usuario."` → Si ocurre un fallo en la base de datos.
 *
 * Módulos relacionados:
 * ---------------------
 * - `config.php` → Contiene la configuración de conexión a la base de datos.
 * - `usuarios` (tabla) → Almacena la información de los usuarios registrados.
 *
 * Flujo de datos interno:
 * -----------------------
 * 1. Se verifica que la solicitud sea de tipo `POST`.
 * 2. Se recibe y sanitiza la información ingresada por el usuario (`name`, `email`, `password`).
 * 3. Se valida que todos los campos estén completos.
 * 4. Se verifica si el correo electrónico ya está registrado en la base de datos.
 * 5. Si el correo no está registrado:
 *    - Se encripta la contraseña con `password_hash()`.
 *    - Se inserta un nuevo usuario en la base de datos.
 *    - Se redirige a la página de inicio de sesión (`../frontend/login.html`).
 * 6. Si ocurre un error en la base de datos, se muestra un mensaje de error.
 */

require 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Validación básica
    if (empty($name) || empty($email) || empty($password)) {
        die("Todos los campos son obligatorios.");
    }

    // Hashear la contraseña
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Verificar si el email ya está registrado
    $query = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE email = ?");
    $query->execute([$email]);

    if ($query->rowCount() > 0) {
        die("El email ya está registrado.");
    }

    // Insertar usuario en la base de datos
    $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, contraseña) VALUES (?, ?, ?)");
    
    if ($stmt->execute([$name, $email, $hashed_password])) {
        // Redirigir correctamente a login.html tras el registro exitoso
        header("Location: ../frontend/login.html");
        exit();
    } else {
        echo "Error al registrar el usuario.";
    }
}
?>
