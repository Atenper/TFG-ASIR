<?php
include("functions.php");

$id = $_GET['id'] ?? 0;
$cliente = getClienteById($id);

if (!$cliente) {
    echo '<div class="alert alert-danger">Cliente no encontrado</div>';
    exit;
}
?>

<form id="editUserForm">
    <input type="hidden" name="id" value="<?php echo $cliente['id']; ?>">

    <div class="mb-3">
        <label class="form-label">Nombre</label>
        <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($cliente['name']); ?>"
            required>
    </div>

    <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" class="form-control" name="mail" value="<?php echo htmlspecialchars($cliente['mail']); ?>"
            required>
    </div>

    <div class="mb-3">
        <label class="form-label">Imagen de perfil</label>
        <input type="file" class="form-control" id="imagenInput" name="imagen" accept="image/*">

        <!-- Preview de la imagen -->
        <div class="mt-3 text-center">
            <img id="imagePreview"
                src="<?php echo !empty($cliente['imagen']) ? htmlspecialchars($cliente['imagen']) : 'https://via.placeholder.com/150'; ?>"
                class="img-thumbnail"
                style="max-width: 150px; max-height: 150px; display: <?php echo !empty($cliente['imagen']) ? 'block' : 'none'; ?>;">
        </div>

        <?php if (!empty($cliente['imagen'])): ?>
            <div class="form-check mt-2">
                <input class="form-check-input" type="checkbox" name="remove_image" id="removeImage">
                <label class="form-check-label" for="removeImage">Eliminar imagen actual</label>
            </div>
        <?php endif; ?>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
    </div>
</form>