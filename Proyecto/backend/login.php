<?php
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
