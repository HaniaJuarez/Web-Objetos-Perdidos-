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

        // Lee la meta-información 'data-valor' del elemento HTML y la convierte a número entero.
        const valorFinal =
            parseInt(
                numero.dataset.valor
            ) || 0;

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

        // Define el intervalo de tiempo repetitivo (30 ms) para simular el conteo animado.
        const intervalo =
            setInterval(() => {

                // Suma el valor del incremento al contador actual en cada paso.
                valorActual += incremento;

                // Si el contador actual alcanza o supera el valor meta deseado:
                if (
                    valorActual >=
                    valorFinal
                ) {

                    // Ajusta el valor final exacto para no sobrepasarlo.
                    valorActual =
                        valorFinal;

                    // Cancela el temporizador del intervalo actual.
                    clearInterval(
                        intervalo
                    );

                }

                // Actualiza el texto visible del elemento en el DOM con el número progresivo.
                numero.textContent =
                    valorActual;

            }, 30);

        // Caso especial: Si la métrica es igual a cero, asegura el valor inmediato sin esperar intervalo.
        if (valorFinal === 0) {

            numero.textContent = "0";

        }

    });

});