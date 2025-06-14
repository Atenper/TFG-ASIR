<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

require_once 'gt_vms.php'; // Reutilizar la lógica de gt_vms.php para obtener las máquinas

try {
    // Obtener datos de máquinas y contenedores desde Proxmox
    $result = gt_vms(); // Llamar a la función que devuelve los datos de máquinas y contenedores
    $vms = $result['vms'] ?? [];
    $containers = $result['containers'] ?? [];

    // Combinar resultados
    $allResults = array_merge($vms, $containers);

    echo json_encode(['success' => true, 'results' => $allResults]);
} catch (Exception $e) {
    error_log('Error en buscar_maquinas.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
