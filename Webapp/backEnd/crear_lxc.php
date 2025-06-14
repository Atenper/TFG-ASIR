<?php
// Recibir y sanitizar los datos del formulario
$ct_id = intval($_POST['ct_id'] ?? 101);
$ct_hostname = escapeshellarg($_POST['ct_hostname'] ?? 'webapp');
$ct_password = escapeshellarg($_POST['ct_password'] ?? 'changeme');
$ct_template = escapeshellarg($_POST['ct_template'] ?? 'ubuntu-22.04-standard_22.04-1_amd64.tar.zst');
$ct_memory = intval($_POST['ct_memory'] ?? 1024);
$ct_cores = intval($_POST['ct_cores'] ?? 1);
$ct_storage = escapeshellarg($_POST['ct_storage'] ?? 'local-lvm');
$ct_net_bridge = escapeshellarg($_POST['ct_net_bridge'] ?? 'vmbr0');

// Construir el comando con variables extra-vars para Ansible
$cmd = "ansible-playbook -i ../ansible/inventario.ini ../ansible/crear_lxc.yml --extra-vars "
    . "\"ct_id=$ct_id "
    . "ct_hostname=$ct_hostname "
    . "ct_password=$ct_password "
    . "ct_template=$ct_template "
    . "ct_memory=$ct_memory "
    . "ct_cores=$ct_cores "
    . "ct_storage=$ct_storage "
    . "ct_net_bridge=$ct_net_bridge\"";

// Ejecutar el comando y capturar la salida y código de retorno
exec($cmd . " 2>&1", $output, $return_var);

// Mostrar resultado
if ($return_var === 0) {
    echo "<pre>Contenedor creado con éxito:\n" . implode("\n", $output) . "</pre>";
} else {
    echo "<pre>Error al crear contenedor:\n" . implode("\n", $output) . "</pre>";
}

// Redirigir a la página principal después de 3 segundos
header("Refresh: 3; url=../main.php");
?>
