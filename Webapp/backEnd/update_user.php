<?php
include("functions.php");

header('Content-Type: application/json');

try {
    // Verificar método
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido', 405);
    }

    // Sanitizar inputs
    $id = sanitize($_POST['id'] ?? 0, 'int');
    $name = sanitize($_POST['name'] ?? '', 'string');
    $mail = sanitize($_POST['mail'] ?? '', 'mail');
    $removeImage = isset($_POST['remove_image']);

    // Validaciones
    if (empty($id) || empty($name) || empty($mail)) {
        throw new Exception('Datos requeridos faltantes', 400);
    }

    // Obtener usuario actual para manejar la imagen anterior
    $currentUser = getClienteById($id);
    if (!$currentUser) {
        throw new Exception('Usuario no encontrado', 404);
    }

    // Procesar imagen
    $imagenPath = $currentUser['imagen'] ?? ''; // Mantener la actual por defecto

    // Eliminar imagen si se solicitó
    if ($removeImage && !empty($currentUser['imagen'])) {
        $oldImagePath = $_SERVER['DOCUMENT_ROOT'] . parse_url($currentUser['imagen'], PHP_URL_PATH);
        if (file_exists($oldImagePath)) {
            unlink($oldImagePath);
        }
        $imagenPath = ''; // Cadena vacía en DB
    }

    // Procesar nueva imagen si se subió
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/avatars/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Validar tipo de archivo
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = mime_content_type($_FILES['imagen']['tmp_name']);
        if (!in_array($fileType, $allowedTypes)) {
            throw new Exception('Tipo de archivo no permitido. Solo JPG, PNG o GIF.', 400);
        }

        // Validar tamaño (max 2MB)
        if ($_FILES['imagen']['size'] > 2097152) {
            throw new Exception('El archivo es demasiado grande. Máximo 2MB.', 400);
        }

        // Eliminar imagen anterior si existe
        if (!empty($currentUser['imagen'])) {
            $oldImagePath = $_SERVER['DOCUMENT_ROOT'] . parse_url($currentUser['imagen'], PHP_URL_PATH);
            if (file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }
        }

        // Generar nombre único para el archivo
        $extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $name) . '.' . $extension;
        $targetPath = $uploadDir . $filename;

        // Mover el archivo subido
        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $targetPath)) {
            $imagenPath = '/uploads/avatars/' . $filename;
        } else {
            throw new Exception('Error al mover el archivo subido', 500);
        }
    }

    // Construir consulta
    $query = "UPDATE users SET name = '$name', mail = '$mail'";
    if ($removeImage || isset($_FILES['imagen'])) {
        $query .= ", imagen = '$imagenPath'";
    }
    $query .= " WHERE id = $id";

    // Ejecutar
    if (!doQuery($query)) {
        throw new Exception('Error al actualizar en la base de datos', 500);
    }

    echo json_encode([
        'success' => true,
        'newImageUrl' => !empty($imagenPath) ? $imagenPath : 'https://via.placeholder.com/150'
    ]);
    
} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}