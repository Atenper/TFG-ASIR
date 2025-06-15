<?php
require_once "backEnd/appHeader.php";
require_once "backEnd/admin_check.php";
// Archivo: cargar_vista.php
$vista = $_GET['vista'] ?? 'inicio'; // Obtener la vista desde la solicitud AJAX

// Validar la vista
$vistasPermitidas = ['maquinas', 'users', 'usersSearch', 'buscar_maquinas'];
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/main.css" rel="stylesheet" />
    <link rel="icon" href="img/favicon.ico" type="image/x-icon">
</head>

<body>
    <!-- Navbar responsiva -->
    <?php include("view/nav.php"); ?>

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
    <!-- Modal crear usuario -->
    <div class="modal fade" id="userModal" tabindex="-1" role="dialog" aria-labelledby="userModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form id="createUserForm" action="backEnd/create_user.php" method="post" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="userModalLabel">Crear Usuario</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="formMessages"></div>

                        <div class="mb-3">
                            <label for="name" class="form-label">Nombre del nuevo cliente:</label>
                            <input type="text" class="form-control" id="name" name="name" maxlength="100" required />
                        </div>

                        <div class="mb-3">
                            <label for="mail" class="form-label">Correo electrónico:</label>
                            <input type="email" class="form-control" id="mail" name="mail" maxlength="100" required />
                        </div>

                        <div class="mb-3">
                            <label for="pass" class="form-label">Contraseña:</label>
                            <input type="password" class="form-control" id="pass" name="pass" maxlength="255"
                                required />
                            <div class="form-text">Mínimo 8 caracteres</div>
                        </div>

                        <div class="mb-3">
                            <label for="img" class="form-label">Imagen de perfil:</label>
                            <input type="file" class="form-control" id="img" name="img" accept="image/*" />

                            <!-- Preview de la imagen -->
                            <div class="mt-3 text-center">
                                <img id="imagePreview" src="https://via.placeholder.com/150" class="img-thumbnail"
                                    style="max-width: 150px; max-height: 150px; display: none;">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <span id="submitText">Guardar cliente</span>
                            <span id="submitSpinner" class="spinner-border spinner-border-sm" role="status"
                                aria-hidden="true" style="display: none;"></span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

<!-- Modal crear contenedor LXC -->
<div class="modal fade" id="crearLXCModal" tabindex="-1" role="dialog" aria-labelledby="crearLXCModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="backEnd/crear_lxc.php" method="post">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="crearLXCModalLabel">Crear contenedor LXC</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <!-- ID del contenedor -->
                    <div class="mb-3">
                        <label for="ct_id" class="form-label">ID del contenedor:</label>
                        <input type="number" class="form-control" id="ct_id" name="ct_id" value="201" min="201" max="9999" required>
                    </div>

                    <!-- Hostname del contenedor -->
                    <div class="mb-3">
                        <label for="ct_hostname" class="form-label">Hostname:</label>
                        <input type="text" class="form-control" id="ct_hostname" name="ct_hostname" value="webapp" required>
                    </div>

                    <!-- Distribución base -->
                    <div class="mb-3">
                        <label for="ct_template" class="form-label">Plantilla:</label>
                        <select class="form-control" id="ct_template" name="ct_template" required>
                            <option value="ubuntu-22.04-standard_22.04-1_amd64.tar.zst" selected>Ubuntu 22.04</option>
                            <option value="debian-11-standard_11.7-1_amd64.tar.zst">Debian 11</option>
                            <option value="alpine-3.18-default_3.18-1_amd64.tar.xz">Alpine 3.18</option>
                        </select>
                    </div>

                    <!-- Configuración de recursos -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="ct_memory" class="form-label">Memoria (MB):</label>
                            <input type="number" class="form-control" id="ct_memory" name="ct_memory" value="1024" min="128" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="ct_cores" class="form-label">Núcleos CPU:</label>
                            <input type="number" class="form-control" id="ct_cores" name="ct_cores" value="1" min="1" max="16" required>
                        </div>
                    </div>

                    <!-- Contraseña root -->
                    <div class="mb-3">
                        <label for="ct_password" class="form-label">Contraseña root:</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="ct_password" name="ct_password" value="changeme" required>
                            <button class="btn btn-outline-secondary" type="button" id="showPasswordBtn">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                        <div class="form-text">La contraseña debe tener al menos 8 caracteres</div>
                    </div>

                    <!-- Opciones avanzadas -->
                    <div class="accordion mb-3" id="advancedOptions">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#advancedCollapse" aria-expanded="false" aria-controls="advancedCollapse">
                                    Opciones avanzadas
                                </button>
                            </h2>
                            <div id="advancedCollapse" class="accordion-collapse collapse" data-bs-parent="#advancedOptions">
                                <div class="accordion-body">
                                    <!-- Configuración de almacenamiento -->
                                    <div class="mb-3">
                                        <label for="ct_storage" class="form-label">Almacenamiento:</label>
                                        <select class="form-control" id="ct_storage" name="ct_storage">
                                            <option value="local-lvm" selected>local-lvm</option>
                                            <option value="local">local</option>
                                            <option value="ceph-storage">ceph-storage</option>
                                        </select>
                                    </div>

                                    <!-- Configuración de red -->
                                    <div class="mb-3">
                                        <label for="ct_net_bridge" class="form-label">Interfaz de red:</label>
                                        <select class="form-control" id="ct_net_bridge" name="ct_net_bridge">
                                            <option value="vmbr0" selected>vmbr0</option>
                                            <option value="vmbr1">vmbr1</option>
                                            <option value="vmbr2">vmbr2</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Crear Contenedor</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal instalar paquete -->
<div class="modal fade" id="installPackageModal" tabindex="-1" aria-labelledby="installPackageModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="installPackageModalLabel">Instalar Aplicación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="installPackageForm">
                    <input type="hidden" id="machineId" name="machineId">
                    <input type="hidden" id="machineType" name="machineType">
                    
                    <div class="mb-3">
                        <label class="form-label">Seleccione la aplicación a instalar:</label>
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-primary btn-install" data-app="wordpress">
                                <i class="fa-brands fa-wordpress"></i> WordPress
                            </button>
                            <button type="button" class="btn btn-primary btn-install" data-app="nextcloud">
                                <i class="fa-solid fa-cloud"></i> Nextcloud
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
    
    <!-- jQuery y Bootstrap Bundle (Popper incluido) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

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
            const vistasPermitidas = ['maquinas', 'users', 'usersSearch', 'buscar_maquinas', 'inicio'];
            if (!vistasPermitidas.includes(vista)) {
                vista = 'inicio'; // Vista por defecto
            }

            $.ajax({
                url: `view/${vista}.php`,
                type: 'GET',
                data: params,
                success: function (response) {
                    $('#contenido').html(response);

                    // Inicializar lógica específica de la vista
                    if (vista === 'maquinas') {
                        console.log("Vista 'maquinas' cargada");
                        loadData(); // Llamar a la función para cargar datos
                    } else if (vista === 'buscar_maquinas') {
                        console.log("Vista 'buscar_maquinas' cargada");
                        // Aquí puedes agregar lógica específica para la búsqueda de máquinas
                    }

                    // Actualizar URL para ambas vistas principales
                    const newUrl = `main.php?vista=${vista}` + (params.search ? `&search=${encodeURIComponent(params.search)}` : '');
                    history.pushState({}, '', newUrl);
                },
                error: function () {
                    $('#contenido').html('<p>Error al cargar la vista.</p>');
                }
            });
        }

        function realizarBusqueda(event) {
            event.preventDefault();

            const tipoBusqueda = document.getElementById('tipo').value; // 'users' o 'maquinas'
            const searchTerm = document.getElementById('searchInput').value.trim();

            if (!searchTerm) {
                alert('Por favor, ingrese un término de búsqueda.');
                return;
            }

            if (tipoBusqueda === 'maquinas') {
                // Redirigir a la vista de búsqueda de máquinas
                cargarVista('buscar_maquinas', { search: searchTerm });
            } else {
                // Mantener la lógica existente para usuarios
                cargarVista(tipoBusqueda, { search: searchTerm });
            }
        }

        // Al cargar la página
        $(document).ready(function () {
            const urlParams = new URLSearchParams(window.location.search);
            const vista = urlParams.get('vista') || 'maquinas'; // Default a maquinas

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
            // Preview de la imagen antes de subir
            $('#img').change(function (e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function (event) {
                        $('#imagePreview').attr('src', event.target.result).show();
                    };
                    reader.readAsDataURL(file);
                }
            });

            // Envío del formulario con AJAX
            $('#createUserForm').submit(function (e) {
                e.preventDefault();

                // Mostrar spinner y deshabilitar botón
                $('#submitText').text('Guardando...');
                $('#submitSpinner').show();
                $('#submitBtn').prop('disabled', true);

                // Limpiar mensajes anteriores
                $('#formMessages').empty();

                // Crear FormData para enviar archivos
                const formData = new FormData(this);

                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        if (response.success) {
                            // Mostrar mensaje de éxito
                            $('#formMessages').html(`
                        <div class="alert alert-success">
                            Usuario creado correctamente
                        </div>
                    `);

                            // Recargar la lista de usuarios después de 1.5 segundos
                            setTimeout(() => {
                                $('#userModal').modal('hide');
                                cargarVista('users');
                            }, 1500);
                        } else {
                            // Mostrar mensaje de error
                            $('#formMessages').html(`
                        <div class="alert alert-danger">
                            ${response.message || 'Error al crear el usuario'}
                        </div>
                    `);
                        }
                    },
                    error: function (xhr) {
                        let errorMsg = 'Error en la conexión';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        $('#formMessages').html(`
                    <div class="alert alert-danger">
                        ${errorMsg}
                    </div>
                `);
                    },
                    complete: function () {
                        // Restaurar botón
                        $('#submitText').text('Guardar cliente');
                        $('#submitSpinner').hide();
                        $('#submitBtn').prop('disabled', false);
                    }
                });
            });

            // Resetear formulario cuando se cierra el modal
            $('#userModal').on('hidden.bs.modal', function () {
                $('#createUserForm')[0].reset();
                $('#imagePreview').hide();
                $('#formMessages').empty();
            });
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
        // Script para mostrar/ocultar contraseña y generar una aleatoria
document.getElementById('showPasswordBtn').addEventListener('click', function() {
    const passwordInput = document.getElementById('ct_password');
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        this.innerHTML = '<i class="fa-solid fa-eye-slash"></i>';
    } else {
        passwordInput.type = 'password';
        this.innerHTML = '<i class="fa-solid fa-eye"></i>';
    }
});
document.addEventListener('DOMContentLoaded', () => {
    const vistaActual = new URLSearchParams(window.location.search).get('vista') || 'inicio';

    if (vistaActual === 'maquinas') {
        console.log("Inicializando lógica para la vista 'maquinas'");

        const refreshBtn = document.getElementById('refreshBtn');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => {
                console.log("Botón de actualización presionado");
                const loadingIndicator = document.getElementById('loadingIndicator');
                const machinesGrid = document.getElementById('machinesGrid');
                const errorAlert = document.getElementById('errorAlert');

                if (loadingIndicator) loadingIndicator.classList.remove('d-none');
                if (machinesGrid) machinesGrid.classList.add('d-none');
                if (errorAlert) errorAlert.classList.add('d-none');

                loadData();
            });
        }
    } else if (vistaActual === 'users') {
        console.log("Inicializando lógica para la vista 'users'");
        // Agrega aquí el código específico para la vista 'users'
    } else {
        console.log(`No hay lógica específica para la vista '${vistaActual}'`);
    }
});
async function loadData() {
    try {
        console.log("Iniciando carga de datos...");
        const response = await fetch('../backEnd/gt_vms.php');
        console.log("Respuesta HTTP:", response.status);

        if (!response.ok) {
            const errorText = await response.text();
            console.error("Error en la respuesta:", errorText);
            throw new Error(`Error ${response.status}: ${errorText}`);
        }

        const data = await response.json();
        console.log("Datos recibidos:", data);

        if (!data.success) {
            throw new Error(data.error || "Error en la estructura de datos");
        }

        renderDashboard(data.data);
        document.getElementById('machinesGrid').classList.remove('d-none');
    } catch (err) {
        console.error("Error completo:", err);
        document.getElementById('errorText').textContent = err.message;
        document.getElementById('errorAlert').classList.remove('d-none');
    } finally {
        document.getElementById('loadingIndicator').classList.add('d-none');
    }
}
    </script>
</body>

</html>