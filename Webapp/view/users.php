<?php
include("../backEnd/list.php");
$search = $_GET['search'] ?? '';
$clientes = getClientes($search);
?>

<div class="container mb-3">
    <?php if(!empty($search)): ?>
        <div class="alert alert-info">
            Mostrando resultados para: <strong><?php echo htmlspecialchars($search) ?></strong>
            <a href="main.php?vista=users" class="btn btn-sm btn-danger ms-3">Limpiar búsqueda</a>
        </div>
    <?php endif; ?>
</div>

<!-- Modal de Edición -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">Editar Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="editUserModalBody">
                <!-- El contenido se cargará dinámicamente via AJAX -->
                <div class="text-center my-5">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Listado de Clientes -->
<div class="container my-4">
    <div id="usersGrid">
        <!-- Contenido dinámico -->
    </div>
    <div class="row">
        <?php foreach ($clientes as $cliente): ?>
            <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-4">
                <div class="card h-100">
                    <img src="<?php echo !empty($cliente['imagen']) ? htmlspecialchars($cliente['imagen']) : 'img/placeholder.png'; ?>" 
                         class="card-img-top img-thumbnail" 
                         alt="Imagen de <?php echo htmlspecialchars($cliente['name']); ?>"
                         style="height: 200px; object-fit: cover;">
                    
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?php echo htmlspecialchars($cliente['name']); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars($cliente['mail']); ?></p>
                        
                        <div class="mt-auto btn-group">
                            <button class="btn btn-primary btn-sm edit-user-btn" 
                                    data-user-id="<?php echo $cliente['id']; ?>">
                                Editar
                            </button>
                            <button onclick="confirmDelete(<?php echo $cliente['id']; ?>)" 
                                    class="btn btn-secondary btn-sm">
                                Borrar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
// Función para cargar el formulario de edición via AJAX
$(document).ready(function() {
    $('.edit-user-btn').click(function() {
        const userId = $(this).data('user-id');
        const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
        
        // Cargar el formulario via AJAX
        $('#editUserModalBody').load(`/backEnd/get_user_form.php?id=${userId}`, function() {
            modal.show();
            
            // Manejar el envío del formulario
            $('#editUserForm').submit(function(e) {
                e.preventDefault();
                
                $.ajax({
                    url: '/backEnd/update_user.php',
                    type: 'POST',
                    data: new FormData(this),
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        window.location.reload();
                    },
                    error: function(xhr) {
                        $('#editUserForm').prepend(
                            '<div class="alert alert-danger">Error al actualizar. Intente nuevamente.</div>'
                        );
                    }
                });
            });
        });
    });
});

function confirmDelete(id) {
    if (confirm('¿Estás seguro de querer eliminar este cliente?')) {
        window.location.href = '../backEnd/delete_user.php?id=' + id;
    }
}
// Preview de la imagen antes de subir
document.getElementById('imagenInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            document.getElementById('imagePreview').src = event.target.result;
            document.getElementById('imagePreview').style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
});

// Manejar el checkbox de eliminar imagen
document.getElementById('removeImage')?.addEventListener('change', function(e) {
    if (e.target.checked) {
        document.getElementById('imagePreview').style.display = 'none';
    } else {
        document.getElementById('imagePreview').style.display = 'block';
    }
});
</script>