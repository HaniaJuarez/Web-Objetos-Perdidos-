<?php
// Inicia la sesión para poder destruirla correctamente.
session_start();
// Vacía todas las variables almacenadas en la sesión.
$_SESSION = [];
session_unset();
// Destruye la sesión del usuario.
session_destroy();

// Redirige al formulario de inicio de sesión.
header('Location: ../frontend/login.html');
exit;

?>