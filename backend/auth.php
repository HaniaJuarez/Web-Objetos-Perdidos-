<?php

session_start();

require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "Solicitud no válida.";
    exit;
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
        echo "Todos los campos son obligatorios.";
        exit;
    }

    // Validar el nombre.
    if (mb_strlen($nombre) > 50) {
        echo "El nombre no debe superar los 50 caracteres.";
        exit;
    }

    // Validar que el correo tenga un formato válido.
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        echo "Ingresa un correo electrónico válido.";
        exit;
    }

    // Validar el nombre.
    if (mb_strlen($nombre) > 100) {
        echo "El nombre no debe superar los 100 caracteres.";
        exit;
    }

    // Validar que el correo tenga un formato válido.
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        echo "Ingresa un correo electrónico válido.";
        exit;
    }

    // Permitir únicamente correos institucionales de alumnos.
    // Formato: zs + ocho dígitos + @estudiantes.uv.mx
    $patronAlumno = '/^zs\d{8}@estudiantes\.uv\.mx$/i';

    if (!preg_match($patronAlumno, $correo)) {
        echo "No se pudo crear la cuenta. Debes utilizar tu correo "
           . "institucional con el formato zs12345678@estudiantes.uv.mx.";
        exit;
    }

    // Validar la longitud mínima de la contraseña.
    if (strlen($password) < 8) {
        echo "La contraseña debe tener al menos 8 caracteres.";
        exit;
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
            'content' => json_encode($datos)            
        ]
    ];

    $context = stream_context_create($options);

    $response = file_get_contents($url, false, $context);

    if ($response === false) {
        echo "Error al registrar el usuario.";
        exit;
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
        $codigoError = $errorSupabase['code'] ?? '';

        if ($codigoError === '23505') {
            echo "Ya existe una cuenta registrada con ese correo institucional.";
            exit;
        }

        echo "No se pudo registrar la cuenta. Verifica los datos "
           . "o inténtalo nuevamente.";
        exit;
    }


    // Registro realizado correctamente.
    header("Location: ../frontend/login.html");
    exit;
}


/* =========================
   LOGIN
   ========================= */

if ($accion === 'login') {

    $correo = $_POST['correo'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($correo) || empty($password)) {
        echo "Correo y contraseña son obligatorios.";
        exit;
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

    $response = file_get_contents($url, false, $context);

    if ($response === false) {
        echo "Error al consultar el usuario.";
        exit;
    }

    $usuarios = json_decode($response, true);

    if (empty($usuarios)) {
        echo "Correo o contraseña incorrectos.";
        exit;
    }

    $usuario = $usuarios[0];

    if (!password_verify($password, $usuario['password'])) {
        echo "Correo o contraseña incorrectos.";
        exit;
    }

    // Validar el correo de los alumnos.
// Los docentes y administradores no tienen esta restricción.
$rolUsuario = strtolower(trim($usuario['rol'] ?? ''));
$correoUsuario = strtolower(trim($usuario['correo'] ?? ''));

if ($rolUsuario === 'alumno') {

    // Formato permitido: zs + 8 dígitos + dominio institucional.
    $patronAlumno = '/^zs\d{8}@estudiantes\.uv\.mx$/i';

    if (!preg_match($patronAlumno, $correoUsuario)) {
        echo "Acceso denegado: los alumnos deben utilizar su correo "
           . "institucional con el formato zs12345678@estudiantes.uv.mx.";
        exit;
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
            header("Location: ../frontend/administrador.php");
            exit;

        case 'docente':
            header("Location: ../frontend/docente.php");
            exit;

        case 'alumno':
            header("Location: ../frontend/index.php");
            exit;

        default:
            // Si el rol no es reconocido, cerrar la sesión y mostrar un error.
            $_SESSION = [];
            session_destroy();

            header("Location: ../frontend/login.html");
            exit;
    }
}

echo "Acción no reconocida.";

?>