<?php
header('Content-Type: application/json');

// Configuración
$logFile = '../ansible/install.log';
$playbookDir = '../ansible/';
set_time_limit(300);

// Log inicial
file_put_contents($logFile, date('[Y-m-d H:i:s]')." Iniciando instalación\n", FILE_APPEND);

// Obtener y validar JSON
$json = file_get_contents('php://input');
$data = json_decode($json, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    die(json_encode(['success' => false, 'error' => 'JSON inválido: '.json_last_error_msg()]));
}

file_put_contents($logFile, "Datos recibidos: ".print_r($data, true)."\n", FILE_APPEND);

// Validar campos obligatorios
$ctId = $data['ct_id'] ?? null;
$appType = $data['app_type'] ?? null;

if (!$ctId || !$appType) {
    http_response_code(400);
    die(json_encode(['success' => false, 'error' => 'Se requieren ct_id y app_type']));
}

if (!in_array($appType, ['wordpress', 'nextcloud'])) {
    http_response_code(400);
    die(json_encode(['success' => false, 'error' => 'app_type debe ser wordpress o nextcloud']));
}

// Determinar playbook
$playbook = $playbookDir . $appType . '.yml';
if (!file_exists($playbook)) {
    http_response_code(404);
    die(json_encode(['success' => false, 'error' => "Playbook no encontrado: $playbook"]));
}

// Ejecutar Ansible
$command = sprintf(
    'ansible-playbook -i ../ansible/inventario.ini %s --extra-vars "ct_id=%s" 2>&1',
    escapeshellarg($playbook),
    escapeshellarg($ctId)
);

file_put_contents($logFile, "Ejecutando: $command\n", FILE_APPEND);
exec($command, $output, $returnCode);
$outputStr = implode("\n", $output);
file_put_contents($logFile, "Resultado ($returnCode): $outputStr\n", FILE_APPEND);

// Respuesta
if ($returnCode === 0) {
    echo json_encode([
        'success' => true,
        'message' => ucfirst($appType).' instalado correctamente en CT '.$ctId,
        'output' => $outputStr
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error en la instalación (Código: '.$returnCode.')',
        'details' => $outputStr
    ]);
}
?>