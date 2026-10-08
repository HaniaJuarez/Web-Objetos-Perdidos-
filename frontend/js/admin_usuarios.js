
// Espera a que la estructura del DOM esté totalmente cargada e interpretada por el navegador
// antes de ejecutar las funciones de filtrado, animación y asignación de eventos.
document.addEventListener("DOMContentLoaded", () => {

    // ========================================================
    // OBTENCIÓN DE ELEMENTOS DEL DOM
    // ========================================================

    // Referencia al campo de texto del buscador de usuarios.
    const buscador =
        document.getElementById("buscar");

    // Referencia al menú desplegable para filtrar por rol de usuario.
    const filtroRol =
        document.getElementById("filtro-rol");

    // Referencia al botón encargado de restablecer los filtros a su estado inicial.
    const botonLimpiar =
        document.getElementById("limpiar-filtros");

    // Colección de todas las filas de la tabla de usuarios dentro del tbody.
    const filas =
        document.querySelectorAll(
            "#tabla-usuarios tbody tr"
        );

    // Referencia al contenedor del mensaje informativo cuando no hay coincidencias.
    const mensaje =
        document.getElementById(
            "mensaje-sin-resultados"
        );


    // ========================================================
    // FUNCIÓN PRINCIPAL DE FILTRADO DE USUARIOS
    // ========================================================

    /**
     * Evalúa los criterios ingresados (texto y rol) sobre los atributos data-*
     * de cada fila de la tabla para determinar su visibilidad en el DOM.
     */
    function filtrarUsuarios() {

        // Obtiene el texto ingresado en el buscador, convirtiéndolo a minúsculas y eliminando espacios en los extremos.
        const texto =
            buscador.value
                .toLowerCase()
                .trim();

        // Recupera la opción de rol seleccionada en el menú desplegable.
        const rolSeleccionado =
            filtroRol.value;

        // Contador para llevar el control de filas que cumplen con los criterios de búsqueda.
        let visibles = 0;


        // Recorre cada fila de la tabla para verificar si coincide con los parámetros definidos.
        filas.forEach(fila => {

            // Extrae los metadatos almacenados en los atributos data-* de la fila.
            const nombre =
                fila.dataset.nombre || "";

            const correo =
                fila.dataset.correo || "";

            const rol =
                fila.dataset.rol || "";


            // Comprueba si el texto ingresado coincide con el nombre o el correo del usuario.
            const coincideTexto =
                nombre.includes(texto) ||
                correo.includes(texto);


            // Comprueba si el rol seleccionado coincide o si el filtro está en la opción por defecto ("").
            const coincideRol =
                rolSeleccionado === "" ||
                rol === rolSeleccionado;


            // Muestra u oculta la fila dependiendo de la combinación de ambos criterios.
            if (
                coincideTexto &&
                coincideRol
            ) {

                // Restablece la visualización por defecto de la fila de la tabla.
                fila.style.display =
                    "";

                // Incrementa el contador de elementos visibles.
                visibles++;

            } else {

                // Oculta la fila si no cumple los requisitos de filtrado.
                fila.style.display =
                    "none";

            }

        });


        // ====================================================
        // MOSTRAR U OCULTAR MENSAJE SIN RESULTADOS
        // ====================================================

        // Controla la visibilidad del mensaje informativo en función del conteo de filas visibles.
        if (mensaje) {

            if (visibles === 0) {

                // Muestra el mensaje de aviso cuando no hay ningún usuario que coincida.
                mensaje.style.display =
                    "block";

            } else {

                // Oculta el mensaje si al menos una fila coincide con los filtros.
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
            filtrarUsuarios
        );

    }


    // ========================================================
    // ASIGNACIÓN DE EVENTOS AL FILTRO POR ROL
    // ========================================================

    // Valida la existencia del selector por rol y le asigna el evento de cambio de opción.
    if (filtroRol) {

        filtroRol.addEventListener(
            "change",
            filtrarUsuarios
        );

    }


    // ========================================================
    // ASIGNACIÓN DE EVENTOS AL BOTÓN LIMPIAR FILTROS
    // ========================================================

    // Valida la existencia del botón de limpieza para restablecer los valores y actualizar la lista.
    if (botonLimpiar) {

        botonLimpiar.addEventListener(
            "click",
            () => {

                // Vacía la caja de texto de búsqueda.
                buscador.value = "";

                // Restablece el menú de roles a la opción por defecto.
                filtroRol.value = "";

                // Ejecuta la función de filtrado para mostrar nuevamente todas las filas.
                filtrarUsuarios();

            }
        );

    }


    // ========================================================
    // ANIMACIÓN DE ENTRADA INICIAL EN CASCADA
    // ========================================================

    // Recorre cada fila de la tabla para ocultarla inicialmente y revelarla progresivamente.
    filas.forEach((fila, indice) => {

        // Establece la opacidad inicial en transparente.
        fila.style.opacity = "0";


        // Configura un temporizador para aplicar la animación de forma secuencial según el índice.
        setTimeout(() => {

            // Asigna la propiedad de transición suave para el cambio de opacidad.
            fila.style.transition =
                "opacity 0.3s ease";

            // Hace visible la fila restaurando la opacidad a 1.
            fila.style.opacity = "1";

        }, indice * 50);

    });

});