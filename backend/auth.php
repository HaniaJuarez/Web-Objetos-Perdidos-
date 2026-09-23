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

    $nombre = $_POST['nombre'] ?? '';
    $correo = $_POST['correo'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($nombre) || empty($correo) || empty($password)) {
        echo "Todos los campos son obligatorios.";
        exit;
    }

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

    echo "Usuario registrado correctamente.";
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

    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['nombre'] = $usuario['nombre'];   
    $_SESSION['correo'] = $usuario['correo'];
    $_SESSION['rol'] = $usuario['rol'];
    $_SESSION['foto'] = $usuario['foto'] ?? '';

    //echo "Inicio de sesión correcto.<br>";
    //echo "Bienvenido, " . htmlspecialchars($usuario['nombre']) . "<br>";
    //echo "Rol: " . htmlspecialchars($usuario['rol']);

    header("Location: ../frontend/index.php");

    exit;
}

echo "Acción no reconocida.";

?>