<?php
include("functions.php"); // Asegúrate de incluir las funciones necesarias

$id = $_GET['id'] ?? 0;

if (deleteFactura($id)) { // Necesitarás esta función
    header("Location: ../main.php?vista=facturas&success=1");
} else {
    header("Location: ../main.php?vista=facturas&error=1");
}
exit;
?>