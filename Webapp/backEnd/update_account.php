<?php
require_once "functions.php";

header('Content-Type: application/json');

try {
    // Verificar sesión
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['userId'])) {
        throw new Exception('No autenticado', 401);
    }

    // Validar CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        throw new Exception('Token CSRF inválido', 403);
    }

    // Sanitizar inputs
    $name = sanitize($_POST['username'] ?? '', 'string');
    $email = sanitize($_POST['email'] ?? '', 'mail');
    $password = sanitize($_POST['password'] ?? '', 'pass');
    $removeImage = isset($_POST['remove_image']);

    // Validaciones básicas
    if (empty($name) || empty($email)) {
        throw new Exception('Nombre y correo electrónico son requeridos', 400);
    }

    // Verificar si el correo ya existe (excepto para el usuario actual)
    $existing = doQuery("SELECT id FROM users WHERE mail = '$email' AND id != {$_SESSION['userId']} LIMIT 1");
    if ($existing) {
        throw new Exception('El correo electrónico ya está registrado', 400);
    }

    // Procesar imagen si se subió
    $imagenPath = null;
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/avatars/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Validar tipo de archivo
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = mime_content_type($_FILES['imagen']['tmp_name']);
        if (!in_array($fileType, $allowedTypes)) {
            throw new Exception('Solo se permiten imágenes JPG, PNG o GIF', 400);
        }

        // Validar tamaño (max 2MB)
        if ($_FILES['imagen']['size'] > 2097152) {
            throw new Exception('La imagen es demasiado grande (máx. 2MB)', 400);
        }

        // Eliminar imagen anterior si existe
        if (!empty($_SESSION['imagen'])) {
            $oldImagePath = $_SERVER['DOCUMENT_ROOT'] . parse_url($_SESSION['imagen'], PHP_URL_PATH);
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
            throw new Exception('Error al guardar la imagen', 500);
        }
    } elseif ($removeImage && !empty($_SESSION['imagen'])) {
        // Eliminar imagen existente
        $oldImagePath = $_SERVER['DOCUMENT_ROOT'] . parse_url($_SESSION['imagen'], PHP_URL_PATH);
        if (file_exists($oldImagePath)) {
            unlink($oldImagePath);
        }
        $imagenPath = ''; // Cadena vacía para eliminar
    }

    // Construir consulta de actualización
    $updates = [
        "name = '$name'",
        "mail = '$email'"
    ];

    // Actualizar contraseña si se proporcionó
    if (!empty($password)) {
        if (strlen($password) < 8) {
            throw new Exception('La contraseña debe tener al menos 8 caracteres', 400);
        }
        $hashedPass = password_hash($password, PASSWORD_BCRYPT);
        $updates[] = "pass = '$hashedPass'";
    }

    // Actualizar imagen si es necesario
    if ($imagenPath !== null) {
        $updates[] = "imagen = " . ($imagenPath ? "'$imagenPath'" : "NULL");
    }

    $query = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = {$_SESSION['userId']}";
    $result = doQuery($query);

    if (!$result) {
        throw new Exception('Error al actualizar la cuenta', 500);
    }

    // Actualizar datos en sesión
    $_SESSION['name'] = $name;
    $_SESSION['mail'] = $email;
    if ($imagenPath !== null) {
        $_SESSION['imagen'] = $imagenPath ?: null;
    }

    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'message' => 'Cuenta actualizada correctamente',
        'data' => [
            'name' => $name,
            'mail' => $email,
            'imagen' => $imagenPath ?: $_SESSION['imagen'] ?? null
        ]
    ]);

} catch (Exception $e) {
    http_response_code($e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>