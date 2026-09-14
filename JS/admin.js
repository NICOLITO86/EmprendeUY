let csrfToken = null;

// SEC-04: evita insertar texto de usuario tal cual en innerHTML.
function escapeHtml(texto) {
    const div = document.createElement("div");
    div.textContent = texto ?? "";
    return div.innerHTML;
}

function obtenerCsrfToken() {
    return fetch("../PHP/csrf_token.php")
        .then(res => res.json())
        .then(datos => { csrfToken = datos.token; });
}

document.addEventListener("DOMContentLoaded", () => {

    fetch("../PHP/auth.php")
        .then(res => res.json())
        .then(datos => {
            if (!datos.autenticado) {
                window.location.href = "iniciar_sesion.html";
                return;
            }
            if (datos.rol !== "administrador") {
                window.location.href = "acceso-denegado.html";
                return;
            }
            // Acceso permitido, se puede mostrar el panel
            document.body.style.display = "block";
            obtenerCsrfToken();
        })
        .catch(error => {
            console.error("Error al verificar acceso:", error);
            window.location.href = "iniciar_sesion.html";
        });

});

const div = document.getElementById("div")
const from_borrar = document.getElementById("from_borrar");
from_borrar.addEventListener("submit", (x) => {
    x.preventDefault()

    if (!validarCedula(from_borrar.cedula.value)) {
        div.innerHTML = "<h3>La cédula debe tener exactamente 8 dígitos.</h3>";
        return;
    }

    let form = new FormData(from_borrar)
    form.append("accion", "borrar_usuario")
    form.append("csrf_token", csrfToken)

    fetch("../PHP/admin.php", {
        method: "post",
        body: form
    })
    .then(res => res.json())
    .then(datos => {
        console.log(datos)
        if (datos.exito) {
            div.innerHTML = '<h3> Borrado</h3>'
        } else {
            div.innerHTML = '<h3> No se pudo borrar</h3>'
        }
    })
})

const div1 = document.getElementById("div1")
const from_borrar1 = document.getElementById("from_borrar1");
from_borrar1.addEventListener("submit", (x) => {
    x.preventDefault()

    let form = new FormData(from_borrar1)
    form.append("accion", "borrar_emprendimiento")
    form.append("csrf_token", csrfToken)

    fetch("../PHP/admin.php", {
        method: "post",
        body: form
    })
    .then(res => res.json())
    .then(datos => {
        console.log(datos)
        if (datos.exito) {
            div1.innerHTML = '<h3> Borrado</h3>'
        } else {
            div1.innerHTML = '<h3> No se pudo borrar</h3>'
        }
    })
})

const from_buscar2 = document.getElementById("from_buscar2");

from_buscar2.addEventListener("submit", (e) => {
    const lista = document.getElementById("lista");
    e.preventDefault();

    let form = new FormData(from_buscar2);
    form.append("accion", "mostrar_todo")
    form.append("csrf_token", csrfToken)

    fetch("../PHP/admin.php", {
        method: "POST",
        body: form
    })
    .then(res => res.json())
    .then(datos => {

        console.log(datos);

        lista.innerHTML = "";

        datos.forEach(a => {

            lista.innerHTML += `
                <div>
                    <p><strong>Cedula:</strong> ${escapeHtml(a.Cedula)}</p>
                    <p><strong>Nombre:</strong> ${escapeHtml(a.Nombre)}</p>
                    <p><strong>Apellido:</strong> ${escapeHtml(a.Apellido)}</p>
                    <p><strong>Fecha de nacimiento:</strong> ${a.Fecha_Nacimiento}</p>
                    <p><strong>Edad:</strong> ${a.Edad}</p>
                    <p><strong>Gmail:</strong> ${a.Gmail}</p>
                    <p><strong>Telefono:</strong> ${a.Num_Telefono}</p>
                    <p><strong>Domicilio:</strong> ${escapeHtml(a.Domicilio)}</p>
                    <p><strong>Calle:</strong> ${escapeHtml(a.Calle)}</p>
                    <p><strong>Manzana:</strong> ${a.Manzana}</p>
                    <p><strong>Solar:</strong> ${a.Solar}</p>
                    <p><strong>Genero:</strong> ${a.Genero}</p>
                    <p><strong>Rol:</strong> ${a.Rol}</p>
                    <hr>
                </div>
            `;

        });

    })

});

const from_buscar = document.getElementById("from_buscar");
const div_buscar = document.getElementById("div_buscar");

from_buscar.addEventListener("submit", (e) => {

    e.preventDefault();

    if (!validarCedula(from_buscar.cedula.value)) {
        div_buscar.innerHTML = "<h3>La cédula debe tener exactamente 8 dígitos.</h3>";
        return;
    }

    let form = new FormData(from_buscar);
    form.append("accion", "buscar_usuario")
    form.append("csrf_token", csrfToken)

    fetch("../PHP/admin.php", {
        method: "post",
        body: form
    })
    .then(res => res.json())
    .then(datos => {

        console.log(datos);

        if (datos.Nombre) {

            div_buscar.innerHTML = `
                <h3>${escapeHtml(datos.Nombre)} ${escapeHtml(datos.Apellido)}</h3>
                <p>Cedula: ${datos.Cedula}</p>
            `;

        } else {

            div_buscar.innerHTML = "<h3>No encontrado</h3>";

        }

    })});

const from_buscar1 = document.getElementById("from_buscar1");
const div_buscar1 = document.getElementById("div_buscar1");

from_buscar1.addEventListener("submit", (e) => {

    e.preventDefault();

    let form = new FormData(from_buscar1);
    form.append("accion", "buscar_emprendimiento")
    form.append("csrf_token", csrfToken)

    console.log("ID enviado:", form.get("ID"));

    fetch("../PHP/admin.php", {
        method: "POST",
        body: form
    })
    .then(res => res.json())
    .then(datos => {

        console.log("JSON recibido:", datos);

        if (datos.ID) {

            div_buscar1.innerHTML = `
                <h3>${escapeHtml(datos.Nombre)}</h3>
                <p>ID: ${datos.ID}</p>
                <p>Descripción: ${escapeHtml(datos.Descripcion)}</p>
            `;

        } else {

            div_buscar1.innerHTML = "<h3>No encontrado</h3>";

        }
    })
    .catch(error => {
        console.error("Error:", error);
    })});