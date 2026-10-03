<?php

/**
 * Script de Procesamiento para la Gestión de Reclamaciones (Docentes)
 * 
 * Este archivo procesa la actualización del estado de una reclamación enviada
 * desde el panel de docente. Permite aprobar o rechazar un ticket de objeto/reclamación
 * interactuando directamente con la API REST de Supabase via PATCH.
 */

// ============================================================
// VERIFICAR SESIÓN
// ============================================================

// Comprueba que exista una sesión activa y válida en el sistema.
require_once 'verificar_sesion.php';

// Carga las variables de entorno global y credenciales (SUPABASE_URL, SUPABASE_KEY).
require_once 'config.php';


// ============================================================
// VERIFICAR ROL
// ============================================================

// Control de acceso Basado en Roles (RBAC):
// Solamente un docente puede aprobar o rechazar una reclamación.
if (
    !isset($_SESSION['rol']) ||
    $_SESSION['rol'] !== 'docente'
) {

    echo "Acceso denegado.";

    exit;
}


// ============================================================
// VERIFICAR MÉTODO
// ============================================================

// Garantiza que el script solo procese solicitudes mediante el protocolo HTTP POST.
if (
    $_SERVER['REQUEST_METHOD'] !== 'POST'
) {

    echo "Solicitud no válida.";

    exit;
}


// ============================================================
// OBTENER DATOS
// ============================================================

// Obtiene el ID único de la reclamación desde los parámetros de la petición POST.
$reclamacion_id =
    $_POST['reclamacion_id'] ?? '';


// Obtiene la acción solicitada por la interfaz ('aprobar' o 'rechazar').
$accion =
    $_POST['accion'] ?? '';


// Obtiene el ID único del docente almacenado en la sesión activa.
$docente_id =
    $_SESSION['usuario_id'];


// ============================================================
// VALIDAR DATOS
// ============================================================

// Comprueba que los parámetros mínimos requeridos no se encuentren vacíos.
if (
    empty($reclamacion_id) ||
    empty($accion)
) {

    echo "Datos incompletos.";

    exit;
}


// ============================================================
// DETERMINAR EL NUEVO ESTADO
// ============================================================

// Mapea la acción solicitada por el usuario al valor equivalente del estado en la base de datos.
if ($accion === 'aprobar') {

    $nuevo_estado =
        'Aprobada';

} elseif ($accion === 'rechazar') {

    $nuevo_estado =
        'Rechazada';

} else {

    echo "Acción no válida.";

    exit;
}


// ============================================================
// DATOS PARA ACTUALIZAR
// ============================================================

// Arreglo asociativo con la estructura exacta que la API de Supabase espera recibir en JSON.
$datos = [

    // Nuevo estado de la reclamación.
    'estado' =>
        $nuevo_estado,

    // Guarda qué docente realizó la acción de aprobación/rechazo.
    'docente_id' =>
        (int)$docente_id

];


// ============================================================
// URL DE SUPABASE
// ============================================================

// Construcción del endpoint REST de Supabase con filtrado PostgREST por ID (?id=eq.ID).
$url =

    SUPABASE_URL .

    '/rest/v1/reclamaciones' .

    '?id=eq.' .

    urlencode($reclamacion_id);


// ============================================================
// CONFIGURACIÓN DE LA PETICIÓN
// ============================================================

// Opciones del contexto HTTP para la llamada REST mediante stream_context.
$options = [

    'http' => [

        // PATCH modifica solamente los campos indicados sin sobrescribir el registro completo.
        'method' =>
            'PATCH',

        // Encabezados de autenticación y formato exigidos por la API REST de Supabase.
        'header' =>

            "apikey: " .
            SUPABASE_KEY .
            "\r\n" .

            "Authorization: Bearer " .
            SUPABASE_KEY .
            "\r\n" .

            "Content-Type: application/json\r\n" .

            "Prefer: return=representation\r\n",

        // Convierte los datos a JSON para ser enviados en el cuerpo de la petición.
        'content' =>
            json_encode($datos),

        // Permite obtener la respuesta del servidor incluso si responde con un estado de error HTTP (4xx o 5xx).
        'ignore_errors' =>
            true
    ]
];


// ============================================================
// EJECUTAR PETICIÓN
// ============================================================

// Crea el recurso de contexto HTTP con las opciones especificadas.
$context =
    stream_context_create($options);


// Envía la actualización a Supabase ejecutando la llamada HTTP.
$response =
    file_get_contents(
        $url,
        false,
        $context
    );


// ============================================================
// COMPROBAR RESPUESTA
// ============================================================

// Verifica si la solicitud HTTP falló por completo a nivel de transporte/red.
if ($response === false) {

    echo "Error al actualizar la reclamación.";

    exit;
}


// Convierte la respuesta JSON devuelta por la API en un arreglo asociativo de PHP.
$resultado =
    json_decode(
        $response,
        true
    );


// Comprueba si Supabase devolvió un objeto de error (propiedad 'message').
if (
    isset($resultado['message'])
) {

    echo "Error al actualizar la reclamación.";

    echo "<br>";

    echo htmlspecialchars(
        $resultado['message']
    );

    exit;
}


// ============================================================
// REGRESAR AL PANEL DE RECLAMACIONES
// ============================================================

// Redirige la navegación del navegador de vuelta al panel principal de reclamaciones del docente.
header(
    "Location: ../frontend/reclamaciones_docente.php"
);

exit;

?>