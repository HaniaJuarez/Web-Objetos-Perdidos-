<?php

// ============================================================
// CONFIGURACIÓN E INICIALIZACIÓN DE SESIÓN
// ============================================================

// Incluye el script encargado de verificar que la sesión del usuario esté activa.
require_once '../backend/verificar_sesion.php';

// Carga las variables y constantes globales de configuración (URL y API Key de Supabase).
require_once '../backend/config.php';


// ============================================================
// VERIFICAR ROL DE ADMINISTRADOR
// ============================================================

// Valida que el usuario autenticado cuente con el rol 'administrador' en $_SESSION.
// Si no cuenta con el rol o la sesión no existe, detiene el procesamiento y muestra error.
if (
    !isset($_SESSION['rol']) ||
    $_SESSION['rol'] !== 'administrador'
) {
    echo "Acceso no autorizado.";
    exit;
}


// ============================================================
// CONSULTAR USUARIOS EN SUPABASE
// ============================================================

// Construye la URL de la API REST para obtener los campos de la tabla 'usuarios',
// ordenando los resultados de manera descendente por su ID.
$url =
    SUPABASE_URL .
    '/rest/v1/usuarios' .
    '?select=id,nombre,correo,rol,foto' .
    '&order=id.desc';


// Configura los encabezados HTTP para la solicitud a Supabase (autenticación y tipo de contenido).
$options = [
    'http' => [
        // Establece el método HTTP a GET.
        'method' => 'GET',

        // Encabezados con las llaves de acceso (apikey y Bearer token) y tipo de respuesta.
        'header' =>
            "apikey: " .
            SUPABASE_KEY .
            "\r\n" .

            "Authorization: Bearer " .
            SUPABASE_KEY .
            "\r\n" .

            "Content-Type: application/json\r\n",

        // Permite procesar la respuesta incluso si el servidor retorna un código de error HTTP.
        'ignore_errors' => true
    ]
];


// Crea el flujo de contexto HTTP necesario para la consulta.
$context =
    stream_context_create($options);


// Realiza la petición GET a la API REST de Supabase.
$response =
    file_get_contents(
        $url,
        false,
        $context
    );


// Comprueba si la petición devolvió un error de conexión o fallo de consulta.
if ($response === false) {

    echo "Error al consultar los usuarios.";

    exit;
}


// Decodifica la respuesta JSON recibida a un arreglo asociativo de PHP.
$usuarios =
    json_decode(
        $response,
        true
    );


// Asigna un arreglo vacío si la decodificación del JSON no retorna una estructura válida.
if (!is_array($usuarios)) {

    $usuarios = [];

}


// ============================================================
// CONTADORES Y MÉTRICAS DE USUARIOS
// ============================================================

// Conteo total de usuarios obtenidos de la consulta.
$total_usuarios = count($usuarios);

// Inicializa los contadores para cada rol específico dentro del sistema.
$total_alumnos = 0;
$total_docentes = 0;
$total_administradores = 0;


// Recorre el arreglo de usuarios para clasificar y acumular el total por cada rol.
foreach ($usuarios as $usuario) {

    // Obtiene el rol del usuario o asigna una cadena vacía en caso de no existir.
    $rol = $usuario['rol'] ?? '';

    // Incrementa el contador correspondiente según el tipo de rol encontrado.
    if ($rol === 'alumno') {

        $total_alumnos++;

    } elseif ($rol === 'docente') {

        $total_docentes++;

    } elseif ($rol === 'administrador') {

        $total_administradores++;

    }

}

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <!-- Define la codificación de caracteres en UTF-8 -->
    <meta charset="UTF-8">

    <!-- Configuración para el diseño responsivo en dispositivos móviles -->
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Administración de usuarios
    </title>

    <!-- Hoja de estilos CSS específica del panel de administración de usuarios -->
    <link
        rel="stylesheet"
        href="css/admin_usuarios.css"
    >

    <!-- Script JavaScript diferido para el filtrado en tiempo real de la tabla -->
    <script
        src="js/admin_usuarios.js"
        defer
    ></script>

</head>


<body>


<!-- ============================================================
     ENCABEZADO PRINCIPAL DE LA VISTA
     ============================================================ -->

<header class="encabezado">

    <div>

        <h1>
            Administración de usuarios
        </h1>

        <p>
            Consulta los usuarios registrados en el sistema.
        </p>

    </div>


    <div class="acciones-encabezado">

        <!-- Enlace para regresar al panel principal de administración -->
        <a
            href="administrador.php"
            class="boton"
        >
            ← Panel administrador
        </a>

    </div>

</header>


<!-- ============================================================
     CONTENIDO PRINCIPAL
     ============================================================ -->

<main class="contenedor">


    <!-- ========================================================
         RESUMEN Y ESTADÍSTICAS POR ROL
         ======================================================== -->

    <section class="resumen">

        <h2>
            Usuarios registrados
        </h2>

        <p>

            Total:

            <strong>
                <?php 
                // Muestra la cantidad total de usuarios registrados
                echo $total_usuarios; 
                ?>
            </strong>

        </p>


        <!-- Desglose visual de contadores divididos por rol -->
        <div class="resumen-roles">

            <div>

                <span>
                    Alumnos
                </span>

                <strong>
                    <?php 
                    // Imprime el total de alumnos
                    echo $total_alumnos; 
                    ?>
                </strong>

            </div>


            <div>

                <span>
                    Docentes
                </span>

                <strong>
                    <?php 
                    // Imprime el total de docentes
                    echo $total_docentes; 
                    ?>
                </strong>

            </div>


            <div>

                <span>
                    Administradores
                </span>

                <strong>
                    <?php 
                    // Imprime el total de administradores
                    echo $total_administradores; 
                    ?>
                </strong>

            </div>

        </div>

    </section>


    <!-- ========================================================
         SECCIÓN DE FILTROS Y BÚSQUEDA
         ======================================================== -->

    <section class="filtros">

        <!-- Caja de texto para buscar por coincidencia de nombre o correo -->
        <div class="campo">

            <label for="buscar">
                Buscar usuario
            </label>

            <input
                type="text"
                id="buscar"
                placeholder="Nombre o correo..."
            >

        </div>


        <!-- Menú desplegable para filtrar los usuarios según su rol -->
        <div class="campo">

            <label for="filtro-rol">
                Rol
            </label>

            <select id="filtro-rol">

                <option value="">
                    Todos
                </option>

                <option value="alumno">
                    Alumno
                </option>

                <option value="docente">
                    Docente
                </option>

                <option value="administrador">
                    Administrador
                </option>

            </select>

        </div>


        <!-- Botón para restablecer los valores de los filtros a su estado por defecto -->
        <button
            type="button"
            id="limpiar-filtros"
            class="boton-secundario"
        >
            Limpiar filtros
        </button>

    </section>


    <!-- ========================================================
         TABLA DE DATOS DE USUARIOS
         ======================================================== -->

    <section class="tabla-contenedor">


        <!-- Condicional que verifica si existen usuarios registrados -->
        <?php if (empty($usuarios)): ?>

            <!-- Mensaje alternativo desplegado cuando la tabla de usuarios está vacía -->
            <div class="sin-resultados">

                <h3>
                    No hay usuarios registrados.
                </h3>

            </div>


        <?php else: ?>


            <!-- Envoltorio con desplazamiento horizontal para tablas responsivas -->
            <div class="tabla-scroll">

                <table id="tabla-usuarios">

                    <thead>

                        <tr>

                            <th>
                                ID
                            </th>

                            <th>
                                Usuario
                            </th>

                            <th>
                                Correo
                            </th>

                            <th>
                                Rol
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                        <!-- Bucle para iterar e imprimir cada usuario como una fila de la tabla -->
                        <?php foreach ($usuarios as $usuario): ?>


                            <?php

                            // Extrae el rol o define un valor por defecto
                            $rol =
                                $usuario['rol']
                                ?? 'Sin rol';

                            ?>


                            <!-- Fila de usuario con data-attributes para filtrado del lado del cliente -->
                            <tr

                                data-nombre="<?php
                                    echo htmlspecialchars(
                                        strtolower(
                                            $usuario['nombre']
                                            ?? ''
                                        )
                                    );
                                ?>"

                                data-correo="<?php
                                    echo htmlspecialchars(
                                        strtolower(
                                            $usuario['correo']
                                            ?? ''
                                        )
                                    );
                                ?>"

                                data-rol="<?php
                                    echo htmlspecialchars(
                                        $rol
                                    );
                                ?>"
                            >


                                <!-- Columna de Identificador -->
                                <td>

                                    <?php
                                    echo htmlspecialchars(
                                        $usuario['id']
                                    );
                                    ?>

                                </td>


                                <!-- Columna de Usuario (Fotografía/Avatar y Nombre) -->
                                <td>

                                    <div class="usuario">

                                        <!-- Verifica si el usuario cuenta con URL de foto de perfil -->
                                        <?php if (!empty($usuario['foto'])): ?>

                                            <img
                                                src="<?php
                                                    echo htmlspecialchars(
                                                        $usuario['foto']
                                                    );
                                                ?>"
                                                alt="Foto del usuario"
                                            >

                                        <?php else: ?>

                                            <!-- Avatar generado con la inicial del nombre cuando no hay foto -->
                                            <div class="avatar">

                                                <?php

                                                echo strtoupper(
                                                    substr(
                                                        $usuario['nombre']
                                                        ?? '?',
                                                        0,
                                                        1
                                                    )
                                                );

                                                ?>

                                            </div>

                                        <?php endif; ?>


                                        <span>

                                            <?php

                                            echo htmlspecialchars(
                                                $usuario['nombre']
                                                ?? 'Sin nombre'
                                            );

                                            ?>

                                        </span>

                                    </div>

                                </td>


                                <!-- Columna de Correo Electrónico -->
                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $usuario['correo']
                                        ?? 'Sin correo'
                                    );

                                    ?>

                                </td>


                                <!-- Columna de Rol asignado -->
                                <td>

                                    <span
                                        class="rol"
                                        data-rol-visual="<?php
                                            echo htmlspecialchars(
                                                $rol
                                            );
                                        ?>"
                                    >

                                        <?php

                                        // Muestra la primera letra del rol en mayúscula
                                        echo htmlspecialchars(
                                            ucfirst($rol)
                                        );

                                        ?>

                                    </span>

                                </td>


                            </tr>


                        <?php endforeach; ?>


                    </tbody>

                </table>

            </div>


            <!-- Bloque de aviso oculto por defecto que activa JS si las búsquedas no devuelven filas -->
            <div
                id="mensaje-sin-resultados"
                class="sin-resultados"
                style="display:none;"
            >

                <h3>
                    No se encontraron usuarios.
                </h3>

                <p>
                    Prueba con otro nombre, correo o rol.
                </p>

            </div>


        <?php endif; ?>


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