
// Espera a que la estructura del DOM esté totalmente cargada e interpretada por el navegador
// antes de ejecutar las funciones de filtrado, animación y asignación de eventos.
document.addEventListener("DOMContentLoaded", () => {

    // ========================================================
    // OBTENCIÓN DE ELEMENTOS DEL DOM
    // ========================================================

    // Referencia al campo de texto del buscador principal.
    const buscador =
        document.getElementById("buscar");

    // Referencia al menú desplegable para filtrar por el estado del objeto.
    const filtroEstado =
        document.getElementById("filtro-estado");

    // Referencia al botón encagado de restablecer los filtros a su estado inicial.
    const botonLimpiar =
        document.getElementById("limpiar-filtros");

    // Colección de todas las tarjetas de objetos presentes en la vista.
    const tarjetas =
        document.querySelectorAll(".tarjeta-objeto");

    // Referencia al contenedor del mensaje informativo cuando no hay coincidencias.
    const mensaje =
        document.getElementById(
            "mensaje-sin-resultados"
        );


    // ========================================================
    // FUNCIÓN PRINCIPAL DE FILTRADO
    // ========================================================

    /**
     * Evalúa los criterios ingresados (texto y estado) sobre los atributos data-*
     * de cada tarjeta para determinar su visibilidad en el DOM.
     */
    function filtrarObjetos() {

        // Obtiene el texto ingresado en el buscador, convirtiéndolo a minúsculas y eliminando espacios en los extremos.
        const texto =
            buscador.value
                .toLowerCase()
                .trim();

        // Recupera la opción de estado seleccionada en el menú desplegable.
        const estadoSeleccionado =
            filtroEstado.value;

        // Contador para llevar el control de tarjetas que cumplen con los criterios de búsqueda.
        let visibles = 0;


        // Recorre cada tarjeta para verificar si coincide con los parámetros definidos.
        tarjetas.forEach(tarjeta => {

            // Extrae los metadatos almacenados en los atributos data-* de la tarjeta.
            const nombre =
                tarjeta.dataset.nombre || "";

            const color =
                tarjeta.dataset.color || "";

            const descripcion =
                tarjeta.dataset.descripcion || "";

            const estado =
                tarjeta.dataset.estado || "";


            // Comprueba si el texto ingresado coincide con el nombre, color o descripción.
            const coincideTexto =
                nombre.includes(texto) ||
                color.includes(texto) ||
                descripcion.includes(texto);


            // Comprueba si el estado seleccionado coincide o si el filtro de estado está en opción general ("").
            const coincideEstado =
                estadoSeleccionado === "" ||
                estado === estadoSeleccionado;


            // Muestra u oculta la tarjeta dependiendo de la combinación de ambos criterios.
            if (
                coincideTexto &&
                coincideEstado
            ) {

                // Muestra la tarjeta en la rejilla.
                tarjeta.style.display =
                    "block";

                // Incrementa el contador de elementos visibles.
                visibles++;

            } else {

                // Oculta la tarjeta si no cumple los requisitos.
                tarjeta.style.display =
                    "none";

            }

        });


        // Controla la visibilidad del mensaje de "Sin resultados" en función del conteo de elementos visibles.
        if (mensaje) {

            if (visibles === 0) {

                // Hace visible el mensaje de aviso cuando no hay ninguna tarjeta disponible.
                mensaje.style.display =
                    "block";

            } else {

                // Oculta el mensaje si al menos una tarjeta coincide con los filtros.
                mensaje.style.display =
                    "none";

            }

        }

    }


    // ========================================================
    // ASIGNACIÓN DE EVENTOS AL BUSCADOR
    // ========================================================

    // Valida la existencia del input del buscador y le asigna el evento de entrada en tiempo real.
    if (buscador) {

        buscador.addEventListener(
            "input",
            filtrarObjetos
        );

    }


    // ========================================================
    // ASIGNACIÓN DE EVENTOS AL FILTRO DE ESTADO
    // ========================================================

    // Valida la existencia del selector de estado y le asigna el evento de cambio de opción.
    if (filtroEstado) {

        filtroEstado.addEventListener(
            "change",
            filtrarObjetos
        );

    }


    // ========================================================
    // ASIGNACIÓN DE EVENTOS AL BOTÓN LIMPIAR FILTROS
    // ========================================================

    // Valida la existencia del botón de limpieza para restablecer los valores y actualizar el listado.
    if (botonLimpiar) {

        botonLimpiar.addEventListener(
            "click",
            () => {

                // Vacía la caja de texto de búsqueda.
                buscador.value = "";

                // Restablece el menú de estados a la opción por defecto.
                filtroEstado.value = "";

                // Ejecuta la función de filtrado para mostrar nuevamente todas las tarjetas.
                filtrarObjetos();

            }
        );

    }


    // ========================================================
    // ANIMACIÓN DE ENTRADA INICIAL EN CASCADA
    // ========================================================

    // Recorre cada tarjeta para ocultarla y desplazarla levemente, revelándola de forma escalonada.
    tarjetas.forEach(
        (tarjeta, indice) => {

            // Establece la opacidad inicial en transparente.
            tarjeta.style.opacity = "0";

            // Desplaza la tarjeta 10px hacia abajo respecto a su posición final.
            tarjeta.style.transform =
                "translateY(10px)";


            // Configura un temporizador para aplicar la animación de forma secuencial según el índice.
            setTimeout(() => {

                // Asigna la propiedad de transición suave para la opacidad y el movimiento vertical.
                tarjeta.style.transition =
                    "opacity 0.4s ease, transform 0.4s ease";

                // Revela la tarjeta ajustando la opacidad a 1.
                tarjeta.style.opacity = "1";

                // Restablece la posición vertical original de la tarjeta.
                tarjeta.style.transform =
                    "translateY(0)";

            }, indice * 70);

        }
    );

});