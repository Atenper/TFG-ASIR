<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow">
    <div class="container-fluid">
        <!-- Nombre de usuario con icono -->
        <a class="navbar-brand d-flex align-items-center me-3 col-1" href="#" data-bs-toggle="modal"
            data-bs-target="#accountModal">
            <i class="fas fa-user-circle me-2"></i>
            <?php echo htmlspecialchars($_SESSION['name'] ?? 'Usuario'); ?>
        </a>

        <!-- Botón para colapsar el navbar en pantallas pequeñas -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent"
            aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Contenido del navbar -->
        <div class="collapse navbar-collapse" id="navbarContent">
            <!-- Formulario de búsqueda comentado -->
            <!--
            <form class="d-flex flex-grow-1 mx-lg-3 my-2 my-lg-0" onsubmit="realizarBusqueda(event)">
                <div class="input-group">
                    <select name="tipo" id="tipo" class="form-select" style="max-width: 150px;">
                        <option value="users">Clientes</option>
                        <option value="maquinas">Máquinas</option>
                    </select>
                    <input type="text" class="form-control" id="searchInput" placeholder="Buscar..." name="search">
                    <button class="btn btn-primary" type="submit">
                        <i class="fa-solid fa-magnifying-glass"></i> Buscar
                    </button>
                </div>
            </form>
            -->
            <!-- Menú de navegación -->
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
                <li class="nav-item">
                    <button class="nav-link" type="button" onclick="cargarVista('maquinas')">
                        <i class="fa-solid fa-file-invoice"></i> Máquinas
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" type="button" onclick="cargarVista('users')">
                        <i class="fa-solid fa-users"></i> Usuarios
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" type="button" data-bs-toggle="modal" data-bs-target="#userModal">
                        <i class="fas fa-download"></i> Crear usuario
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" type="button" data-bs-toggle="modal" data-bs-target="#crearLXCModal">
                        <i class="fas fa-file-export"></i> Crear Máquina
                    </button>
                </li>
                <li class="nav-item">
                    <form action="backEnd/mngSession.php" method="post" class="d-flex align-items-center">
                        <input type="hidden" name="op" value="sc">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                        <button type="submit" class="btn btn-outline-danger btn-sm ms-3" title="Cerrar sesión">
                            <i class="fas fa-sign-out-alt"></i>
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Incluir FontAwesome para iconos -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>