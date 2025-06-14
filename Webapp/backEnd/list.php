<?php
include("commons.php");
function getClientes($search = null){
    $search = sanitize($search);
    $query = "SELECT id, name, mail, imagen FROM users";
    
    if (!empty($search)) {
        // Escapamos manualmente para LIKE
        $safeSearch = str_replace(['%', '_'], ['\%', '\_'], $search);
        $query .= " WHERE name LIKE '%".mysqli_real_escape_string(getLink(), $safeSearch)."%'";
    }
    
    
    $result = doQuery($query);
    return is_array($result) ? $result : [];
}

function getFacturas($search = null) {
    $search = sanitize($search);
    
    $query = "SELECT f.*, u.name as cliente_name, u.mail as cliente_email 
              FROM facturas f
              LEFT JOIN users u ON f.user_id = u.id";
    
    if (!empty($search)) {
        $safeSearch = str_replace(['%', '_'], ['\%', '\_'], $search);
        $escapedSearch = mysqli_real_escape_string(getLink(), $safeSearch);
        
        $query .= " WHERE f.nombre LIKE '%$escapedSearch%' 
                    OR u.name LIKE '%$escapedSearch%'
                    OR f.id LIKE '%$escapedSearch%'";
    }
    
    $query .= " ORDER BY f.fecha_creacion DESC";
    
    $result = doQuery($query);
    return is_array($result) ? $result : [];
}

function getClienteFacturas($search = null) {
    // Verificar sesión y obtener ID del usuario
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['userId'])) {
        return [];
    }

    // Sanitizar y escapar valores
    $user_id = mysqli_real_escape_string(getLink(), (int)$_SESSION['userId']);
    $search = sanitize($search);
    
    // Query optimizada
    $query = "SELECT f.*, u.name as cliente_name 
    FROM facturas f
    INNER JOIN users u ON f.user_id = u.id 
    WHERE f.user_id = '$user_id'";

    // Búsqueda
    if (!empty($search)) {
        $safeSearch = str_replace(['%', '_'], ['\%', '\_'], $search);
        $escapedSearch = mysqli_real_escape_string(getLink(), $safeSearch);
        
        $query .= " AND (
            f.nombre LIKE '%$escapedSearch%' OR 
            f.id LIKE '%$escapedSearch%'
        )";
    }

    $query .= " ORDER BY f.fecha_creacion DESC";

    return is_array(doQuery($query)) ? doQuery($query) : [];
}




?>
