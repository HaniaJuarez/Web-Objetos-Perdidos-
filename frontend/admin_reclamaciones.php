<?php

// ============================================================
// VERIFICACIÓN DE SESIÓN Y CONFIGURACIÓN INICIAL
// ============================================================

// Incluye el script encargado de validar que la sesión esté iniciada y activa.
require_once '../backend/verificar_sesion.php';

// Carga las variables y constantes globales de configuración (URL y API Key de Supabase).
require_once '../backend/config.php';

/*
|--------------------------------------------------------------------------
| Verificar que el usuario sea administrador
|--------------------------------------------------------------------------
*/

// Valida que el rol almacenado en la sesión corresponda a 'administrador'.
// Si no existe o no coincide, restringe el acceso e interrumpe la ejecución del script.
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
    echo "Acceso no autorizado.";
    exit;
}

/*
|--------------------------------------------------------------------------
| Consultar reclamaciones en Supabase
|--------------------------------------------------------------------------
*/

// Construye la URL para solicitar la lista completa de reclamaciones ordenadas de forma descendente por su ID.
$url_reclamaciones =
    SUPABASE_URL .
    '/rest/v1/reclamaciones' .
    '?select=id,objeto_id,objeto_perdido_id,usuario_id,descripcion,estado,ticket,fecha,docente_id' .
    '&order=id.desc';

// Configura las cabeceras HTTP necesarias para autenticar la petición en Supabase.
$options_reclamaciones = [
    'http' => [
        // Método HTTP a utilizar (GET).
        'method' => 'GET',
        // Encabezados con las credenciales apikey, Bearer Token y tipo de contenido JSON.
        'header' =>
            "apikey: " . SUPABASE_KEY . "\r\n" .
            "Authorization: Bearer " . SUPABASE_KEY . "\r\n" .
            "Content-Type: application/json\r\n",
        // Evita interrumpir la ejecución si el servidor devuelve un código de estado de error HTTP.
        'ignore_errors' => true
    ]
];

// Crea el flujo de contexto HTTP con las opciones definidas.
$context_reclamaciones =
    stream_context_create($options_reclamaciones);

// Realiza la petición GET a la API REST de Supabase.
$response_reclamaciones =
    file_get_contents(
        $url_reclamaciones,
        false,
        $context_reclamaciones
    );

// Verifica si ocurrió un fallo al realizar la conexión o lectura de datos.
if ($response_reclamaciones === false) {
    echo "No se pudieron consultar las reclamaciones.";
    exit;
}

// Convierte la respuesta en formato JSON a un arreglo asociativo de PHP.
$reclamaciones =
    json_decode($response_reclamaciones, true);

// Garantiza que la variable contenga un arreglo válido en caso de recibir un JSON nulo o malformado.
if (!is_array($reclamaciones)) {
    $reclamaciones = [];
}

/*
|--------------------------------------------------------------------------
| Función para realizar consultas GET a Supabase
|--------------------------------------------------------------------------
*/

/**
 * Realiza peticiones auxiliares a la API REST de Supabase mediante HTTP GET.
 *
 * @param string $url URL del endpoint a consultar.
 * @return array Arreglo asociativo con los datos obtenidos o un arreglo vacío en caso de error.
 */
function consultarSupabase($url)
{
    // Opciones de configuración para la petición auxiliar.
    $options = [
        'http' => [
            'method' => 'GET',
            'header' =>
                "apikey: " . SUPABASE_KEY . "\r\n" .
                "Authorization: Bearer " . SUPABASE_KEY . "\r\n" .
                "Content-Type: application/json\r\n",
            'ignore_errors' => true
        ]
    ];

    // Crea el flujo de contexto HTTP.
    $context = stream_context_create($options);

    // Ejecuta la petición GET a la API REST.
    $response =
        file_get_contents(
            $url,
            false,
            $context
        );

    // Retorna un arreglo vacío si falla la petición HTTP.
    if ($response === false) {
        return [];
    }

    // Decodifica la respuesta JSON.
    $datos = json_decode($response, true);

    // Retorna los datos si la decodificación produjo un arreglo válido, o un arreglo vacío.
    return is_array($datos) ? $datos : [];
}

/*
|--------------------------------------------------------------------------
| Contadores y estadísticas de estados
|--------------------------------------------------------------------------
*/

// Almacena el número total de reclamaciones obtenidas.
$total_reclamaciones = count($reclamaciones);

// Inicializa las variables para contabilizar los diferentes estados de las reclamaciones.
$pendientes = 0;
$aprobadas = 0;
$rechazadas = 0;
$cerradas = 0;

// Recorre las reclamaciones para acumular el conteo correspondiente según cada estado.
foreach ($reclamaciones as $reclamacion) {

    $estado = $reclamacion['estado'] ?? '';

    if ($estado === 'Pendiente') {
        $pendientes++;
    }

    if ($estado === 'Aprobada') {
        $aprobadas++;
    }

    if ($estado === 'Rechazada') {
        $rechazadas++;
    }

    if ($estado === 'Cerrada') {
        $cerradas++;
    }
}

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <!-- Define la codificación de caracteres en UTF-8 -->
    <meta charset="UTF-8">

    <!-- Configuración para asegurar que la vista sea totalmente responsiva -->
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Reclamaciones - Administrador</title>

    <!-- Hoja de estilos CSS del módulo de administración de reclamaciones -->
    <link
        rel="stylesheet"
        href="css/admin_reclamaciones.css"
    >

    <!-- Script JavaScript para filtrado y búsqueda dinámicos en el cliente -->
    <script
        src="js/admin_reclamaciones.js"
        defer
    ></script>

</head>

<body>

<!-- ENCABEZADO PRINCIPAL DE LA PÁGINA -->
<header class="encabezado">

    <div class="encabezado-contenido">

        <div>

            <h1>
                Reclamaciones
            </h1>

            <p>
                Panel de administración
            </p>

        </div>

        <!-- Enlace para regresar al panel de control principal -->
        <a
            href="administrador.php"
            class="boton-regresar"
        >
            ← Volver al panel
        </a>

    </div>

</header>


<!-- CONTENEDOR PRINCIPAL DEL CONTENIDO -->
<main class="contenedor">

    <!-- =========================================================
         TARJETAS DE RESUMEN Y ESTADÍSTICAS DE ESTADOS
         ========================================================= -->

    <section class="resumen">

        <!-- Muestra el total global de reclamaciones -->
        <div class="tarjeta-resumen">

            <span class="numero">
                <?php echo $total_reclamaciones; ?>
            </span>

            <span class="texto">
                Total
            </span>

        </div>


        <!-- Muestra el número de reclamaciones pendientes -->
        <div class="tarjeta-resumen pendiente">

            <span class="numero">
                <?php echo $pendientes; ?>
            </span>

            <span class="texto">
                Pendientes
            </span>

        </div>


        <!-- Muestra el número de reclamaciones aprobadas -->
        <div class="tarjeta-resumen aprobada">

            <span class="numero">
                <?php echo $aprobadas; ?>
            </span>

            <span class="texto">
                Aprobadas
            </span>

        </div>


        <!-- Muestra el número de reclamaciones rechazadas -->
        <div class="tarjeta-resumen rechazada">

            <span class="numero">
                <?php echo $rechazadas; ?>
            </span>

            <span class="texto">
                Rechazadas
            </span>

        </div>


        <!-- Muestra el número de reclamaciones cerradas -->
        <div class="tarjeta-resumen cerrada">

            <span class="numero">
                <?php echo $cerradas; ?>
            </span>

            <span class="texto">
                Cerradas
            </span>

        </div>

    </section>


    <!-- =========================================================
         BARRA DE FILTROS Y BÚSQUEDA DENTRO DE LA LISTA
         ========================================================= -->

    <section class="filtros">

        <!-- Campo de entrada de texto libre para buscar coincidencias por ticket, usuario u objeto -->
        <div class="campo">

            <label for="buscar">
                Buscar
            </label>

            <input
                type="text"
                id="buscar"
                placeholder="Ticket, usuario u objeto..."
            >

        </div>


        <!-- Selector para filtrar las tarjetas según su estado -->
        <div class="campo">

            <label for="filtro-estado">
                Estado
            </label>

            <select id="filtro-estado">

                <option value="">
                    Todos
                </option>

                <option value="Pendiente">
                    Pendiente
                </option>

                <option value="Aprobada">
                    Aprobada
                </option>

                <option value="Rechazada">
                    Rechazada
                </option>

                <option value="Cerrada">
                    Cerrada
                </option>

            </select>

        </div>


        <!-- Botón para reiniciar todos los filtros aplicados en la vista -->
        <button
            type="button"
            id="limpiar-filtros"
            class="boton-limpiar"
        >
            Limpiar filtros
        </button>

    </section>


    <!-- =========================================================
         LISTA DE TARJETAS DE RECLAMACIONES
         ========================================================= -->

    <section class="seccion-reclamaciones">

        <div class="titulo-seccion">

            <h2>
                Todas las reclamaciones
            </h2>

            <!-- Indicador dinámico actualizado por JS o servidor con la cantidad mostrada -->
            <span id="contador-resultados">
                <?php echo $total_reclamaciones; ?> reclamaciones
            </span>

        </div>


        <div class="lista-reclamaciones">

            <!-- Muestra un aviso vacío si no existen registros de reclamaciones -->
            <?php if (empty($reclamaciones)): ?>

                <div class="mensaje-vacio">

                    <h3>
                        No existen reclamaciones
                    </h3>

                    <p>
                        Todavía no se ha registrado ninguna reclamación.
                    </p>

                </div>

            <?php else: ?>

                <!-- Bucle para iterar e imprimir cada solicitud de reclamación -->
                <?php foreach ($reclamaciones as $reclamacion): ?>

                    <?php

                    /*
                    |------------------------------------------------------
                    | Extracción de datos de la reclamación actual
                    |------------------------------------------------------
                    */

                    $id =
                        $reclamacion['id'] ?? '';

                    $ticket =
                        $reclamacion['ticket'] ?? 'Sin ticket';

                    $usuario_id =
                        $reclamacion['usuario_id'] ?? '';

                    $objeto_id =
                        $reclamacion['objeto_id'] ?? '';

                    $objeto_perdido_id =
                        $reclamacion['objeto_perdido_id'] ?? '';

                    $descripcion =
                        $reclamacion['descripcion'] ?? '';

                    $estado =
                        $reclamacion['estado'] ?? 'Sin estado';

                    $fecha =
                        $reclamacion['fecha'] ?? '';

                    $docente_id =
                        $reclamacion['docente_id'] ?? '';


                    /*
                    |------------------------------------------------------
                    | Consultar datos del usuario solicitante
                    |------------------------------------------------------
                    */

                    $usuario = [];

                    if (!empty($usuario_id)) {

                        $url_usuario =
                            SUPABASE_URL .
                            '/rest/v1/usuarios' .
                            '?id=eq.' .
                            urlencode($usuario_id) .
                            '&select=id,nombre,correo';

                        $usuarios =
                            consultarSupabase($url_usuario);

                        if (!empty($usuarios)) {
                            $usuario = $usuarios[0];
                        }
                    }


                    /*
                    |------------------------------------------------------
                    | Consultar datos del objeto encontrado en resguardo
                    |------------------------------------------------------
                    */

                    $objeto = [];

                    if (!empty($objeto_id)) {

                        $url_objeto =
                            SUPABASE_URL .
                            '/rest/v1/objetos' .
                            '?id=eq.' .
                            urlencode($objeto_id) .
                            '&select=id,nombre,estado,imagen';

                        $objetos =
                            consultarSupabase($url_objeto);

                        if (!empty($objetos)) {
                            $objeto = $objetos[0];
                        }
                    }


                    /*
                    |------------------------------------------------------
                    | Consultar datos del objeto reportado como perdido
                    |------------------------------------------------------
                    */

                    $objeto_perdido = [];

                    if (!empty($objeto_perdido_id)) {

                        $url_objeto_perdido =
                            SUPABASE_URL .
                            '/rest/v1/objetos' .
                            '?id=eq.' .
                            urlencode($objeto_perdido_id) .
                            '&select=id,nombre,estado,imagen';

                        $objetos_perdidos =
                            consultarSupabase(
                                $url_objeto_perdido
                            );

                        if (!empty($objetos_perdidos)) {

                            $objeto_perdido =
                                $objetos_perdidos[0];
                        }
                    }

                    ?>


                    <!-- Tarjeta contenedora de la reclamación con data-attributes para filtrado del cliente -->
                    <article
                        class="tarjeta-reclamacion"
                        data-estado="<?php echo htmlspecialchars($estado); ?>"
                        data-busqueda="<?php
                            echo htmlspecialchars(
                                strtolower(
                                    $ticket .
                                    ' ' .
                                    ($usuario['nombre'] ?? '') .
                                    ' ' .
                                    ($usuario['correo'] ?? '') .
                                    ' ' .
                                    ($objeto['nombre'] ?? '') .
                                    ' ' .
                                    ($objeto_perdido['nombre'] ?? '')
                                )
                            );
                        ?>"
                    >

                        <!-- ENCABEZADO DE LA TARJETA -->

                        <div class="cabecera-reclamacion">

                            <div>

                                <span class="etiqueta-ticket">
                                    <?php echo htmlspecialchars($ticket); ?>
                                </span>

                                <h3>
                                    Reclamación #<?php echo htmlspecialchars($id); ?>
                                </h3>

                            </div>


                            <!-- Muestra la badge de estado del ticket de reclamación -->
                            <span
                                class="estado estado-<?php
                                    echo strtolower(
                                        $estado
                                    );
                                ?>"
                            >
                                <?php echo htmlspecialchars($estado); ?>
                            </span>

                        </div>


                        <!-- INFORMACIÓN DE DETALLE -->

                        <div class="informacion">

                            <!-- Bloque de información del alumno/usuario -->
                            <div class="bloque">

                                <h4>
                                    Usuario
                                </h4>

                                <?php if (!empty($usuario)): ?>

                                    <p>
                                        <strong>
                                            Nombre:
                                        </strong>

                                        <?php
                                        echo htmlspecialchars(
                                            $usuario['nombre'] ?? 'Sin nombre'
                                        );
                                        ?>
                                    </p>

                                    <p>
                                        <strong>
                                            Correo:
                                        </strong>

                                        <?php
                                        echo htmlspecialchars(
                                            $usuario['correo'] ?? 'Sin correo'
                                        );
                                        ?>
                                    </p>

                                    <p>
                                        <strong>
                                            ID:
                                        </strong>

                                        <?php
                                        echo htmlspecialchars(
                                            $usuario_id
                                        );
                                        ?>
                                    </p>

                                <?php else: ?>

                                    <p>
                                        Usuario no disponible.
                                    </p>

                                <?php endif; ?>

                            </div>


                            <!-- Bloque de información del objeto resguardado/encontrado -->
                            <div class="bloque">

                                <h4>
                                    Objeto encontrado
                                </h4>

                                <?php if (!empty($objeto)): ?>

                                    <p>
                                        <strong>
                                            Nombre:
                                        </strong>

                                        <?php
                                        echo htmlspecialchars(
                                            $objeto['nombre'] ?? 'Sin nombre'
                                        );
                                        ?>
                                    </p>

                                    <p>
                                        <strong>
                                            ID:
                                        </strong>

                                        <?php
                                        echo htmlspecialchars(
                                            $objeto_id
                                        );
                                        ?>
                                    </p>

                                    <a
                                        href="objeto.php?id=<?php echo urlencode($objeto_id); ?>"
                                        class="enlace"
                                    >
                                        Ver objeto
                                    </a>

                                <?php else: ?>

                                    <p>
                                        Objeto no disponible.
                                    </p>

                                <?php endif; ?>

                            </div>


                            <!-- Bloque de información del objeto perdido vinculado -->
                            <div class="bloque">

                                <h4>
                                    Objeto perdido relacionado
                                </h4>

                                <?php if (!empty($objeto_perdido)): ?>

                                    <p>
                                        <strong>
                                            Nombre:
                                        </strong>

                                        <?php
                                        echo htmlspecialchars(
                                            $objeto_perdido['nombre'] ?? 'Sin nombre'
                                        );
                                        ?>
                                    </p>

                                    <p>
                                        <strong>
                                            ID:
                                        </strong>

                                        <?php
                                        echo htmlspecialchars(
                                            $objeto_perdido_id
                                        );
                                        ?>
                                    </p>

                                    <a
                                        href="objeto.php?id=<?php echo urlencode($objeto_perdido_id); ?>"
                                        class="enlace"
                                    >
                                        Ver objeto perdido
                                    </a>

                                <?php else: ?>

                                    <p>
                                        No existe un objeto perdido relacionado.
                                    </p>

                                <?php endif; ?>

                            </div>


                            <!-- Bloque con la fecha de la reclamación -->
                            <div class="bloque">

                                <h4>
                                    Fecha
                                </h4>

                                <p>

                                    <?php

                                    if (!empty($fecha)) {

                                        echo date(
                                            'd/m/Y H:i',
                                            strtotime($fecha)
                                        );

                                    } else {

                                        echo 'Sin fecha';

                                    }

                                    ?>

                                </p>

                            </div>

                        </div>


                        <!-- DESCRIPCIÓN Y JUSTIFICACIÓN PROPORCIONADA POR EL USUARIO -->

                        <div class="descripcion">

                            <h4>
                                Información proporcionada para verificar la propiedad
                            </h4>

                            <p>
                                <?php
                                echo nl2br(
                                    htmlspecialchars(
                                        $descripcion
                                    )
                                );
                                ?>
                            </p>

                        </div>


                        <!-- INFORMACIÓN DEL DOCENTE A CARGO -->

                        <div class="docente-info">

                            <strong>
                                Docente responsable:
                            </strong>

                            <?php if (!empty($docente_id)): ?>

                                ID <?php
                                echo htmlspecialchars(
                                    $docente_id
                                );
                                ?>

                            <?php else: ?>

                                Pendiente de asignar

                            <?php endif; ?>

                        </div>


                        <!-- BOTONES DE ACCIÓN DE LA TARJETA -->

                        <div class="acciones">

                            <a
                                href="objeto.php?id=<?php echo urlencode($objeto_id); ?>"
                                class="boton-secundario"
                            >
                                Ver objeto
                            </a>

                        </div>

                    </article>

                <?php endforeach; ?>

            <?php endif; ?>

        </div>

    </section>

</main>


<!-- PIE DE PÁGINA -->
<footer>

    <p>
        Universidad Veracruzana - Facultad
    </p>

</footer>

</body>
</html>