<?php

// ============================================================
// VERIFICAR SESIÓN
// ============================================================

// Incluye el script encargando de comprobar que la sesión esté iniciada.
require_once '../backend/verificar_sesion.php';

// Carga las variables y constantes de configuración (URLs y llaves de acceso).
require_once '../backend/config.php';


// ============================================================
// VERIFICAR QUE EL USUARIO SEA ADMINISTRADOR
// ============================================================

// Comprueba que el rol almacenado en la sesión corresponda a 'administrador'.
// Si el rol no está definido o no coincide, restringe el acceso y frena el script.
if (
    !isset($_SESSION['rol']) ||
    $_SESSION['rol'] !== 'administrador'
) {
    echo "Acceso no autorizado.";
    exit;
}


// ============================================================
// FUNCIÓN PARA CONSULTAR SUPABASE
// ============================================================

/**
 * Realiza peticiones HTTP de tipo GET a la API REST de Supabase.
 *
 * @param string $url URL del recurso a consultar en Supabase.
 * @return array Arreglo con la respuesta codificada de la consulta o un arreglo vacío en caso de falla.
 */
function consultarSupabase($url)
{
    // Define las opciones de la petición HTTP con sus cabeceras correspondientes.
    $options = [
        'http' => [
            // Método de la consulta HTTP.
            'method' => 'GET',

            // Cabeceras de autenticación (apikey y Bearer Token).
            'header' =>
                "apikey: " .
                SUPABASE_KEY .
                "\r\n" .

                "Authorization: Bearer " .
                SUPABASE_KEY .
                "\r\n" .

                "Content-Type: application/json\r\n",

            // Evita detener la ejecución en caso de respuesta con error HTTP.
            'ignore_errors' => true
        ]
    ];

    // Crea el contexto para la transmisión de datos HTTP.
    $context =
        stream_context_create($options);

    // Obtiene el contenido del recurso indicado por la URL.
    $response =
        file_get_contents(
            $url,
            false,
            $context
        );

    // Devuelve un arreglo vacío si no se recibió respuesta de la URL.
    if ($response === false) {
        return [];
    }

    // Decodifica la respuesta JSON recibida en un arreglo asociativo.
    $datos =
        json_decode(
            $response,
            true
        );

    // Verifica que el contenido parseado sea un arreglo válido.
    if (!is_array($datos)) {
        return [];
    }

    return $datos;
}


// ============================================================
// USUARIOS
// ============================================================

// URL para consultar todos los identificadores de la tabla usuarios.
$url_usuarios =
    SUPABASE_URL .
    '/rest/v1/usuarios?select=id';

// Realiza la consulta HTTP a Supabase.
$usuarios =
    consultarSupabase(
        $url_usuarios
    );

// Cuenta el total de usuarios obtenidos.
$total_usuarios =
    count($usuarios);


// ============================================================
// OBJETOS
// ============================================================

// URL para consultar el total de objetos registrados.
$url_objetos =
    SUPABASE_URL .
    '/rest/v1/objetos?select=id';

// Ejecuta la consulta de la lista completa de objetos.
$objetos =
    consultarSupabase(
        $url_objetos
    );

// Cuenta el total general de objetos.
$total_objetos =
    count($objetos);


// ============================================================
// OBJETOS PERDIDOS
// ============================================================

// URL para obtener objetos cuyo estado sea 'Perdido'.
$url_perdidos =
    SUPABASE_URL .
    '/rest/v1/objetos' .
    '?estado=eq.Perdido' .
    '&select=id';

// Ejecuta la consulta de objetos perdidos.
$perdidos =
    consultarSupabase(
        $url_perdidos
    );

// Cuenta el total de objetos perdidos.
$total_perdidos =
    count($perdidos);


// ============================================================
// OBJETOS ENCONTRADOS
// ============================================================

// URL para consultar los objetos clasificados como 'Encontrado'.
$url_encontrados =
    SUPABASE_URL .
    '/rest/v1/objetos' .
    '?estado=eq.Encontrado' .
    '&select=id';

// Ejecuta la consulta de objetos encontrados.
$encontrados =
    consultarSupabase(
        $url_encontrados
    );

// Cuenta el total de objetos encontrados.
$total_encontrados =
    count($encontrados);


// ============================================================
// OBJETOS EN RESGUARDO
// ============================================================

// URL para obtener la cantidad de objetos bajo el estado 'En resguardo'.
$url_resguardo =
    SUPABASE_URL .
    '/rest/v1/objetos' .
    '?estado=eq.En%20resguardo' .
    '&select=id';

// Consulta los objetos en resguardo.
$resguardo =
    consultarSupabase(
        $url_resguardo
    );

// Cuenta el total de objetos bajo resguardo.
$total_resguardo =
    count($resguardo);


// ============================================================
// OBJETOS RECUPERADOS
// ============================================================

// URL para filtrar los objetos entregados o bajo el estado 'Recuperado'.
$url_recuperados =
    SUPABASE_URL .
    '/rest/v1/objetos' .
    '?estado=eq.Recuperado' .
    '&select=id';

// Consulta los objetos recuperados.
$recuperados =
    consultarSupabase(
        $url_recuperados
    );

// Cuenta el total de objetos recuperados.
$total_recuperados =
    count($recuperados);


// ============================================================
// RECLAMACIONES TOTALES
// ============================================================

// URL para pedir todos los registros de la tabla reclamaciones.
$url_reclamaciones =
    SUPABASE_URL .
    '/rest/v1/reclamaciones?select=id';

// Consulta la lista general de reclamaciones.
$reclamaciones =
    consultarSupabase(
        $url_reclamaciones
    );

// Cuenta el total general de reclamaciones.
$total_reclamaciones =
    count($reclamaciones);


// ============================================================
// RECLAMACIONES PENDIENTES
// ============================================================

// URL para filtrar reclamaciones registradas con estado 'Pendiente'.
$url_pendientes =
    SUPABASE_URL .
    '/rest/v1/reclamaciones' .
    '?estado=eq.Pendiente' .
    '&select=id';

// Consulta las reclamaciones pendientes.
$pendientes =
    consultarSupabase(
        $url_pendientes
    );

// Cuenta las reclamaciones pendientes de atención.
$total_pendientes =
    count($pendientes);


// ============================================================
// RECLAMACIONES CERRADAS
// ============================================================

// URL para filtrar reclamaciones finalizadas bajo el estado 'Cerrada'.
$url_cerradas =
    SUPABASE_URL .
    '/rest/v1/reclamaciones' .
    '?estado=eq.Cerrada' .
    '&select=id';

// Consulta las reclamaciones cerradas.
$cerradas =
    consultarSupabase(
        $url_cerradas
    );

// Cuenta las reclamaciones concluidas.
$total_cerradas =
    count($cerradas);


// ============================================================
// HISTORIAL DE OBJETOS
// ============================================================

// URL para obtener los movimientos registrados en la tabla historial_objetos.
$url_historial =
    SUPABASE_URL .
    '/rest/v1/historial_objetos?select=id';

// Consulta el historial de cambios de los objetos.
$historial =
    consultarSupabase(
        $url_historial
    );

// Cuenta la cantidad total de movimientos en la bitácora.
$total_movimientos =
    count($historial);

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <!-- Codificación de caracteres estándar -->
    <meta charset="UTF-8">

    <!-- Configuración para asegurar que el sitio sea responsivo -->
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Panel de administrador
    </title>

    <!-- Hoja de estilos del panel de administración -->
    <link
        rel="stylesheet"
        href="css/administrador.css"
    >

    <!-- Script de apoyo del panel cargado de forma diferida -->
    <script
        src="js/administrador.js"
        defer
    ></script>

</head>


<body>


<!-- ============================================================
     ENCABEZADO
     ============================================================ -->

<header class="encabezado">

    <div>

        <h1>
            Panel de administrador
        </h1>

        <p>
            Bienvenido,
            <strong>
                <?php
                // Escapa caracteres especiales del nombre almacenado en la sesión
                echo htmlspecialchars(
                    $_SESSION['nombre']
                );
                ?>
            </strong>
        </p>

    </div>


    <div class="acciones-encabezado">

        <!-- Botón para refrescar métricas o recargar elementos dinámicos -->
        <button
            type="button"
            id="boton-actualizar"
        >
            Actualizar estadísticas
        </button>

        <!-- Enlace para retornar a la vista principal -->
        <a
            href="index.php"
            class="boton-inicio"
        >
            Volver al inicio
        </a>

    </div>

</header>


<!-- ============================================================
     CONTENIDO PRINCIPAL
     ============================================================ -->

<main class="contenedor">


    <section class="bienvenida">

        <h2>
            Resumen del sistema
        </h2>

        <p>
            Consulta las estadísticas actuales de usuarios,
            objetos, reclamaciones y movimientos registrados.
        </p>

    </section>


    <!-- ========================================================
         ESTADÍSTICAS GENERALES
         ======================================================== -->

    <section class="seccion">

        <h2>
            Estadísticas generales
        </h2>


        <div class="estadisticas">


            <!-- Tarjeta que indica el total de usuarios registrados -->
            <article class="tarjeta estadistica">

                <span class="icono">
                    👥
                </span>

                <div>

                    <h3>
                        Usuarios
                    </h3>

                    <strong
                        class="numero"
                        data-valor="<?php echo $total_usuarios; ?>"
                    >
                        <?php echo $total_usuarios; ?>
                    </strong>

                </div>

            </article>


            <!-- Tarjeta con el recuento total de objetos -->
            <article class="tarjeta estadistica">

                <span class="icono">
                    📦
                </span>

                <div>

                    <h3>
                        Objetos
                    </h3>

                    <strong
                        class="numero"
                        data-valor="<?php echo $total_objetos; ?>"
                    >
                        <?php echo $total_objetos; ?>
                    </strong>

                </div>

            </article>


            <!-- Tarjeta con la cantidad de solicitudes de reclamación -->
            <article class="tarjeta estadistica">

                <span class="icono">
                    📋
                </span>

                <div>

                    <h3>
                        Reclamaciones
                    </h3>

                    <strong
                        class="numero"
                        data-valor="<?php echo $total_reclamaciones; ?>"
                    >
                        <?php echo $total_reclamaciones; ?>
                    </strong>

                </div>

            </article>


            <!-- Tarjeta con el total de movimientos/cambios en la bitácora -->
            <article class="tarjeta estadistica">

                <span class="icono">
                    📝
                </span>

                <div>

                    <h3>
                        Movimientos
                    </h3>

                    <strong
                        class="numero"
                        data-valor="<?php echo $total_movimientos; ?>"
                    >
                        <?php echo $total_movimientos; ?>
                    </strong>

                </div>

            </article>


        </div>

    </section>


    <!-- ========================================================
         ESTADOS DE LOS OBJETOS
         ======================================================== -->

    <section class="seccion">

        <h2>
            Estado de los objetos
        </h2>


        <div class="estadisticas">


            <!-- Indicador de objetos perdidos -->
            <article class="tarjeta estado-perdido">

                <h3>
                    Objetos perdidos
                </h3>

                <strong
                    class="numero"
                    data-valor="<?php echo $total_perdidos; ?>"
                >
                    <?php echo $total_perdidos; ?>
                </strong>

                <p>
                    Objetos reportados como perdidos.
                </p>

            </article>


            <!-- Indicador de objetos encontrados -->
            <article class="tarjeta estado-encontrado">

                <h3>
                    Objetos encontrados
                </h3>

                <strong
                    class="numero"
                    data-valor="<?php echo $total_encontrados; ?>"
                >
                    <?php echo $total_encontrados; ?>
                </strong>

                <p>
                    Objetos encontrados y disponibles.
                </p>

            </article>


            <!-- Indicador de objetos en resguardo -->
            <article class="tarjeta estado-resguardo">

                <h3>
                    En resguardo
                </h3>

                <strong
                    class="numero"
                    data-valor="<?php echo $total_resguardo; ?>"
                >
                    <?php echo $total_resguardo; ?>
                </strong>

                <p>
                    Objetos bajo resguardo docente.
                </p>

            </article>


            <!-- Indicador de objetos recuperados -->
            <article class="tarjeta estado-recuperado">

                <h3>
                    Recuperados
                </h3>

                <strong
                    class="numero"
                    data-valor="<?php echo $total_recuperados; ?>"
                >
                    <?php echo $total_recuperados; ?>
                </strong>

                <p>
                    Objetos entregados a sus propietarios.
                </p>

            </article>


        </div>

    </section>


    <!-- ========================================================
         RECLAMACIONES
         ======================================================== -->

    <section class="seccion">

        <h2>
            Estado de las reclamaciones
        </h2>


        <div class="estadisticas">


            <!-- Recuento global de reclamaciones -->
            <article class="tarjeta">

                <h3>
                    Total
                </h3>

                <strong
                    class="numero"
                    data-valor="<?php echo $total_reclamaciones; ?>"
                >
                    <?php echo $total_reclamaciones; ?>
                </strong>

            </article>


            <!-- Muestra de reclamaciones pendientes -->
            <article class="tarjeta">

                <h3>
                    Pendientes
                </h3>

                <strong
                    class="numero"
                    data-valor="<?php echo $total_pendientes; ?>"
                >
                    <?php echo $total_pendientes; ?>
                </strong>

                <p>
                    Requieren revisión docente.
                </p>

            </article>


            <!-- Muestra de reclamaciones cerradas -->
            <article class="tarjeta">

                <h3>
                    Cerradas
                </h3>

                <strong
                    class="numero"
                    data-valor="<?php echo $total_cerradas; ?>"
                >
                    <?php echo $total_cerradas; ?>
                </strong>

                <p>
                    Reclamaciones finalizadas.
                </p>

            </article>


        </div>

    </section>


    <!-- ========================================================
         ACCIONES DEL ADMINISTRADOR
         ======================================================== -->

    <section class="seccion">

        <h2>
            Administración del sistema
        </h2>


        <div class="acciones">


            <!-- Enlace directo al módulo de gestión de objetos -->
            <a
                href="admin_objetos.php"
                class="accion"
            >

                <span>
                    📦
                </span>

                <div>

                    <strong>
                        Gestionar objetos
                    </strong>

                    <p>
                        Consultar y administrar objetos.
                    </p>

                </div>

            </a>


            <!-- Enlace directo al módulo de administración de usuarios -->
            <a
                href="admin_usuarios.php"
                class="accion"
            >

                <span>
                    👥
                </span>

                <div>

                    <strong>
                        Gestionar usuarios
                    </strong>

                    <p>
                        Consultar usuarios registrados.
                    </p>

                </div>

            </a>


            <!-- Enlace directo al módulo de atención a reclamaciones -->
            <a
                href="admin_reclamaciones.php"
                class="accion"
            >

                <span>
                    📋
                </span>

                <div>

                    <strong>
                        Gestionar reclamaciones
                    </strong>

                    <p>
                        Consultar las reclamaciones del sistema.
                    </p>

                </div>

            </a>


            <!-- Enlace directo al módulo de historial global -->
            <a
                href="admin_historial.php"
                class="accion"
            >

                <span>
                    📜
                </span>

                <div>

                    <strong>
                        Historial del sistema
                    </strong>

                    <p>
                        Consultar movimientos de objetos.
                    </p>

                </div>

            </a>


        </div>

    </section>


</main>


<!-- Pie de página informativo -->
<footer>

    <p>
        Universidad Veracruzana - Facultad
    </p>

</footer>


</body>

</html>