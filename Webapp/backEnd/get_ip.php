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
    error_log("Iniciando solicitud para obtener IP...");

    // Obtener datos de la solicitud JSON
    $data = json_decode(file_get_contents('php://input'), true);
    error_log("Datos recibidos: " . print_r($data, true));

    if (!isset($data['id']) || !isset($data['type'])) {
        handleError('ID o tipo no proporcionado');
    }

    $id = intval($data['id']);
    $type = $data['type'];

    error_log("ID recibido: $id, Tipo recibido: $type");

    // Ejecutar el playbook de Ansible
    $cmd = "ansible-playbook -i ../ansible/inventario.ini ../ansible/get_ip.yml --extra-vars 'vmid=$id vm_type=$type'";
    exec($cmd . " 2>&1", $output, $return_var);

    error_log("Salida completa del comando:");
    foreach ($output as $line) {
        error_log($line); // Registrar cada línea de la salida
    }
    error_log("Código de retorno: $return_var");

    if ($return_var === 0) {
        // Buscar la IP en la salida del comando
        $ip = null;
        foreach ($output as $line) {
            if (strpos($line, 'stdout') !== false) { // Buscar la línea que contiene 'stdout'
                $parts = explode(':', $line);
                if (isset($parts[1])) {
                    $ip = trim($parts[1], " \t\n\r\0\x0B\","); // Eliminar espacios, comillas y caracteres adicionales
                }
                break;
            }
        }

        if ($ip) {
            echo json_encode(['success' => true, 'ip' => $ip]);
        } else {
            handleError("No se pudo obtener la IP para $type $id.");
        }
    } else {
        handleError("Error al ejecutar el playbook de Ansible para $type $id.");
    }
} catch (Exception $e) {
    handleError($e->getMessage());
}
?>