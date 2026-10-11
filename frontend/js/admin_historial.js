
// Espera a que el documento HTML esté completamente cargado e interpretado antes de ejecutar el código script
document.addEventListener("DOMContentLoaded", () => {

    /*
    |--------------------------------------------------------------------------
    | SELECCIÓN DE ELEMENTOS DEL DOM
    |--------------------------------------------------------------------------
    | Obtiene las referencias necesarias a los controles de interfaz, la lista
    | de tarjetas de movimientos y el contador de resultados.
    |
    */

    // Referencia al campo de búsqueda de texto libre
    const buscador =
        document.getElementById("buscar");

    // Referencia al selector desplegable para filtrar por el estado nuevo del movimiento
    const filtroEstado =
        document.getElementById("filtro-estado");

    // Referencia al botón para reiniciar o limpiar los filtros activos
    const botonLimpiar =
        document.getElementById("limpiar-filtros");

    // Selecciona todas las tarjetas del historial de movimientos presentes en el DOM
    const tarjetas =
        document.querySelectorAll(".tarjeta-movimiento");

    // Elemento donde se muestra y actualiza el contador de resultados visibles
    const contador =
        document.getElementById("contador-resultados");


    /*
    |--------------------------------------------------------------------------
    | FUNCIÓN DE FILTRADO DEL HISTORIAL
    |--------------------------------------------------------------------------
    | Evalúa el término de búsqueda y el estado seleccionado en cada tarjeta
    | para determinar su visibilidad y actualizar la métrica en tiempo real.
    |
    */

    function filtrarHistorial() {

        // Normaliza el texto ingresado (convertido a minúsculas y sin espacios laterales)
        const texto =
            buscador.value
                .toLowerCase()
                .trim();

        // Obtiene el estado seleccionado actualmente en el control desplegable
        const estadoSeleccionado =
            filtroEstado.value;

        // Acumulador de tarjetas que cumplen con los criterios de filtrado
        let visibles = 0;


        // Iteración sobre cada tarjeta de movimiento
        tarjetas.forEach(tarjeta => {

            // Obtiene los valores de los atributos de datos personalizados (data-*)
            const contenido =
                tarjeta.dataset.busqueda || "";

            const estado =
                tarjeta.dataset.estado || "";


            // Verifica si el texto ingresado coincide con el contenido buscable
            const coincideTexto =
                contenido.includes(texto);

            // Verifica si el estado coincide o si se seleccionó la opción por defecto ("Todos")
            const coincideEstado =
                estadoSeleccionado === "" ||
                estado === estadoSeleccionado;


            // Muestra u oculta la tarjeta según cumpla o no ambas condiciones
            if (
                coincideTexto &&
                coincideEstado
            ) {

                // Hace visible la tarjeta en el DOM
                tarjeta.style.display = "block";

                // Incrementa el número de elementos visibles
                visibles++;

            } else {

                // Oculta la tarjeta que no cumple los filtros
                tarjeta.style.display = "none";

            }

        });


        // Actualiza la etiqueta del contador considerando la concordancia gramatical (singular/plural)
        contador.textContent =
            visibles +
            (
                visibles === 1
                    ? " movimiento"
                    : " movimientos"
            );
    }


    /*
    |--------------------------------------------------------------------------
    | ASIGNACIÓN DE EVENTOS DE INTERACCIÓN
    |--------------------------------------------------------------------------
    | Vincula los controles del formulario con la función de filtrado dinámico.
    |
    */

    // Escucha el evento de entrada en el buscador para filtrar mientras se escribe
    buscador.addEventListener(
        "input",
        filtrarHistorial
    );


    // Escucha los cambios en el selector de estados
    filtroEstado.addEventListener(
        "change",
        filtrarHistorial
    );


    // Escucha el clic en el botón de limpieza para restablecer controles y actualizar la lista
    botonLimpiar.addEventListener(
        "click",
        () => {

            // Vacía el campo de texto de búsqueda
            buscador.value = "";

            // Restablece el menú desplegable a su opción predeterminada
            filtroEstado.value = "";

            // Vuelve a filtrar para mostrar nuevamente todos los registros
            filtrarHistorial();

        }
    );


    /*
    |--------------------------------------------------------------------------
    | ANIMACIÓN INICIAL DE CARGA
    |--------------------------------------------------------------------------
    | Aplica un efecto visual de desvanecimiento y elevación escalonada a las
    | tarjetas al cargar la vista.
    |
    */

    tarjetas.forEach((tarjeta, indice) => {

        // Inicializa la tarjeta invisible
        tarjeta.style.opacity = "0";

        // Desplaza la tarjeta 10px hacia abajo desde su posición original
        tarjeta.style.transform =
            "translateY(10px)";


        // Aplica un retraso proporcional al índice para generar la animación en cascada
        setTimeout(() => {

            // Configura las propiedades de transición CSS suaves
            tarjeta.style.transition =
                "opacity 0.4s ease, transform 0.4s ease";

            // Restablece la opacidad a visible
            tarjeta.style.opacity = "1";

            // Regresa la tarjeta a su posición vertical correcta
            tarjeta.style.transform =
                "translateY(0)";

        }, indice * 80);

    });

});