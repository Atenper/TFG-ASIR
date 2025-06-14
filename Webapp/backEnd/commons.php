<?php
function sanitize($valor, $tipo = 'string') {
    $result = null;
    switch ($tipo) {
        case 'mail':
            $result = filter_var(trim($valor), FILTER_SANITIZE_EMAIL);
            break;
        case 'url':
            $result = filter_var(trim($valor), FILTER_SANITIZE_URL);
            break;
        case 'int':
            $result = filter_var($valor, FILTER_SANITIZE_NUMBER_INT);
            break;
        case 'float':
            $result = filter_var($valor, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_THOUSAND);
            break;
        case 'pass':
            $result = trim($valor); // No alteramos contraseñas para evitar modificaciones
            break;
        case 'string': // Caso 'string' y por defecto
        default:
            $result = filter_var(trim($valor), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    }
    return $result;
}

function gotoURL($url, $msg) {
	header("Location: $url?msg=$msg");
	exit();		
}

function getLink() {
	$servername = "localhost";
	$username = "webapp";
	$password = "webapp";
	$database = "webappdb";

	// Create connection
	try {				
		$link = new mysqli($servername, $username, $password, $database);
		
	} catch (Exception $e) {
		sessionClose();
		//gotoURL("../index.php", $e->getMessage());
		gotoURL("../index.php", "db_error");
	}
	return $link;	
}

function doQuery($query) {
    if (empty($query)) {
        return false; // Retornar false si la consulta está vacía
    }

    try {
        $conexion = getLink(); // Obtener la conexión desde la función getLink()
        
        $query = trim($query);
        $tipo = strtoupper(substr($query, 0, 2));
        $transactionTypes = ["SE","IN", "UP", "DE", "RE"];
        $isTransaction = in_array($tipo, $transactionTypes);

        if ($isTransaction) {
            mysqli_begin_transaction($conexion);
        }

        $resultado = mysqli_query($conexion, $query);
        if (!$resultado) {
            throw new Exception(mysqli_error($conexion)); // Capturar error sin detener el script
        }

        $response = false;
        switch ($tipo) {
            case "SE": // SELECT
                $data = [];
                while ($row = mysqli_fetch_assoc($resultado)) {
                    $data[] = $row;
                }
                mysqli_free_result($resultado);
                $response = !empty($data) ? $data : false; // false si no hay resultados
                break;

            case "IN": // INSERT
                $response = mysqli_insert_id($conexion) ?: mysqli_affected_rows($conexion);
                break;

            case "UP": // UPDATE
            case "DE": // DELETE
            case "RE": // REPLACE
                $response = mysqli_affected_rows($conexion);
                break;

            default:
                throw new Exception("Tipo de consulta no reconocido");
        }

        if ($isTransaction) {
            mysqli_commit($conexion);
        }

    } catch (Exception $e) {
        if (isset($conexion) && $isTransaction) {
            mysqli_rollback($conexion);
        }
        trigger_error("Error en la consulta: " . $e->getMessage(), E_USER_WARNING);
        $response = ($tipo === "SE") ? false : 0;
    } finally {
        if (isset($conexion)) {
            mysqli_close($conexion);
        }
    }

    return $response;
}
?>