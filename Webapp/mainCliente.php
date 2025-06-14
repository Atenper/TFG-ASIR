<?php
//require_once "backEnd/admin_check.php";
require_once "backEnd/appHeader.php";
// Archivo: cargar_vista.php
$vista = $_GET['vista'] ?? 'inicio'; // Obtener la vista desde la solicitud AJAX

// Validar la vista
$vistasPermitidas = ['maquinas'];
if (!in_array($vista, $vistasPermitidas)) {
    $vista = 'inicio'; // Vista por defecto si no es válida
}

// Incluir el archivo de mensajes
$mensajes = include("view/messages_es.php"); // Ajusta la ruta según tu estructura

// Verificar si hay un mensaje en la sesión
$mensaje_key = $_SESSION['msg'] ?? ''; // Obtener la clave del mensaje
$mensaje_texto = '';
$mensaje_tipo = '';

if ($mensaje_key && isset($mensajes[$mensaje_key])) {
    $mensaje_texto = $mensajes[$mensaje_key][0]; // Texto del mensaje
    $mensaje_tipo = $mensajes[$mensaje_key][1]; // Tipo de mensaje (éxito, error, etc.)
    unset($_SESSION['msg']); // Eliminar el mensaje de la sesión
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Menú de Usuario</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <!-- Bootstrap 5 CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/main.css" rel="stylesheet" />
</head>

<body>
    <!-- Navbar responsiva -->
    <?php include("view/navCliente.php"); ?>
    <?php include("view/maquinasCliente.php"); ?>
    <!-- Contenedor del mensaje centrado -->
    <div id="mensaje-container" class="alert alert-<?php echo $mensaje_tipo; ?> alert-dismissible fade show"
        role="alert" style="display: <?php echo $mensaje_texto ? 'block' : 'none'; ?>;">
        <span id="mensaje-texto"><?php echo $mensaje_texto; ?></span>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
    <!-- Párrafo predeterminado -->
    <div id="contenido" class="text-secondary fs-4">
    </div>
    </div>
    <!-- Modal para gestionar cuenta -->
    <div class="modal fade" id="accountModal" tabindex="-1" aria-labelledby="accountModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="accountForm" action="backEnd/update_account.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                    <div class="modal-header">
                        <h5 class="modal-title" id="accountModalLabel">Gestionar cuenta</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div id="accountMessages"></div>

                        <!-- Avatar -->
                        <div class="text-center mb-4">
                            <img id="accountImagePreview"
                                src="<?php echo !empty($_SESSION['imagen']) ? htmlspecialchars($_SESSION['imagen']) : 'https://via.placeholder.com/150'; ?>"
                                class="img-thumbnail rounded-circle"
                                style="width: 150px; height: 150px; object-fit: cover;">
                            <div class="mt-2">
                                <input type="file" class="form-control d-none" id="accountImageInput" name="imagen"
                                    accept="image/*">
                                <button type="button" class="btn btn-sm btn-outline-primary"
                                    onclick="document.getElementById('accountImageInput').click()">
                                    Cambiar imagen
                                </button>
                                <?php if (!empty($_SESSION['imagen'])): ?>
                                    <button type="button" class="btn btn-sm btn-outline-danger" id="removeImageBtn">
                                        Eliminar
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Campos del formulario (nombre, email, contraseña) -->
                        <div class="mb-3">
                            <label for="username" class="form-label">Nombre de usuario</label>
                            <input type="text" class="form-control" id="username" name="username"
                                value="<?php echo htmlspecialchars($_SESSION['name'] ?? ''); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Correo electrónico</label>
                            <input type="email" class="form-control" id="email" name="email"
                                value="<?php echo htmlspecialchars($_SESSION['mail'] ?? ''); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Nueva contraseña</label>
                            <input type="password" class="form-control" id="password" name="password"
                                placeholder="Dejar en blanco para mantener la actual">
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirmar nueva contraseña</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- jQuery y Bootstrap Bundle (Popper incluido) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>

    <!-- Script para mostrar el mensaje -->
    <script>
        $(document).ready(function () {
            const mensajeContainer = $('#mensaje-container');
            const mensajeTexto = $('#mensaje-texto');

            // Ocultar el mensaje después de 5 segundos
            if (mensajeTexto.text().trim() !== '') {
                setTimeout(function () {
                    mensajeContainer.fadeOut();
                }, 5000);
            }
        });

        $(document).ready(function () {
            $('#cliente_buscador').on('input', function () {
                const query = $(this).val();
                const resultadosDiv = $('#resultados_clientes');

                if (query.length >= 2) {
                    fetch(`backEnd/buscar_clientes.php?q=${encodeURIComponent(query)}`)
                        .then(response => response.json())
                        .then(data => {
                            resultadosDiv.empty().show(); // Limpiar y mostrar el cuadro de sugerencias
                            if (data.length === 0) {
                                resultadosDiv.append('<div class="suggestion">No se encontraron resultados</div>');
                            } else {
                                data.forEach(usuario => {
                                    const elemento = $(`<div class="suggestion">${usuario.name} (${usuario.mail})</div>`);
                                    elemento.on('click', function () {
                                        $('#cliente_buscador').val(usuario.name);
                                        $('#cliente_id').val(usuario.id);
                                        resultadosDiv.hide(); // Ocultar sugerencias tras la selección
                                    });
                                    resultadosDiv.append(elemento);
                                });
                            }
                        })
                        .catch(error => console.error('Error:', error));
                } else {
                    resultadosDiv.hide(); // Ocultar si la búsqueda es muy corta
                }
            });

            // Ocultar sugerencias al hacer clic fuera
            $(document).on('click', function (e) {
                if (!$('#cliente_buscador').is(e.target) && !$('#resultados_clientes').is(e.target) && $('#resultados_clientes').has(e.target).length === 0) {
                    $('#resultados_clientes').hide();
                }
            });
        });
        function cargarVista(vista, params = {}) {
            // Validar que la vista esté permitida (puedes usar las mismas que en PHP)
            const vistasPermitidas = ['facturas', 'inicio'];
            if (!vistasPermitidas.includes(vista)) {
                vista = 'facturas'; // Vista por defecto
            }

            $.ajax({
                url: `view/${vista}Cliente.php`,
                type: 'GET',
                data: params,
                success: function (response) {
                    $('#contenido').html(response);
                    // Actualizar URL para ambas vistas principales
                    if (vista === 'facturas') {
                        const newUrl = `mainCliente.php?vista=${vista}` + (params.search ? `&search=${encodeURIComponent(params.search)}` : '');
                        history.pushState({}, '', newUrl);
                    }
                },
                error: function () {
                    $('#contenido').html('<p>Error al cargar la vista.</p>');
                }
            });
        }

        function realizarBusqueda(event) {
            event.preventDefault();
            const searchTerm = $('#searchInput').val().trim();
            const tipoBusqueda = $('#tipo').val();

            if (searchTerm) {
                cargarVista(tipoBusqueda, {
                    search: searchTerm
                });
            } else {
                // Si no hay término de búsqueda, simplemente cargar la vista sin parámetros
                cargarVista(tipoBusqueda);
            }
        }
        // Al cargar la página
        $(document).ready(function () {
            const urlParams = new URLSearchParams(window.location.search);
            const vista = urlParams.get('vista') || 'facturas'; // Default a facturas

            // Cargar vista con parámetros
            const searchParam = urlParams.get('search');
            if (searchParam) {
                cargarVista(vista, {
                    search: searchParam
                });
                // Establecer los valores en los inputs
                $('#searchInput').val(searchParam);
                $('#tipo').val(vista); // Establecer el select según la vista
            } else {
                cargarVista(vista);
            }
        });
        $(document).ready(function () {
            // Preview de imagen
            $('#accountImageInput').change(function (e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function (event) {
                        $('#accountImagePreview').attr('src', event.target.result);
                    };
                    reader.readAsDataURL(file);
                }
            });

            // Eliminar imagen
            $('#removeImageBtn').click(function () {
                if (confirm('¿Estás seguro de querer eliminar tu imagen de perfil?')) {
                    $('#accountImagePreview').attr('src', 'https://via.placeholder.com/150');
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'remove_image',
                        value: '1'
                    }).appendTo('#accountForm');
                }
            });

            // Validación del formulario
            $('#accountForm').submit(function (e) {
                e.preventDefault();

                const password = $('#password').val();
                const confirmPassword = $('#confirm_password').val();

                if (password !== confirmPassword) {
                    $('#accountMessages').html(
                        '<div class="alert alert-danger">Las contraseñas no coinciden</div>'
                    );
                    return false;
                }

                // Envío AJAX
                const formData = new FormData(this);

                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        if (response.success) {
                            $('#accountMessages').html(
                                '<div class="alert alert-success">' + response.message + '</div>'
                            );
                            setTimeout(() => {
                                location.reload();
                            }, 1500);
                        } else {
                            $('#accountMessages').html(
                                '<div class="alert alert-danger">' + response.message + '</div>'
                            );
                        }
                    },
                    error: function (xhr) {
                        $('#accountMessages').html(
                            '<div class="alert alert-danger">Error en la conexión</div>'
                        );
                    }
                });
            });
        });
    </script>
</body>

</html>