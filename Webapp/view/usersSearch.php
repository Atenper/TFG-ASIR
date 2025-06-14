<?php
include("../backEnd/list.php");
$text = $_GET['texto'];
$clientes = getClientesLike($text);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        .card{
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: scale(1.025);
            box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.3);
        }

        .btn {
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease, background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-5px);
            box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.3);
        }

    </style>
</head>
<body>
<div class="container my-4">
    <h2>Resultados de búsqueda de clientes</h2>
    <div id="usersGrid">
        <!-- Aquí se mostrarán los resultados de búsqueda -->
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const searchParam = new URLSearchParams(window.location.search).get('search');
        if (searchParam) {
            console.log(`Buscando clientes con el término: ${searchParam}`);
            // Aquí puedes agregar lógica para cargar los resultados de búsqueda
        }
    });
</script>

<div class="container my-4">
    <div class="row">
        <?php foreach ($clientes as $cliente): ?>
            <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-4">
                <div class="card">
                    <img src="img/oficina2.jpg" class="card-img-top" alt="Imagen de perfil">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $cliente['name']; ?></h5>
                        <p class="card-text"><?php echo $cliente['mail']; ?></p>
                        <a href="#" class="btn btn-primary">Editar</a>
                        <a href="#" class="btn btn-secondary">Borrar</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>   
</body>
</html>
