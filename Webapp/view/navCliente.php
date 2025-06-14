<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow">
    <div class="container-fluid">
        <!-- Nombre de usuario con icono -->
        <a class="navbar-brand d-flex align-items-center me-3 col-1" href="#" data-bs-toggle="modal"
            data-bs-target="#accountModal">
            <i class="fas fa-user-circle me-2"></i>
            <?php echo htmlspecialchars($_SESSION['name'] ?? 'Usuario'); ?>
        </a>
        <!-- Barra de búsqueda (se colapsa en pantallas pequeñas) -->


        <!-- Botón para colapsar el navbar en pantallas pequeñas -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent"
            aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
            <i class="fas fa-bars"></i>
        </button>


        <!-- Contenido del navbar -->
        <div class="collapse navbar-collapse" id="navbarContent">
            <!-- Menú de navegación -->
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">


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