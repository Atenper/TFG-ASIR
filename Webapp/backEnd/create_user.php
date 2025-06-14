<?php
require_once "functions.php";

header('Content-Type: application/json');

try {
    // Verificar método
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido', 405);
    }

    // Sanitizar inputs
    $name = sanitize($_POST['name'] ?? '', 'string');
    $mail = sanitize($_POST['mail'] ?? '', 'mail');
    $pass = sanitize($_POST['pass'] ?? '', 'pass');

    // Validaciones básicas
    if (empty($name) || empty($mail) || empty($pass)) {
        throw new Exception('Todos los campos son requeridos', 400);
    }

    if (strlen($pass) < 8) {
        throw new Exception('La contraseña debe tener al menos 8 caracteres', 400);
    }

    // Verificar si el correo ya existe
    $existing = doQuery("SELECT id FROM users WHERE mail = '$mail' LIMIT 1");
    if ($existing) {
        throw new Exception('El correo electrónico ya está registrado', 400);
    }

    // Procesar imagen si se subió
    $imagenPath = null;
    if (isset($_FILES['img']) && $_FILES['img']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/avatars/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Validar tipo de archivo
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = mime_content_type($_FILES['img']['tmp_name']);
        if (!in_array($fileType, $allowedTypes)) {
            throw new Exception('Solo se permiten imágenes JPG, PNG o GIF', 400);
        }

        // Validar tamaño (max 2MB)
        if ($_FILES['img']['size'] > 2097152) {
            throw new Exception('La imagen es demasiado grande (máx. 2MB)', 400);
        }

        // Generar nombre único para el archivo
        $extension = pathinfo($_FILES['img']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $name) . '.' . $extension;
        $targetPath = $uploadDir . $filename;

        // Mover el archivo subido
        if (move_uploaded_file($_FILES['img']['tmp_name'], $targetPath)) {
            $imagenPath = '/uploads/avatars/' . $filename;
        } else {
            throw new Exception('Error al guardar la imagen', 500);
        }
    }

    // Hash de la contraseña
    $hashedPass = password_hash($pass, PASSWORD_BCRYPT);

    // Insertar en la base de datos
    $query = "INSERT INTO users (name, mail, pass, imagen, tipo) VALUES (
        '$name',
        '$mail',
        '$hashedPass',
        " . ($imagenPath ? "'$imagenPath'" : "NULL") . ",
        1
    )";

    $result = doQuery($query);
    if (!$result) {
        // Si falla, eliminar la imagen subida (si existe)
        if ($imagenPath && file_exists($_SERVER['DOCUMENT_ROOT'] . $imagenPath)) {
            unlink($_SERVER['DOCUMENT_ROOT'] . $imagenPath);
        }
        throw new Exception('Error al crear el usuario en la base de datos', 500);
    }

    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'message' => 'Usuario creado correctamente'
    ]);

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}