// Espera a que el documento HTML esté completamente cargado e interpretado
// antes de ejecutar cualquier script o interacción con el DOM.
document.addEventListener("DOMContentLoaded", () => {

    // ========================================================
    // ANIMACIÓN DE LAS TARJETAS (EFECTO FADE-IN Y DESPLAZAMIENTO)
    // ========================================================

    // Selecciona todos los elementos que contengan la clase ".tarjeta" en el DOM.
    const tarjetas =
        document.querySelectorAll(".tarjeta");

    // Recorre cada tarjeta para aplicar un estado inicial transparente y desplazado,
    // animándolas secuencialmente según su índice.
    tarjetas.forEach((tarjeta, indice) => {

        // Oculta la tarjeta haciendo su opacidad totalmente transparente.
        tarjeta.style.opacity = "0";

        // Desplaza la tarjeta 10px hacia abajo desde su posición original.
        tarjeta.style.transform =
            "translateY(10px)";

        // Programa la animación de entrada con un retraso escalonado (80 ms por cada tarjeta).
        setTimeout(() => {

            // Asigna las propiedades de transición suave para la opacidad y la posición vertical.
            tarjeta.style.transition =
                "opacity 0.4s ease, transform 0.4s ease";

            // Revela la tarjeta restaurando la opacidad a 1.
            tarjeta.style.opacity = "1";

            // Devuelve la tarjeta a su posición vertical original (0px).
            tarjeta.style.transform =
                "translateY(0)";

        }, indice * 80);

    });


    // ========================================================
    // BOTÓN ACTUALIZAR (RECARGA MÁGINA / REFRESCAR DATOS)
    // ========================================================

    // Busca en el DOM el botón mediante su identificador único "boton-actualizar".
    const botonActualizar =
        document.getElementById(
            "boton-actualizar"
        );

    // Valida que el botón exista en la vista actual antes de asociar el evento.
    if (botonActualizar) {

        // Escucha el evento de clic del usuario sobre el botón.
        botonActualizar.addEventListener(
            "click",
            () => {

                // Cambia el texto del botón para retroalimentar la acción en curso.
                botonActualizar.textContent =
                    "Actualizando...";

                // Deshabilita el botón para evitar clics dobles o peticiones repetidas.
                botonActualizar.disabled =
                    true;

                // Espera 500 ms antes de recargar la página para mostrar el estado de carga.
                setTimeout(() => {

                    // Recarga la página actual del navegador.
                    location.reload();

                }, 500);

            }
        );

    }


    // ========================================================
    // ANIMACIÓN CONTAGIOSA / CONTADOR PROGRESIVO DE NÚMEROS
    // ========================================================

    // Selecciona todos los elementos indicadores numéricos etiquetados con la clase ".numero".
    const numeros =
        document.querySelectorAll(".numero");

    // Procesa cada elemento numérico para animar su conteo desde 0 hasta el valor final.
    numeros.forEach(numero => {

        // Si existe data-valor, utiliza ese valor.
        // Si no existe, recupera el número que PHP ya imprimió.
        const textoInicial = numero.textContent.trim();

        const valorAtributo = numero.getAttribute("data-valor");

        const valorFinal = valorAtributo !== null
            ? parseInt(valorAtributo, 10)
            : parseInt(textoInicial, 10);

        // Si no se encuentra un número válido, no modifica el elemento.
        if (Number.isNaN(valorFinal)) {
            return;
        }

        // Inicializa el contador del valor progresivo en 0.
        let valorActual = 0;

        // Calcula el paso de incremento garantizando que sea al menos 1 para no congelar la iteración.
        const incremento =
            Math.max(
                1,
                Math.ceil(
                    valorFinal / 20
                )
            );
        
        // Si el valor final es cero, lo muestra inmediatamente.
        if (valorFinal === 0) {
            numero.textContent = "0";
            return;
        }

        // Define el intervalo de tiempo repetitivo (30 ms) para simular el conteo animado.
        const intervalo = setInterval(() => {

            valorActual += incremento;

            if (valorActual >= valorFinal) {
                valorActual = valorFinal;
                clearInterval(intervalo);
            }

            numero.textContent = valorActual;

        }, 30);

    });

});