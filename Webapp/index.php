<?php
// Detectar el idioma desde GET, Sesión o Configuración Predeterminada
$default_lang = 'es'; // Español por defecto
$lang = isset($_GET['lang']) ? $_GET['lang'] : (isset($_SESSION['lang']) ? $_SESSION['lang'] : $default_lang);

// Validar que el idioma existe, si no, usar español
$available_languages = ['es', 'en'];
if (!in_array($lang, $available_languages)) {
    $lang = $default_lang;
}

// Cargar el diccionario de mensajes
$messages = include "view/messages_$lang.php";

// Obtener el mensaje (ahora cada mensaje es un array: [0] => texto, [1] => tipo)
$msg = (isset($_GET['msg']) && array_key_exists($_GET['msg'], $messages))
    ? $messages[$_GET['msg']]
    : [];
?>
<!DOCTYPE html>
<html lang="en" class="h-100">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Login</title>

    <!-- Bootstrap 5 CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet" />
    <link href="css/main.css" rel="stylesheet" />

    <!-- Bootstrap JS + jQuery -->
    <script src="js/jquery-3.6.0.min.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function () {
            // Recoger mensaje PHP (ahora usando índices 0 y 1)
            var phpMsgText = "<?php echo isset($msg[0]) ? addslashes($msg[0]) : ''; ?>";
            var phpMsgType = "<?php echo isset($msg[1]) ? addslashes($msg[1]) : ''; ?>";

            if (phpMsgText.length > 0) {
                // Ocultar el párrafo y mostrar el mensaje en su lugar
                $('#left-paragraph').hide();
                var alertClass = 'alert-secondary'; // valor por defecto
                if (phpMsgType === 'success') {
                    alertClass = 'custom-alert-success';
                } else if (phpMsgType === 'error') {
                    alertClass = 'custom-alert-error';
                } else if (phpMsgType === 'alert') {
                    alertClass = 'custom-alert-alert';
                }
                $('#left-message').removeClass('d-none')
                    .removeClass('alert-secondary')
                    .addClass(alertClass)
                    .text(phpMsgText);

                // Tras 5 segundos, oculta el mensaje y vuelve a mostrar el párrafo
                setTimeout(function () {
                    $('#left-message').addClass('d-none');
                    $('#left-paragraph').fadeIn();
                }, 5000);
            }

            // Efecto de fadeIn inicial en la tarjeta
            $('.custom-card').hide().fadeIn(1500);
        });
    </script>
</head>

<body class="h-100">
    <div class="container-fluid m-0 p-0 h-100 bg-fondo">
        <div class="row g-0 h-100">

            <!-- Panel Izquierdo -->
            <div class="col-md-6 left-side h-100 bg-panel justify-content-center align-content-center">
                <!-- Logo -->
                <div class="col-md-8 col-12 justify-content-center align-content-center">
                    <img class="img-fluid" src="img/logo.png" alt="Logo" />
                </div>
                <!-- Párrafo y contenedor de mensaje -->
                <p id="left-paragraph">
                    <i class="letrah1">FastRabbit</i>
                </p>
                <div id="left-message" class="alert text-center d-none" role="alert"></div>
                <!-- login que aparece solo cuando la pantalla es un móvil -->
                <div class="custom-card mt-3 d-block d-md-none">
                    <h1 class="bg-h1">Login</h1>
                    <div class="tab-pane fade show active" id="login-movil" role="tabpanel">
                        <form action="backEnd/mngSession.php" method="post">
                            <input type="hidden" name="op" value="ss">

                            <div class="mb-3">
                                <label for="loginMail" class="form-label">Correo Electrónico</label>
                                <input type="email" class="form-control" id="loginMail-movil" name="mail" required>
                            </div>

                            <div class="mb-3">
                                <label for="loginPassword" class="form-label">Contraseña</label>
                                <input type="password" class="form-control" id="loginPassword-movil" name="pass" required
                                    autocomplete="off">
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                Iniciar Sesión
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Panel de login que solo aparece en pantallas para tablets o superior -->
            <div class="col-md-6 d-none d-md-block right-side justify-content-center align-content-center">
                <div class="custom-card mx-auto">
                    <h1 class="bg-h1">Login</h1>

                    <div class="tab-content mt-3" id="myTabContent">
                        <!-- Formulario de Login -->
                        <div class="tab-pane fade show active" id="login" role="tabpanel">
                            <form action="backEnd/mngSession.php" method="post">
                                <input type="hidden" name="op" value="ss">

                                <div class="mb-3">
                                    <label for="loginMail" class="form-label">Correo Electrónico</label>
                                    <input type="email" class="form-control" id="loginMail" name="mail" required>
                                </div>

                                <div class="mb-3">
                                    <label for="loginPassword" class="form-label">Contraseña</label>
                                    <input type="password" class="form-control" id="loginPassword" name="pass" required
                                        autocomplete="off">
                                </div>

                                <button type="submit" class="btn btn-primary w-100">
                                    Iniciar Sesión
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>