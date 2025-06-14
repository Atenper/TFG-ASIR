<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

function handleError($message) {
    error_log("Error: $message"); // Registrar el error en los logs
    http_response_code(500);
    die(json_encode(['success' => false, 'error' => $message]));
}

try {
    // Registrar inicio de la solicitud
    error_log("Iniciando solicitud para apagar LXC...");

    // Obtener datos de la solicitud JSON
    $data = json_decode(file_get_contents('php://input'), true);
    error_log("Datos recibidos: " . print_r($data, true));

    if (!isset($data['ct_id'])) {
        handleError('ID del contenedor no proporcionado');
    }

    $ct_id = intval($data['ct_id']);
    error_log("ID del contenedor recibido: $ct_id");

    // Ejecutar el playbook de Ansible para apagar el contenedor
    $cmd = "ansible-playbook -i ../ansible/inventario.ini ../ansible/apagar_lxc.yml --extra-vars 'ct_id=$ct_id'";
    exec($cmd . " 2>&1", $output, $return_var);

    error_log("Salida completa del comando:");
    foreach ($output as $line) {
        error_log($line); // Registrar cada línea de la salida
    }
    error_log("Código de retorno: $return_var");

    if ($return_var === 0) {
        echo json_encode(['success' => true, 'message' => "Contenedor $ct_id apagado correctamente."]);
    } else {
        handleError("Error al apagar el contenedor $ct_id: " . implode("\n", $output));
    }
} catch (Exception $e) {
    handleError($e->getMessage());
}
?>