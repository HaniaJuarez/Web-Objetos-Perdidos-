
// Espera a que el documento HTML esté completamente cargado e interpretado antes de ejecutar el código script
document.addEventListener("DOMContentLoaded", () => {

    /*
    |--------------------------------------------------------------------------
    | SELECCIÓN DE ELEMENTOS DEL DOM
    |--------------------------------------------------------------------------
    | Obtiene las referencias necesarias a los controles de interfaz, la lista
    | de tarjetas y el contador de resultados.
    |
    */

    // Referencia al campo de búsqueda por texto
    const buscador =
        document.getElementById("buscar");

    // Referencia al selector para filtrar por estado
    const filtroEstado =
        document.getElementById("filtro-estado");

    // Referencia al botón para reiniciar o restablecer los filtros
    const botonLimpiar =
        document.getElementById("limpiar-filtros");

    // Obtiene el conjunto de todas las tarjetas de reclamaciones presentes en la vista
    const tarjetas =
        document.querySelectorAll(".tarjeta-reclamacion");

    // Elemento dinámico para actualizar el conteo de elementos visibles
    const contador =
        document.getElementById("contador-resultados");


    /*
    |--------------------------------------------------------------------------
    | FUNCIÓN DE FILTRADO
    |--------------------------------------------------------------------------
    | Evalúa las coincidencias de texto y estado en cada tarjeta para ocultar
    | o mostrar elementos en el DOM y actualizar la métrica visible.
    |
    */

    function filtrarReclamaciones() {

        // Normaliza el texto ingresado en el buscador (minúsculas y sin espacios adicionales)
        const texto =
            buscador.value
                .toLowerCase()
                .trim();

        // Obtiene el estado seleccionado actualmente en el control select
        const estadoSeleccionado =
            filtroEstado.value;

        // Inicializa el contador de tarjetas visibles que cumplen con los filtros
        let visibles = 0;


        // Recorre cada una de las tarjetas para comprobar si cumple las condiciones de filtrado
        tarjetas.forEach(tarjeta => {

            // Extrae los valores guardados en los atributos de datos personalizados (data-*)
            const contenido =
                tarjeta.dataset.busqueda || "";

            const estado =
                tarjeta.dataset.estado || "";


            // Comprueba si el dataset de búsqueda contiene el texto ingresado
            const coincideTexto =
                contenido.includes(texto);

            // Comprueba si el estado coincide o si no hay filtro activo de estado (opción 'Todos')
            const coincideEstado =
                estadoSeleccionado === "" ||
                estado === estadoSeleccionado;


            // Muestra u oculta la tarjeta según cumpla o no ambas condiciones
            if (
                coincideTexto &&
                coincideEstado
            ) {

                // Muestra la tarjeta en pantalla
                tarjeta.style.display = "block";

                // Incrementa el acumulador de elementos visibles
                visibles++;

            } else {

                // Oculta la tarjeta que no coincide con los parámetros
                tarjeta.style.display = "none";

            }

        });


        // Actualiza el texto descriptivo del contador contemplando el número plural o singular
        contador.textContent =
            visibles +
            (
                visibles === 1
                    ? " reclamación"
                    : " reclamaciones"
            );
    }


    /*
    |--------------------------------------------------------------------------
    | ASIGNACIÓN DE EVENTOS DE CONTROL
    |--------------------------------------------------------------------------
    | Asocia las funciones a los eventos de entrada de usuario sobre los filtros.
    |
    */

    // Ejecuta la función de filtrado al escribir dentro de la caja de texto
    buscador.addEventListener(
        "input",
        filtrarReclamaciones
    );


    // Ejecuta la función de filtrado al cambiar la opción del selector de estado
    filtroEstado.addEventListener(
        "change",
        filtrarReclamaciones
    );


    // Restablece los campos de filtro a sus valores predeterminados y refresca el listado
    botonLimpiar.addEventListener(
        "click",
        () => {

            // Limpia el input de texto
            buscador.value = "";

            // Restablece el menú desplegable a la opción inicial
            filtroEstado.value = "";

            // Vuelve a filtrar para visualizar nuevamente todas las tarjetas
            filtrarReclamaciones();

        }
    );


    /*
    |--------------------------------------------------------------------------
    | ANIMACIÓN INICIAL DE CARGA
    |--------------------------------------------------------------------------
    | Aplica un efecto visual suave de desvanecimiento y movimiento vertical
    | escalonado a cada tarjeta al cargar la página.
    |
    */

    tarjetas.forEach((tarjeta, indice) => {

        // Oculta inicialmente la tarjeta estableciendo su opacidad en 0
        tarjeta.style.opacity = "0";

        // Desplaza la tarjeta 10px hacia abajo desde su posición natural
        tarjeta.style.transform =
            "translateY(10px)";


        // Aplica un temporizador desfasado para generar el efecto en cascada
        setTimeout(() => {

            // Configura la transición de CSS para suavizar la animación
            tarjeta.style.transition =
                "opacity 0.4s ease, transform 0.4s ease";

            // Restablece la opacidad a visible
            tarjeta.style.opacity = "1";

            // Regresa la tarjeta a su posición vertical original
            tarjeta.style.transform =
                "translateY(0)";

        }, indice * 80);

    });

});