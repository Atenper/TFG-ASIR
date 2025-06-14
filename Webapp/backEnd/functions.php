<?php
include("commons.php");


function getFacturaById($id) {
    $id = sanitize($id, 'int');
    $query = "SELECT f.*, u.name AS user_name 
              FROM facturas f 
              JOIN users u ON f.user_id = u.id 
              WHERE f.id = $id LIMIT 1";
    $result = doQuery($query);
    return ($result && !empty($result)) ? $result[0] : false;
}

function updateDownloadDate($id) {
    $id = sanitize($id, 'int');
    $now = date('Y-m-d H:i:s');
    $query = "UPDATE facturas SET fecha_descarga = '$now' WHERE id = $id";
    return (doQuery($query) !== false);
}

function deleteFactura($id) {
    $id = sanitize($id, 'int');
    
    // 1. Obtener información de la factura
    $factura = getFacturaById($id);
    if (!$factura) {
        error_log("Factura no encontrada con ID: $id");
        return false;
    }
    
    // 2. Eliminar registro de la base de datos
    $query = "DELETE FROM facturas WHERE id = $id";
    $result = doQuery($query);
    
    // 3. Si existe archivo físico y se eliminó de la BD, borrarlo
    if ($result !== false && !empty($factura['ruta_archivo'])) {
        $filePath = $_SERVER['DOCUMENT_ROOT'] . $factura['ruta_archivo'];
        if (file_exists($filePath)) {
            if (!unlink($filePath)) {
                error_log("Error al borrar archivo: $filePath");
            }
        } else {
            error_log("Archivo no encontrado: $filePath");
        }
    }
    
    return ($result !== false);
}


function getClienteById($id) {
    $id = sanitize($id, 'int');
    $query = "SELECT * FROM users WHERE id = $id LIMIT 1";
    $result = doQuery($query);
    return ($result && !empty($result)) ? $result[0] : false;
}

function deleteCliente($id) {
    $id = sanitize($id, 'int');
    
    // 1. Obtener información del cliente
    $cliente = getClienteById($id);
    if (!$cliente) {
        error_log("Cliente no encontrado con ID: $id");
        return false;
    }
    
    // 2. Eliminar registro de la base de datos
    $query = "DELETE FROM users WHERE id = $id";
    $result = doQuery($query);
    
    // 3. Si existe imagen de perfil, borrarla
    if ($result !== false && !empty($cliente['imagen'])) {
        $filePath = $_SERVER['DOCUMENT_ROOT'] . parse_url($cliente['imagen'], PHP_URL_PATH);
        if (file_exists($filePath) && is_writable($filePath)) {
            if (!unlink($filePath)) {
                error_log("Error al borrar imagen: $filePath");
            }
        }
    }
    
    return ($result !== false);
}

?>