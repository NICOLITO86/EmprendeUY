const contenedor = document.getElementById("contenedor");

function cargarPerfil() { 

    fetch("../PHP/mostrar_perfil.php")
    .then(res => res.json())
    .then(datos => {

        if (!datos.exito) {
            contenedor.innerHTML = `<p>${datos.cargarPerfil}</p>`;
            return;
        }