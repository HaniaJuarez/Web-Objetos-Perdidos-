<?php

session_start();

require_once 'config.php';

// Enviar mensajes que JavaScript pueda mostrar en el formulario.
function responder($mensaje, $codigoHTTP = 200) {
    http_response_code($codigoHTTP);
    header('Content-Type: text/plain; charset=UTF-8');
    echo $mensaje;
    exit;
}

function responderLogin($mensaje, $exito = false, $destino = '') {
    header('Content-Type: application/json; charset=UTF-8');

    echo json_encode([
        'exito' => $exito,
        'mensaje' => $mensaje,
        'destino' => $destino
    ]);

    exit;
}

// Solo aceptar solicitudes POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder('Solicitud no válida.', 405);
}

$accion = $_POST['accion'] ?? '';

/* =========================
   REGISTRO
   ========================= */

if ($accion === 'registro') {

    //$nombre = $_POST['nombre'] ?? '';
    //$correo = $_POST['correo'] ?? '';
    // Obtener y limpiar los datos del formulario.
    $nombre = trim($_POST['nombre'] ?? '');
    $correo = strtolower(trim($_POST['correo'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (empty($nombre) || empty($correo) || empty($password)) {
        responder('Todos los campos son obligatorios.', 400);
    }

    // Validar el nombre.
    if (mb_strlen($nombre) > 50) {
        responder('El nombre no debe superar los 50 caracteres.', 400);
    }

    // Validar que el correo tenga un formato válido.
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        responder('Ingresa un correo electrónico válido.', 400);
    }

    // Permitir únicamente correos institucionales de alumnos.
    // Formato: zs + ocho dígitos + @estudiantes.uv.mx
    $patronAlumno = '/^zs\d{8}@estudiantes\.uv\.mx$/i';

    if (!preg_match($patronAlumno, $correo)) {
        responder('Debes utilizar tu correo institucional con el formato zS12345678@estudiantes.uv.mx.', 400);
        
    }

    // Validar la longitud mínima de la contraseña.
    if (strlen($password) < 8) {
        responder('La contraseña debe tener al menos 8 caracteres.', 400);
    }

    // Encriptar la contraseña antes de guardarla.
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $url = SUPABASE_URL . '/rest/v1/usuarios';

    $datos = [
        'nombre' => $nombre,
        'correo' => $correo,
        'password' => $passwordHash,
        'rol' => 'alumno'
    ];

    $options = [
        'http' => [
            'method' => 'POST',
            'header' =>
                "apikey: " . SUPABASE_KEY . "\r\n" .
                "Authorization: Bearer " . SUPABASE_KEY . "\r\n" .
                "Content-Type: application/json\r\n" .
                "Prefer: return=representation\r\n",
            'content' => json_encode($datos),
            'ignore_errors' => true,
            'timeout' => 20           
        ]
    ];

    $context = stream_context_create($options);

    // Evitar que PHP muestre una advertencia técnica en pantalla.
    $response = @file_get_contents($url, false, $context);

    // Si no se recibió respuesta, ocurrió un problema de conexión.
    if ($response === false) {
        //responder('No fue posible conectar con el servicio. Inténtalo más tarde.', 500);
        $errorPHP = error_get_last();

        error_log(
            'Error al registrar en Supabase: ' .
            ($errorPHP['message'] ?? 'Error de conexión desconocido')
        
        );

        responder(
            'No fue posible conectar con el servicio. Revisa el registro de errores del servidor.',
            500
        );
    }

    // Obtener el código HTTP devuelto por Supabase.
    $codigoHTTP = 0;

    if (isset($http_response_header)) {
        foreach ($http_response_header as $encabezado) {
            if (preg_match('/^HTTP\/\S+\s+(\d{3})/', $encabezado, $coincidencias)) {
                $codigoHTTP = (int) $coincidencias[1];
                break;
            }
        }
    }

    // Detectar errores, por ejemplo, si el correo ya existe
    // y la base de datos tiene una restricción UNIQUE.
    if ($codigoHTTP < 200 || $codigoHTTP >= 300) {

        $errorSupabase = json_decode($response, true);

        // Registrar el detalle técnico para revisar el problema.
        error_log(
            'Error de Supabase al registrar. HTTP ' . $codigoHTTP .
            ' - Respuesta: ' . $response
        );

        $codigoError = $errorSupabase['code'] ?? '';

        if ($codigoError === '23505') {
            responder('Ya existe una cuenta registrada con ese correo institucional.', 409);
        }

        if ($codigoError === '42501') {
            responder(
                'Supabase rechazó la operación por permisos de acceso a la tabla.',403);
        }

        responder(
            'Supabase rechazó el registro (HTTP ' . $codigoHTTP .'). Revisa la terminal para conocer el detalle.',400);
    }


    // Registro realizado correctamente.
    responder(
    'Cuenta creada correctamente. Ya puedes iniciar sesión.',201);
}


/* =========================
   LOGIN
   ========================= */

if ($accion === 'login') {

    $correo = $_POST['correo'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($correo) || empty($password)) {
        responderLogin('Correo y contraseña son obligatorios.', 400);
    }

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        responderLogin('Ingresa un correo electrónico válido.', 400);
    }

    $url = SUPABASE_URL . '/rest/v1/usuarios?correo=eq.'
         . urlencode($correo)
         . '&select=id,nombre,correo,password,rol,foto';

    $options = [
        'http' => [
            'method' => 'GET',
            'header' =>
                "apikey: " . SUPABASE_KEY . "\r\n" .
                "Authorization: Bearer " . SUPABASE_KEY . "\r\n" .
                "Content-Type: application/json\r\n"
        ]
    ];

    $context = stream_context_create($options);

    $response = @file_get_contents($url, false, $context);

    if ($response === false) {
        responder('Error al consultar el usuario.', 500);
    }

    $codigoHTTP = 0;

    if (isset($http_response_header)) {
        foreach ($http_response_header as $encabezado) {
            if (preg_match(
                '/^HTTP\/\S+\s+(\d{3})/',
                $encabezado,
                $coincidencias
            )) {
                $codigoHTTP = (int) $coincidencias[1];
                break;
            }
        }
    }

    if ($codigoHTTP < 200 || $codigoHTTP >= 300) {
        responder('No fue posible consultar las cuentas. Inténtalo más tarde.', 500);
    }

    $usuarios = json_decode($response, true);

    if (!is_array($usuarios) || empty($usuarios)) {
        responder('Correo o contraseña incorrectos.', 401);
    }

    $usuario = $usuarios[0];

    if (!password_verify($password, $usuario['password'])) {
        responder('Correo o contraseña incorrectos.', 401);
    }

    // Validar el correo de los alumnos.
    // Los docentes y administradores no tienen esta restricción.
    $rolUsuario = strtolower(trim($usuario['rol'] ?? ''));
    $correoUsuario = strtolower(trim($usuario['correo'] ?? ''));

    if ($rolUsuario === 'alumno') {

        // Formato permitido: zs + 8 dígitos + dominio institucional.
        $patronAlumno = '/^zs\d{8}@estudiantes\.uv\.mx$/i';

        if (!preg_match($patronAlumno, $correoUsuario)) {
            responder(
                'Los alumnos deben utilizar su correo institucional de la UV.',
                403);
        }
    }

    // Guardar los datos del usuario autenticado en la sesión.
    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['nombre'] = $usuario['nombre'];   
    $_SESSION['correo'] = $usuario['correo'];
    //$_SESSION['rol'] = $usuario['rol'];
    $_SESSION['rol'] = strtolower(trim($usuario['rol'] ?? ''));
    $_SESSION['foto'] = $usuario['foto'] ?? '';

    //echo "Inicio de sesión correcto.<br>";
    //echo "Bienvenido, " . htmlspecialchars($usuario['nombre']) . "<br>";
    //echo "Rol: " . htmlspecialchars($usuario['rol']);

    //header("Location: ../frontend/index.php");
    //exit;

    // Regenerar el identificador de sesión por seguridad.
    session_regenerate_id(true);

    // Redirigir al usuario según el rol registrado en Supabase.
    switch ($_SESSION['rol']) {
        case 'administrador':
            responderLogin(
                'Inicio de sesión correcto.',
                true,
                '../frontend/administrador.php'
            );

        case 'docente':
            responderLogin(
                'Inicio de sesión correcto.',
                true,
                '../frontend/docente.php'
            );

        case 'alumno':
            responderLogin(
                'Inicio de sesión correcto.',
                true,
                '../frontend/index.php'
            );

        default:
            // Si el rol no es reconocido, cerrar la sesión y mostrar un error.
            $_SESSION = [];
            session_destroy();

            responderLogin(
                'El rol de la cuenta no es válido.',
                false
            );
    }
}

responder('Acción no reconocida.', 400);

?>