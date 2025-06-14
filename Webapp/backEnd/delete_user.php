<?php
include("functions.php");

if (!isset($_GET['id'])) {
    header("Location: ../main.php?vista=users&error=1");
    exit;
}

$id = $_GET['id'];
if (deleteCliente($id)) {
    header("Location: ../main.php?vista=users&success=1");
} else {
    header("Location: ../main.php?vista=users&error=2");
}
exit;
?>