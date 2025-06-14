<?php
session_set_cookie_params([
    'lifetime' => 0,           // Sesión se cierra al cerrar el navegador
    'path' => '/',             // Disponible en todo el sitio
    'domain' => '',            // Solo este dominio
    'secure' => true,          // Solo en HTTPS (importante en producción)
    'httponly' => true,        // Evita acceso desde JavaScript
    'samesite' => 'Strict'     // Previene ataques CSRF
]);

session_start();
// Verificar que el usuario esté autenticado
if (!isset($_SESSION['userId'])) {
    header("Location: index.php?msg=protected");
    exit();
}

// Detectar el idioma desde GET, sesión o usar uno por defecto
$default_lang = 'es';
$lang = $_GET['lang'] ?? ($_SESSION['lang'] ?? $default_lang);

// Validar idioma
$available_languages = ['es', 'en'];
if (!in_array($lang, $available_languages)) {
    $lang = $default_lang;
}
$_SESSION['lang'] = $lang;

// Cargar el diccionario de mensajes (cada mensaje es [texto, tipo])
$messages = include "view/messages_$lang.php";
$msgKey = $_GET['msg'] ?? null;
$msg = isset($messages[$msgKey]) ? $messages[$msgKey] : [];
?>