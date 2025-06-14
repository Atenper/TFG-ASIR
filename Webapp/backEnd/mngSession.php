<?php
include("commons.php");

function userAuthentication($mail, $pass, &$userData) {
    $query = "SELECT id, name, pass, tipo, mail, imagen FROM users WHERE mail = ?";
    $conn = getLink();
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $mail);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        if (password_verify($pass, $row['pass'])) {
            $userData = [
                'id' => $row['id'],
                'name' => $row['name'],
                'tipo' => $row['tipo'],
                'mail' => $row['mail'],
                'imagen' => !empty($row['imagen']) ? $row['imagen'] : 'img/default_profile.jpg'
            ];
            return true;
        }
    }
    return false;
}

function sessionClose($txt = null) {
    $_SESSION = array();
    session_destroy();
    global $url, $msg;
    $url = '../index.php';
    $msg = $txt ? $txt : 'session_closed';
}

function createSession($id, $name, $tipo, $mail, $imagen) {
    session_regenerate_id(true);
    $_SESSION['userId'] = $id;
    $_SESSION['name'] = explode(" ", ucfirst($name))[0];
    $_SESSION['tipo'] = $tipo;
    $_SESSION['mail'] = $mail;
    $_SESSION['imagen'] = !empty($imagen) ? $imagen : 'img/default_profile.jpg';
    $_SESSION['lastActivity'] = time();
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Iniciar sesión con manejo de errores
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inicializar variables de sesión si no existen
$_SESSION['mail'] = $_SESSION['mail'] ?? null;
$_SESSION['name'] = $_SESSION['name'] ?? null;
$_SESSION['tipo'] = $_SESSION['tipo'] ?? null;

// Variables globales para redirección y mensajes
$url = null;
$msg = null;

// Obtener la operación y sanitizar las entradas
$op = sanitize($_REQUEST['op'] ?? '');
$mail = sanitize($_POST['mail'] ?? '', 'mail');
$pass = sanitize($_POST['pass'] ?? '', 'pass');

// Procesar la operación
switch ($op) {
    case 'ss': // Iniciar sesión
        if (isset($_SESSION['userId'])) {
            sessionClose("session_duplicity");
        } else {
            $userData = [];
            if (userAuthentication($mail, $pass, $userData)) {
                createSession(
                    $userData['id'], 
                    $userData['name'], 
                    $userData['tipo'], 
                    $userData['mail'],
                    $userData['imagen']
                );
                
                // Redirigir según el tipo de usuario
                $url = ($userData['tipo'] === 2) ? '../main.php' : '../mainCliente.php';
                $msg = "login";
            } else {
                $url = '../index.php';
                $msg = 'auth_error';
            }
        }
        break;

    case 'sc': // Cerrar sesión
        if (!isset($_SESSION['csrf_token']) || ($_POST['csrf_token'] !== $_SESSION['csrf_token'])) {
            sessionClose("session_token");
        }
        sessionClose();
        break;

    case 'ur': // Registrar usuario
        $name = sanitize($_POST['name'] ?? '');
        $pass = password_hash($pass, PASSWORD_DEFAULT);
        $query = "INSERT INTO users (mail, pass, name, tipo, imagen) VALUES (?, ?, ?, 1, 'img/default_profile.jpg')";
        $conn = getLink();
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sss", $mail, $pass, $name);
        $result = $stmt->execute();
        
        if ($result) {
            $userId = $stmt->insert_id;
            createSession(
                $userId,
                $name,
                1, // Tipo cliente
                $mail,
                'img/default_profile.jpg'
            );
        }
        
        $url = '../mainCliente.php';
        $msg = ($result) ? 'reg_success' : 'reg_error';
        break;

    default:
        sessionClose("unexpected_error");
        break;
}

// Redirigir a la URL correspondiente con el mensaje
gotoURL($url, $msg);
?>