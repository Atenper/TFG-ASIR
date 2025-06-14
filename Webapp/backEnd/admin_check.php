<?php
// Verificar que el usuario esté autenticado
if (!isset($_SESSION['userId'])) {
    $_SESSION['msg'] = 'protected'; // Mensaje: "Debes iniciar sesión para acceder a esta página."
    header("Location: index.php"); // Redirigir al login
    exit();
}

// Verificar que el usuario sea administrador (tipo 2)
if ($_SESSION['tipo'] !== 2) {
    // Cerrar la sesión
    session_unset(); // Eliminar todas las variables de sesión
    session_destroy(); // Destruir la sesión

    // Redirigir al login con un mensaje de error
    $_SESSION['msg'] = 'access_denied'; // Mensaje: "Acceso denegado. No tienes permisos suficientes."
    header("Location: index.php"); // Redirigir al login
    exit();
}
?>