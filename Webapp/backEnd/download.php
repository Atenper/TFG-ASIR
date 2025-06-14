<?php
include("functions.php"); // Asegúrate de incluir las funciones necesarias

$id = $_GET['id'] ?? 0;
$factura = getFacturaById($id); // Necesitarás esta función

if ($factura && file_exists($factura['ruta_archivo'])) {
    // Actualizar fecha de descarga
    updateDownloadDate($id); // Necesitarás esta función
    
    // Configurar headers para la descarga
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="'.basename($factura['ruta_archivo']).'"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($factura['ruta_archivo']));
    readfile($factura['ruta_archivo']);
    exit;
} else {
    // Redirigir con mensaje de error
    header("Location: ../main.php?vista=facturas&error=1");
    exit;
}
?>