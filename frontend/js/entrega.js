document.addEventListener("DOMContentLoaded", () => {

    const formulario =
        document.getElementById("form-entrega");


    if (!formulario) {
        return;
    }


    formulario.addEventListener("submit", (evento) => {

        const confirmar = confirm(
            "¿Confirmas que el objeto fue entregado correctamente al propietario?"
        );


        if (!confirmar) {

            evento.preventDefault();

        }

    });

});