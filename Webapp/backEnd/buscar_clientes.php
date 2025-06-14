<?php
include("commons.php");

// Obtener el término de búsqueda
$query = $_GET['q'] ?? '';

// Buscar usuarios en la base de datos
if (!empty($query)) {
    $sql = "SELECT id, name, mail FROM users WHERE name LIKE '%$query%' OR mail LIKE '%$query%' LIMIT 10";
    $usuarios = doQuery($sql) ?: [];
} else {
    $usuarios = [];
}

// Devolver los resultados en formato JSON
header('Content-Type: application/json');
echo json_encode($usuarios);
?>