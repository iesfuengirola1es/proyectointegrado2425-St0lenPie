<?php
require 'config.php'; // Asegurar que config.php contiene la conexión correcta

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
