<?php

//session_start();
require_once 'verificar_sesion.php';

if (!isset($_SESSION['usuario_id'])) {
    echo "No hay una sesión iniciada.";
    exit;
}

echo "<h1>Sesión activa</h1>";

echo "ID: " . htmlspecialchars($_SESSION['usuario_id']) . "<br>";
echo "Nombre: " . htmlspecialchars($_SESSION['nombre']) . "<br>";
echo "Correo: " . htmlspecialchars($_SESSION['correo']) . "<br>";
echo "Rol: " . htmlspecialchars($_SESSION['rol']) . "<br>";

?>