<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

$proxmoxHost = '192.168.1.69';
$proxmoxPort = '8006';
$credentials = [
    'username' => 'root@pam',
    'password' => '13Jiofreed' // ⚠️ Recuerda proteger esto en producción
];

function handleError($message) {
    http_response_code(500);
    die(json_encode(['success' => false, 'error' => $message]));
}

try {
    // 1. Autenticación
    $authUrl = "https://$proxmoxHost:$proxmoxPort/api2/json/access/ticket";
    $chAuth = curl_init($authUrl);

    curl_setopt_array($chAuth, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($credentials),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_TIMEOUT => 10,
    ]);

    $response = curl_exec($chAuth);
    if ($response === false) {
        handleError('Error al conectar con Proxmox: ' . curl_error($chAuth));
    }
    curl_close($chAuth);

    $authData = json_decode($response, true);
    if (!isset($authData['data']['ticket']) || !isset($authData['data']['CSRFPreventionToken'])) {
        handleError('Autenticación fallida. Respuesta inesperada de Proxmox.');
    }

    $ticket = $authData['data']['ticket'];
    $csrfToken = $authData['data']['CSRFPreventionToken'];

    error_log('Respuesta de autenticación: ' . print_r($authData, true));

    // 2. Obtener nodos
    $nodesUrl = "https://$proxmoxHost:$proxmoxPort/api2/json/nodes";
    $chNodes = curl_init($nodesUrl);

    curl_setopt_array($chNodes, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_HTTPHEADER => [
            "Cookie: PVEAuthCookie=$ticket",
            "CSRFPreventionToken: $csrfToken"
        ],
    ]);

    $nodesResponse = curl_exec($chNodes);
    if ($nodesResponse === false) {
        handleError('Error al obtener nodos: ' . curl_error($chNodes));
    }
    curl_close($chNodes);

    error_log("Respuesta cruda de nodos: " . $nodesResponse);

    try {
        // Lógica para obtener nodos, máquinas virtuales (QEMU) y contenedores (LXC)
        error_log('Iniciando obtención de datos desde Proxmox...');
        $nodesData = json_decode($nodesResponse, true);
        error_log('Datos de nodos: ' . print_r($nodesData, true));

        if (empty($nodesData['data'])) {
            throw new Exception('No se encontraron nodos en Proxmox.');
        }

        $nodeName = $nodesData['data'][0]['node'];
        $result = [
            'node' => $nodesData['data'][0],
            'vms' => [],
            'containers' => []
        ];

        // Obtener VMs (QEMU)
        error_log('Obteniendo máquinas virtuales...');
        $qemuUrl = "https://$proxmoxHost:$proxmoxPort/api2/json/nodes/$nodeName/qemu";
        $chQemu = curl_init($qemuUrl);
        curl_setopt_array($chQemu, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER => [
                "Cookie: PVEAuthCookie=$ticket",
                "CSRFPreventionToken: $csrfToken"
            ],
        ]);
        $qemuResponse = curl_exec($chQemu);
        if ($qemuResponse === false) {
            throw new Exception('Error al obtener máquinas virtuales: ' . curl_error($chQemu));
        }
        curl_close($chQemu);
        error_log('Datos de máquinas virtuales: ' . print_r(json_decode($qemuResponse, true), true));

        $result['vms'] = json_decode($qemuResponse, true)['data'] ?? [];

        // Obtener contenedores (LXC)
        error_log('Obteniendo contenedores...');
        $lxcUrl = "https://$proxmoxHost:$proxmoxPort/api2/json/nodes/$nodeName/lxc";
        $chLxc = curl_init($lxcUrl);
        curl_setopt_array($chLxc, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER => [
                "Cookie: PVEAuthCookie=$ticket",
                "CSRFPreventionToken: $csrfToken"
            ],
        ]);
        $lxcResponse = curl_exec($chLxc);
        if ($lxcResponse === false) {
            throw new Exception('Error al obtener contenedores: ' . curl_error($chLxc));
        }
        curl_close($chLxc);
        error_log('Datos de contenedores: ' . print_r(json_decode($lxcResponse, true), true));

        $result['containers'] = json_decode($lxcResponse, true)['data'] ?? [];

        // Respuesta final
        echo json_encode(['success' => true, 'data' => $result]);
    } catch (Exception $e) {
        error_log('Error en gt_vms: ' . $e->getMessage());
        throw $e;
    }

} catch (Exception $e) {
    handleError($e->getMessage());
}
?>
