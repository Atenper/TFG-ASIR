<?php
include("commons.php");
session_start();

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['userId'])) {
    header("Location: index.php?msg=protected");
    exit();
}

// Habilitar visualización de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['factura']) || empty($_FILES['factura']['name'][0])) {
        $_SESSION['msg'] = 'no_files_uploaded'; // Mensaje de error
        header("Location: ../main.php");
        exit();
    }

    // Obtener datos del formulario
    $cliente_id = sanitize($_POST['cliente_id'] ?? 0, 'int');
    $fecha_creacion = date('Y-m-d H:i:s');
    $uploads_dir = "/var/www/html/uploads/"; // Ruta corregida

    // Verificar y crear el directorio si no existe
    if (!is_dir($uploads_dir)) {
        mkdir($uploads_dir, 0777, true);
    }

    // Verificar permisos de escritura
    if (!is_writable($uploads_dir)) {
        $_SESSION['msg'] = 'upload_dir_not_writable'; // Mensaje de error
        header("Location: ../main.php");
        exit();
    }

    $archivos = $_FILES['factura'];
    $total_archivos = count($archivos['name']);

    foreach ($archivos['name'] as $i => $nombre_archivo) {
        if ($archivos['error'][$i] === UPLOAD_ERR_OK) {
            $ruta_temporal = $archivos['tmp_name'][$i];
            $tipo_archivo = mime_content_type($ruta_temporal);
            $extension = strtolower(pathinfo($nombre_archivo, PATHINFO_EXTENSION));
            $tamano_archivo = $archivos['size'][$i];
            $tipos_permitidos = ['application/pdf'];
            $extensiones_permitidas = ['pdf'];
            $tamano_maximo = 200 * 1024 * 1024; // 200 MB

            // Validar tipo MIME y extensión
            if (in_array($tipo_archivo, $tipos_permitidos) && in_array($extension, $extensiones_permitidas) && $tamano_archivo <= $tamano_maximo) {
                $nombre_unico = uniqid() . "_" . $nombre_archivo;
                $ruta_destino = $uploads_dir . $nombre_unico;

                if (move_uploaded_file($ruta_temporal, $ruta_destino)) {
                    // Insertar la factura en la base de datos usando doQuery()
                    $sql = "INSERT INTO facturas (nombre, ruta_archivo, fecha_creacion, fecha_descarga, user_id) 
                            VALUES ('$nombre_archivo', '$ruta_destino', '$fecha_creacion', NULL, $cliente_id)";
                    $resultado = doQuery($sql);

                    if ($resultado) {
                        $_SESSION['msg'] = 'file_upload_success'; // Mensaje de éxito
                    } else {
                        $_SESSION['msg'] = 'file_upload_db_error'; // Mensaje de error
                    }
                } else {
                    $_SESSION['msg'] = 'file_move_error'; // Mensaje de error
                }
            } else {
                $_SESSION['msg'] = 'invalid_file_type'; // Mensaje de error
            }
        } else {
            $_SESSION['msg'] = 'file_upload_error'; // Mensaje de error
        }
    }
} else {
    $_SESSION['msg'] = 'invalid_request'; // Mensaje de error
}

// Redirigir de vuelta a main.php
header("Location: ../main.php");
exit();
?>