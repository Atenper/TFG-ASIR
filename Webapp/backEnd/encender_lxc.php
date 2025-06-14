<?php
// filepath: /home/kali/Desktop/Webapp/backEnd/encender_lxc.php

header('Content-Type: application/json');

// Obtener datos de la solicitud JSON
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['ct_id'])) {
    error_log('ID del contenedor no proporcionado'); // Registrar el error en los logs
    echo json_encode(['success' => false, 'error' => 'ID del contenedor no proporcionado']);
    exit;
}

$ct_id = intval($data['ct_id']);
error_log("ID recibido: $ct_id"); // Registrar el ID recibido en los logs

// Ejecutar el comando para encender el contenedor
$cmd = "ansible-playbook -i ../ansible/inventario.ini ../ansible/encender_lxc.yml --extra-vars \"ct_id=$ct_id\"";
exec($cmd . " 2>&1", $output, $return_var);

if ($return_var === 0) {
    echo json_encode(['success' => true, 'message' => "Contenedor $ct_id encendido correctamente"]);
} else {
    echo json_encode(['success' => false, 'error' => "Error al encender el contenedor $ct_id: " . implode("\n", $output)]);
}
?>