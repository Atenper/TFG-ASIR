<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

function handleError($message) {
    error_log("Error: $message"); // Registrar el error en los logs
    http_response_code(500);
    die(json_encode(['success' => false, 'error' => $message]));
}

try {
    // Obtener datos de la solicitud JSON
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['ct_id'])) {
        handleError('ID del contenedor no proporcionado');
    }

    $ct_id = intval($data['ct_id']);
    error_log("ID del contenedor recibido para borrar: $ct_id");

    // Ejecutar el playbook de Ansible para borrar la máquina
    $cmd = "ansible-playbook -i ../ansible/inventario.ini ../ansible/borrar_lxc.yml --extra-vars 'ct_id=$ct_id'";
    exec($cmd . " 2>&1", $output, $return_var);

    error_log("Salida completa del comando:");
    foreach ($output as $line) {
        error_log($line); // Registrar cada línea de la salida
    }
    error_log("Código de retorno: $return_var");

    if ($return_var === 0) {
        echo json_encode(['success' => true, 'message' => "Contenedor $ct_id borrado correctamente."]);
    } else {
        handleError("Error al borrar el contenedor $ct_id: " . implode("\n", $output));
    }
} catch (Exception $e) {
    handleError($e->getMessage());
}
?>