<?php
// ============================================================
// 1. VERIFICAR SESIÓN Y CONFIGURACIÓN
// ============================================================

// Incluye el archivo que verifica que el usuario tenga una sesión activa en el sistema.
require_once '../backend/verificar_sesion.php';

// Incluye la configuración global del proyecto (URLs, claves API de Supabase, etc.).
require_once '../backend/config.php';

// ============================================================
// 2. VERIFICAR QUE SEA ADMINISTRADOR
// ============================================================

// Comprueba si existe la variable de sesión 'rol' o si el rol no es igual a 'administrador'.
if (
    !isset($_SESSION['rol']) ||
    $_SESSION['rol'] !== 'administrador'
) {
    // Establece el código de estado HTTP 403 (Prohibido/Acceso denegado).
    http_response_code(403);
    // Finaliza la ejecución del script mostrando un mensaje de error.
    exit('Acceso no autorizado.');
}

// ============================================================
// 3. FUNCIÓN PARA CONSULTAR SUPABASE
// ============================================================

/**
 * Realiza una petición GET a la API REST de Supabase.
 *
 * @param string $url Endpoint completo con parámetros de consulta.
 * @return array Arreglo asociativo con la respuesta o arreglo vacío en caso de error.
 */
function consultarSupabase($url)
{
    // Define la configuración y cabeceras de la petición HTTP.
    $options = [
        'http' => [
            'method' => 'GET',
            'header' =>
                "apikey: " . SUPABASE_KEY . "\r\n" .
                "Authorization: Bearer " . SUPABASE_KEY . "\r\n" .
                "Accept: application/json\r\n",
            'ignore_errors' => true,
            'timeout' => 15
        ]
    ];

    // Crea el contexto de transmisión para la petición con las opciones configuradas.
    $context = stream_context_create($options);

    // Obtiene el contenido de la URL suprimiendo advertencias con @.
    $response = @file_get_contents(
        $url,
        false,
        $context
    );

    // Retorna un arreglo vacío si la conexión o lectura fallaron.
    if ($response === false) {
        return [];
    }

    // Convierte la cadena JSON recibida en un arreglo asociativo de PHP.
    $datos = json_decode($response, true);

    // Si el resultado no es un arreglo válido, devuelve un arreglo vacío.
    if (!is_array($datos)) {
        return [];
    }

    // Retorna los datos procesados.
    return $datos;
}

// ============================================================
// 4. CONSULTAR USUARIOS
// ============================================================

// Realiza la consulta a la tabla 'usuarios' obteniendo únicamente los campos 'id' y 'rol'.
$usuarios = consultarSupabase(
    SUPABASE_URL .
    '/rest/v1/usuarios?select=id,rol'
);

// Obtiene la cantidad total de usuarios registrados.
$total_usuarios = count($usuarios);

// Inicializa contadores para cada rol de usuario.
$total_alumnos = 0;
$total_docentes = 0;
$total_administradores = 0;

// Recorre cada registro para clasificar los usuarios según su rol.
foreach ($usuarios as $usuario) {

    // Normaliza el texto del rol convirtiéndolo a minúsculas y limpiando espacios.
    $rol = strtolower(
        trim($usuario['rol'] ?? '')
    );

    // Incrementa el contador correspondiente según la coincidencia de rol.
    if ($rol === 'alumno') {
        $total_alumnos++;
    } elseif ($rol === 'docente') {
        $total_docentes++;
    } elseif ($rol === 'administrador') {
        $total_administradores++;
    }
}

// ============================================================
// 5. CONSULTAR OBJETOS
// ============================================================

// Realiza la consulta a la tabla 'objetos' solicitando 'id' y 'estado'.
$objetos = consultarSupabase(
    SUPABASE_URL .
    '/rest/v1/objetos?select=id,estado'
);

// Obtiene el total de objetos registrados en el sistema.
$total_objetos = count($objetos);

// Inicializa contadores para cada estado posible de los objetos.
$total_perdidos = 0;
$total_encontrados = 0;
$total_resguardo = 0;
$total_recuperados = 0;

// Recorre cada objeto para agruparlo según su estado actual.
foreach ($objetos as $objeto) {

    // Normaliza la cadena de texto del estado.
    $estado = strtolower(
        trim($objeto['estado'] ?? '')
    );

    // Acumula el conteo correspondiente para cada estado.
    if ($estado === 'perdido') {
        $total_perdidos++;
    } elseif ($estado === 'encontrado') {
        $total_encontrados++;
    } elseif ($estado === 'en resguardo') {
        $total_resguardo++;
    } elseif ($estado === 'recuperado') {
        $total_recuperados++;
    }
}

// ============================================================
// 6. CONSULTAR RECLAMACIONES
// ============================================================

// Consulta la tabla 'reclamaciones' obteniendo únicamente los identificadores y estados.
$reclamaciones = consultarSupabase(
    SUPABASE_URL .
    '/rest/v1/reclamaciones?select=id,estado'
);

// Cuenta el total de solicitudes de reclamación recibidas.
$total_reclamaciones = count($reclamaciones);

// Inicializa contadores para la clasificación por estados de reclamación.
$total_pendientes = 0;
$total_aprobadas = 0;
$total_rechazadas = 0;
$total_cerradas = 0;

// Recorre las reclamaciones contabilizando cada estado.
foreach ($reclamaciones as $reclamacion) {

    // Normaliza el estado en minúsculas y sin espacios iniciales/finales.
    $estado = strtolower(
        trim($reclamacion['estado'] ?? '')
    );

    // Incrementa las métricas de estado de las reclamaciones.
    if ($estado === 'pendiente') {
        $total_pendientes++;
    } elseif ($estado === 'aprobada') {
        $total_aprobadas++;
    } elseif ($estado === 'rechazada') {
        $total_rechazadas++;
    } elseif (
        $estado === 'cerrada' ||
        $estado === 'cerrada '
    ) {
        $total_cerradas++;
    }
}

// ============================================================
// 7. CONSULTAR HISTORIAL
// ============================================================

// Consulta los registros de la tabla 'historial_objetos'.
$historial = consultarSupabase(
    SUPABASE_URL .
    '/rest/v1/historial_objetos?select=id'
);

// Cuenta el total de movimientos/eventos registrados en el historial del sistema.
$total_movimientos = count($historial);

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <!-- Codificación de caracteres estándar UTF-8 -->
    <meta charset="UTF-8">

    <!-- Configuración para el diseño adaptable en dispositivos móviles -->
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Panel de administrador</title>

    <!-- Enlace a la hoja de estilos del panel de administración -->
    <link
        rel="stylesheet"
        href="css/administrador.css"
    >

    <!-- Script ejecutable diferido para soporte dinámico del panel -->
    <script
        src="js/administrador.js"
        defer
    ></script>

</head>

<body>

<!-- ============================================================
     ENCABEZADO PRINCIPAL DE LA PÁGINA
     ============================================================ -->

<header class="encabezado">

    <div>

        <h1>Panel de administrador</h1>

        <!-- Muestra el nombre del usuario administrador desde la variable de sesión -->
        <p>
            Bienvenido,
            <strong>
                <?php
                echo htmlspecialchars(
                    $_SESSION['nombre'] ?? 'Administrador',
                    ENT_QUOTES,
                    'UTF-8'
                );
                ?>
            </strong>
        </p>

    </div>

    <!-- Botones de navegación y recarga dentro del encabezado -->
    <div class="acciones-encabezado">

        <button
            type="button"
            id="boton-actualizar"
            onclick="window.location.reload()"
        >
            Actualizar estadísticas
        </button>

        <a
            href="index.php"
            class="boton-inicio"
        >
            Volver al inicio
        </a>

    </div>

</header>


<!-- CONTENEDOR DE CONTENIDO PRINCIPAL -->
<main class="contenedor">

    <!-- ========================================================
         SECCIÓN DE BIENVENIDA E INTRODUCCIÓN
         ======================================================== -->

    <section class="bienvenida">

        <h2>Resumen del sistema</h2>

        <p>
            Consulta las estadísticas de usuarios, objetos,
            reclamaciones y movimientos registrados.
        </p>

    </section>


    <!-- ========================================================
         ESTADÍSTICAS GENERALES DE LA PLATAFORMA
         ======================================================== -->

    <section class="seccion">

        <h2>Estadísticas generales</h2>

        <div class="estadisticas">

            <!-- Tarjeta con el total global de usuarios -->
            <article class="tarjeta estadistica">

                <span class="icono">👥</span>

                <div>

                    <h3>Usuarios</h3>

                    <strong
                        class="numero"
                        data-valor="<?php echo $total_usuarios; ?>"
                    >
                        <?php echo $total_usuarios; ?>
                    </strong>

                </div>

            </article>


            <!-- Tarjeta con el total global de objetos -->
            <article class="tarjeta estadistica">

                <span class="icono">📦</span>

                <div>

                    <h3>Objetos</h3>

                    <strong
                        class="numero"
                        data-valor="<?php echo $total_objetos; ?>"
                    >
                        <?php echo $total_objetos; ?>
                    </strong>

                </div>

            </article>


            <!-- Tarjeta con el total global de reclamaciones -->
            <article class="tarjeta estadistica">

                <span class="icono">📋</span>

                <div>

                    <h3>Reclamaciones</h3>

                    <strong
                        class="numero"
                        data-valor="<?php echo $total_reclamaciones; ?>"
                    >
                        <?php echo $total_reclamaciones; ?>
                    </strong>

                </div>

            </article>


            <!-- Tarjeta con el total global de movimientos en el historial -->
            <article class="tarjeta estadistica">

                <span class="icono">📝</span>

                <div>

                    <h3>Movimientos</h3>

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
         DESGLOSE DE USUARIOS POR ROL
         ======================================================== -->

    <section class="seccion">

        <h2>Usuarios por rol</h2>

        <div class="estadisticas">

            <!-- Conteo de alumnos -->
            <article class="tarjeta">

                <h3>Alumnos</h3>

                <strong class="numero">
                    <?php echo $total_alumnos; ?>
                </strong>

                <p>Usuarios con rol de alumno.</p>

            </article>


            <!-- Conteo de docentes -->
            <article class="tarjeta">

                <h3>Docentes</h3>

                <strong class="numero">
                    <?php echo $total_docentes; ?>
                </strong>

                <p>Usuarios con rol de docente.</p>

            </article>


            <!-- Conteo de administradores -->
            <article class="tarjeta">

                <h3>Administradores</h3>

                <strong class="numero">
                    <?php echo $total_administradores; ?>
                </strong>

                <p>Usuarios con rol de administrador.</p>

            </article>

        </div>

    </section>


    <!-- ========================================================
         DESGLOSE DEL ESTADO DE LOS OBJETOS
         ======================================================== -->

    <section class="seccion">

        <h2>Estado de los objetos</h2>

        <div class="estadisticas">

            <!-- Tarjeta de objetos perdidos -->
            <article class="tarjeta estado-perdido">

                <h3>Objetos perdidos</h3>

                <strong class="numero">
                    <?php echo $total_perdidos; ?>
                </strong>

                <p>Objetos reportados como perdidos.</p>

            </article>


            <!-- Tarjeta de objetos encontrados -->
            <article class="tarjeta estado-encontrado">

                <h3>Objetos encontrados</h3>

                <strong class="numero">
                    <?php echo $total_encontrados; ?>
                </strong>

                <p>Objetos encontrados registrados.</p>

            </article>


            <!-- Tarjeta de objetos bajo resguardo -->
            <article class="tarjeta estado-resguardo">

                <h3>En resguardo</h3>

                <strong class="numero">
                    <?php echo $total_resguardo; ?>
                </strong>

                <p>Objetos bajo resguardo docente.</p>

            </article>


            <!-- Tarjeta de objetos recuperados -->
            <article class="tarjeta estado-recuperado">

                <h3>Recuperados</h3>

                <strong class="numero">
                    <?php echo $total_recuperados; ?>
                </strong>

                <p>Objetos con entrega registrada.</p>

            </article>

        </div>

    </section>


    <!-- ========================================================
         DESGLOSE DEL ESTADO DE LAS RECLAMACIONES
         ======================================================== -->

    <section class="seccion">

        <h2>Estado de las reclamaciones</h2>

        <div class="estadisticas">

            <!-- Total de reclamaciones -->
            <article class="tarjeta">

                <h3>Total</h3>

                <strong class="numero">
                    <?php echo $total_reclamaciones; ?>
                </strong>

                <p>Todas las reclamaciones registradas.</p>

            </article>


            <!-- Reclamaciones pendientes -->
            <article class="tarjeta">

                <h3>Pendientes</h3>

                <strong class="numero">
                    <?php echo $total_pendientes; ?>
                </strong>

                <p>Esperan revisión del docente.</p>

            </article>


            <!-- Reclamaciones aprobadas -->
            <article class="tarjeta">

                <h3>Aprobadas</h3>

                <strong class="numero">
                    <?php echo $total_aprobadas; ?>
                </strong>

                <p>Reclamaciones aprobadas.</p>

            </article>


            <!-- Reclamaciones rechazadas -->
            <article class="tarjeta">

                <h3>Rechazadas</h3>

                <strong class="numero">
                    <?php echo $total_rechazadas; ?>
                </strong>

                <p>Reclamaciones rechazadas.</p>

            </article>


            <!-- Reclamaciones cerradas -->
            <article class="tarjeta">

                <h3>Cerradas</h3>

                <strong class="numero">
                    <?php echo $total_cerradas; ?>
                </strong>

                <p>Procesos de reclamación finalizados.</p>

            </article>

        </div>

    </section>


    <!-- ========================================================
         ACCESOS DIRECTOS A LOS MÓDULOS DE ADMINISTRACIÓN
         ======================================================== -->

    <section class="seccion">

        <h2>Administración del sistema</h2>

        <div class="acciones">

            <!-- Enlace al módulo de gestión de objetos -->
            <a
                href="admin_objetos.php"
                class="accion"
            >

                <span>📦</span>

                <div>

                    <strong>Gestionar objetos</strong>

                    <p>
                        Consultar objetos y sus estados.
                    </p>

                </div>

            </a>


            <!-- Enlace al módulo de gestión de usuarios -->
            <a
                href="admin_usuarios.php"
                class="accion"
            >

                <span>👥</span>

                <div>

                    <strong>Gestionar usuarios</strong>

                    <p>
                        Consultar usuarios y roles.
                    </p>

                </div>

            </a>


            <!-- Enlace al módulo de gestión de reclamaciones -->
            <a
                href="admin_reclamaciones.php"
                class="accion"
            >

                <span>📋</span>

                <div>

                    <strong>Gestionar reclamaciones</strong>

                    <p>
                        Consultar reclamaciones y tickets.
                    </p>

                </div>

            </a>


            <!-- Enlace al historial de movimientos -->
            <a
                href="admin_historial.php"
                class="accion"
            >

                <span>📜</span>

                <div>

                    <strong>Historial del sistema</strong>

                    <p>
                        Consultar los movimientos de los objetos.
                    </p>

                </div>

            </a>

        </div>

    </section>

</main>


<!-- PIE DE PÁGINA -->
<footer>

    <p>Universidad Veracruzana - Facultad</p>

</footer>

</body>
</html>