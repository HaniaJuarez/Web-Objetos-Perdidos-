<?php

// Incluye el script encargarlo de verificar si existe una sesión activa del usuario.
require_once '../backend/verificar_sesion.php';
// Incluye el archivo de configuración con las variables globales y credenciales necesarias.
require_once '../backend/config.php';

// Obtiene el ID del objeto enviado por URL a través del método GET; si no existe, asigna una cadena vacía.
$id =$_GET['id'] ?? '';

// Comprueba si el ID está vacío para detener la ejecución si no se proporcionó un parámetro válido.
if (empty($id)) {
    // Imprime un mensaje en pantalla indicando la falta del identificador.
    echo "Objeto no especificado.";
    // Detiene completamente la ejecución del script.
    exit;
}

// Construye la URL de la API REST de Supabase filtrando por ID exacto y solicitando la categoría relacionada.
$url =
    SUPABASE_URL .
    '/rest/v1/objetos?id=eq.' .
    urlencode($id) .
    '&select=*,categorias(nombre)';

// Define la configuración y cabeceras de la petición HTTP GET hacia Supabase.
$options = [
    // Define el bloque de opciones para la petición de tipo HTTP.
    'http' => [
        // Establece el método de envío como "GET".
        'method' => 'GET',
        // Define las cabeceras HTTP necesarias para autenticación (API Key y Bearer token) y tipo de respuesta.
        'header' =>
            "apikey: " . SUPABASE_KEY . "\r\n" .
            "Authorization: Bearer " . SUPABASE_KEY . "\r\n" .
            "Content-Type: application/json\r\n",
        // Evita un fallo fatal en PHP si la API responde con un código de error HTTP (como 404 o 500).
        'ignore_errors' => true
    ]
];

// Crea el recurso de contexto con las opciones especificadas para la petición HTTP.
$context = stream_context_create($options);

// Ejecuta la consulta HTTP a la URL de Supabase usando el contexto de flujo creado.
$response = file_get_contents($url,
    false,
    $context
);

// Verifica si ocurrió un fallo crítico en la consulta de red.
if ($response === false) {
    // Imprime un mensaje de error si no se pudo consultar el objeto.
    echo "Error al consultar el objeto.";
    // Finaliza la ejecución del script.
    exit;
}

// Decodifica la respuesta JSON recibida desde Supabase y la convierte en un arreglo asociativo de PHP.
$objetos = json_decode($response, true);

// Comprueba si la lista retornada por la consulta está vacía (no existe un objeto con ese ID).
if (empty($objetos)) {
    // Imprime el mensaje de error de objeto no localizado.
    echo "Objeto no encontrado.";
    // Detiene la ejecución del script.
    exit;
}

// Asigna el primer elemento del arreglo devuelto como el objeto principal a renderizar.
$objeto =$objetos[0];

?>

<!-- Define la estructura base del documento HTML5 -->
<!DOCTYPE html>
<!-- Especifica el idioma del contenido como español -->
<html lang="es">

<!-- Inicio de la sección de metadatos de la página -->
<head>

    <!-- Define la codificación de caracteres como UTF-8 -->
    <meta charset="UTF-8">

    <!-- Configura el comportamiento de la pantalla en dispositivos móviles (responsive design) -->
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <!-- Define el título de la pestaña del navegador escapando caracteres especiales del nombre del objeto -->
    <title>
        <?php echo htmlspecialchars($objeto['nombre']); ?>
    </title>

</head>

<!-- Inicio del cuerpo visible del documento HTML -->
<body>

    <!-- Enlace HTML para regresar a la página principal "index.php" -->
    <a href="index.php">
        ← Volver al inicio
    </a>

    <!-- Muestra el nombre principal del objeto de forma segura utilizando un encabezado H1 -->
    <h1>
        <?php echo htmlspecialchars($objeto['nombre']); ?>
    </h1>


    <!-- Comprueba si el objeto cuenta con una imagen asociada registrada -->
    <?php if (!empty($objeto['imagen'])): ?>

        <!-- Muestra la imagen del objeto sanitizando su atributo de fuente (src) -->
        <img
            src="<?php echo htmlspecialchars($objeto['imagen']); ?>"
            alt="Fotografía del objeto"
            width="300"
        >

    <!-- Cierre de la condición que valida la existencia de la imagen -->
    <?php endif; ?>


    <!-- Párrafo para mostrar la categoría del objeto -->
    <p>

        <!-- Etiqueta de texto en negrita para el título del campo -->
        <strong>Categoría:</strong>

        <?php

        // Imprime de forma segura la categoría o el texto 'Sin categoría' mediante el operador null coalesce (??)
        echo htmlspecialchars(
            $objeto['categorias']['nombre'] ?? 'Sin categoría'
        );

        ?>

    </p>


    <!-- Párrafo para mostrar la descripción pública del objeto -->
    <p>

        <!-- Etiqueta de texto en negrita para el título del campo -->
        <strong>Descripción:</strong>

        <?php

        // Imprime la descripción pública escapando caracteres HTML o una cadena vacía si no existe
        echo htmlspecialchars(
            $objeto['descripcion_publica'] ?? ''
        );

        ?>

    </p>


    <!-- Párrafo para mostrar el color del objeto -->
    <p>

        <!-- Etiqueta de texto en negrita para el título del campo -->
        <strong>Color:</strong>

        <?php

        // Imprime de manera segura el color asignado al objeto o 'No especificado' por defecto
        echo htmlspecialchars(
            $objeto['color'] ?? 'No especificado'
        );

        ?>

    </p>


    <!-- Párrafo para mostrar la fecha de registro/suceso -->
    <p>

        <!-- Etiqueta de texto en negrita para el título del campo -->
        <strong>Fecha:</strong>

        <?php

        // Muestra la fecha asociada al registro de manera segura
        echo htmlspecialchars(
            $objeto['fecha'] ?? ''
        );

        ?>

    </p>


    <!-- Párrafo para mostrar el estado actual (Perdido/Encontrado/etc.) -->
    <p>

        <!-- Etiqueta de texto en negrita para el título del campo -->
        <strong>Estado:</strong>

        <?php

        // Muestra el estado del objeto de forma segura
        echo htmlspecialchars(
            $objeto['estado'] ?? ''
        );

        ?>

    </p>


    <!-- Verifica si la ubicación del objeto se encuentra registrada -->
    <?php if (!empty($objeto['ubicacion'])): ?>

        <!-- Párrafo que contiene los datos de la ubicación -->
        <p>

            <!-- Etiqueta de texto en negrita para el título del campo -->
            <strong>Ubicación:</strong>

            <?php

            // Imprime de forma segura el texto de la ubicación del objeto
            echo htmlspecialchars(
                $objeto['ubicacion']
            );

            ?>

        </p>

    <!-- Cierre del bloque condicional de la ubicación -->
    <?php endif; ?>


    <!-- Verifica si existe un responsable asignado al objeto -->
    <?php if (!empty($objeto['responsable'])): ?>

        <!-- Párrafo que muestra el nombre del responsable -->
        <p>

            <!-- Etiqueta de texto en negrita para el título del campo -->
            <strong>Responsable:</strong>

            <?php

            // Imprime la información del responsable escapando caracteres especiales
            echo htmlspecialchars(
                $objeto['responsable']
            );

            ?>

        </p>

    <!-- Cierre del bloque condicional del responsable -->
    <?php endif; ?>


    <!-- Evalúa si el estado actual del objeto es exactamente igual a "Encontrado" -->
    <?php if ($objeto['estado'] === 'Encontrado'): ?>

        <!-- Salto de línea estructural -->
        <br>

        <!-- Enlace que redirige a la vista de reclamación enviando el ID del objeto -->
        <a
            href="reclamacion.php?id=<?php echo $objeto['id']; ?>"
        >

            <!-- Botón interactivo para iniciar la reclamación -->
            <button type="button">
                Solicitar reclamación
            </button>

        </a>

    <!-- Cierre de la condición de estado "Encontrado" -->
    <?php endif; ?>


    <!-- Evalúa si el estado actual del objeto es igual a "Perdido" -->
    <?php if ($objeto['estado'] === 'Perdido'): ?>

        <!-- Línea divisoria horizontal -->
        <hr>

        <!-- Encabezado secundario para la sección de coincidencias encontradas -->
        <h2>
            Posibles coincidencias
        </h2>

        <!-- Contenedor HTML donde se cargarán dinámicamente las coincidencias vía JavaScript -->
        <div id="coincidencias">
            Buscando coincidencias...
        </div>


        <!-- Apertura del bloque de script cliente en JS -->
        <script>

            // Función asíncrona para consultar y renderizar coincidencias desde el servidor
            async function cargarCoincidencias() {

                try {

                    // Realiza la solicitud HTTP asíncrona al script PHP de coincidencias usando el ID del objeto
                    const respuesta =
                        await fetch(
                            "../backend/coincidencias.php?id=<?php echo $objeto['id']; ?>"
                        );

                    // Convierte la respuesta recibida desde el servidor en formato JSON a objeto JavaScript
                    const coincidencias =
                        await respuesta.json();


                    // Obtiene la referencia del elemento HTML contenedor de coincidencias por su ID
                    const contenedor =
                        document.getElementById("coincidencias");


                    // Limpia el contenido inicial/previo dentro del contenedor
                    contenedor.innerHTML = "";


                    // Valida que el resultado sea un arreglo y contenga al menos un elemento de coincidencia
                    if (
                        !Array.isArray(coincidencias) ||
                        coincidencias.length === 0
                    ) {

                        // Inserta un mensaje indicando la ausencia de coincidencias si el arreglo está vacío
                        contenedor.innerHTML =
                            "<p>No se encontraron coincidencias.</p>";

                        // Cancela la ejecución posterior de la función
                        return;
                    }


                    // Recorre cada uno de los objetos devueltos en la lista de coincidencias
                    coincidencias.forEach(objetoEncontrado => {

                        // Crea dinámicamente un nuevo elemento contenedor <div>
                        const div =
                            document.createElement("div");


                        // Define la plantilla HTML con la información estructurada de la coincidencia
                        div.innerHTML = `

                            <h3>
                                ${objetoEncontrado.nombre}
                            </h3>

                            <p>
                                Color:
                                ${objetoEncontrado.color || "No especificado"}
                            </p>

                            <p>
                                Coincidencia:
                                ${objetoEncontrado.puntos}%
                            </p>

                            <a
                                href="objeto.php?id=${objetoEncontrado.id}&perdido_id=<?php echo $objeto['id']; ?>"
                            >
                                Ver objeto encontrado
                            </a>

                            <hr>

                        `;


                        // Inserta el elemento <div> configurado dentro del contenedor de coincidencias en el DOM
                        contenedor.appendChild(div);

                    });


                } catch (error) {

                    // Muestra en la consola cualquier error generado durante la ejecución del bloque try
                    console.error(
                        "Error al cargar coincidencias:",
                        error
                    );

                }

            }


            // Invoca la función para iniciar la carga automática de coincidencias
            cargarCoincidencias();

        </script>

    <!-- Cierre de la condición de estado "Perdido" -->
    <?php endif; ?>


<!-- Cierre del cuerpo del documento HTML -->
</body>

<!-- Cierre del elemento raíz HTML -->
</html>