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

// Suspender / reactivar: mismo formulario con distinta acción. Nada se borra de la base de datos.
function configurarSuspension(formId, divId, accion) {
    const formulario = document.getElementById(formId);
    const salida = document.getElementById(divId);

    formulario.addEventListener("submit", (x) => {
        x.preventDefault();

        if (!validarCedula(formulario.cedula.value)) {
            salida.innerHTML = "<h3>La cédula debe tener exactamente 8 dígitos.</h3>";
            return;
        }

        let form = new FormData(formulario);
        form.append("accion", accion);
        form.append("csrf_token", csrfToken);

        fetch("../PHP/admin.php", { method: "post", body: form })
            .then(res => res.json())
            .then(datos => {
                salida.innerHTML = "<h3>" + escapeHtml(datos.mensaje || (datos.exito ? "Listo" : "No se pudo completar")) + "</h3>";
            })
            .catch(() => { salida.innerHTML = "<h3>Error de conexión</h3>"; });
    });
}

configurarSuspension("from_suspender", "div", "suspender_usuario");
configurarSuspension("from_reactivar", "div_reactivar", "reactivar_usuario");

function etiquetaEstado(activo) {
    return Number(activo) === 1 ? "Activo" : "Suspendido";
}

function configurarSuspensionEmprendimiento(formId, divId, accion) {
    const formulario = document.getElementById(formId);
    const salida = document.getElementById(divId);

    formulario.addEventListener("submit", (x) => {
        x.preventDefault();

        let form = new FormData(formulario);
        form.append("accion", accion);
        form.append("csrf_token", csrfToken);

        fetch("../PHP/admin.php", { method: "post", body: form })
            .then(res => res.json())
            .then(datos => {
                salida.innerHTML = "<h3>" + escapeHtml(datos.mensaje || (datos.exito ? "Listo" : "No se pudo completar")) + "</h3>";
            })
            .catch(() => { salida.innerHTML = "<h3>Error de conexión</h3>"; });
    });
}

configurarSuspensionEmprendimiento("from_suspender_emp", "div1", "suspender_emprendimiento");
configurarSuspensionEmprendimiento("from_reactivar_emp", "div_reactivar_emp", "reactivar_emprendimiento");

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
                    <p><strong>Gmail:</strong> ${a.Correo}</p>
                    <p><strong>Telefono:</strong> ${a.Num_Telefono}</p>
                    <p><strong>Domicilio:</strong> ${escapeHtml(a.Domicilio)}</p>
                    <p><strong>Calle:</strong> ${escapeHtml(a.Calle)}</p>
                    <p><strong>Manzana:</strong> ${a.Manzana}</p>
                    <p><strong>Solar:</strong> ${a.Solar}</p>
                    <p><strong>Genero:</strong> ${a.Genero}</p>
                    <p><strong>Rol:</strong> ${a.Rol}</p>
                    <p><strong>Estado:</strong> ${etiquetaEstado(a.Activo)}</p>
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

        if (datos.Nombre) {``

            div_buscar.innerHTML = `
 <p><strong>Cedula:</strong> ${escapeHtml(datos.Cedula)}</p>
                    <p><strong>Nombre:</strong> ${escapeHtml(datos.Nombre)}</p>
                    <p><strong>Apellido:</strong> ${escapeHtml(datos.Apellido)}</p>
                    <p><strong>Fecha de nacimiento:</strong> ${datos.Fecha_Nacimiento}</p>
                    <p><strong>Edad:</strong> ${datos.Edad}</p>
                    <p><strong>Gmail:</strong> ${datos.Correo}</p>
                    <p><strong>Telefono:</strong> ${datos.Num_Telefono}</p>
                    <p><strong>Domicilio:</strong> ${escapeHtml(datos.Domicilio)}</p>
                    <p><strong>Calle:</strong> ${escapeHtml(datos.Calle)}</p>
                    <p><strong>Manzana:</strong> ${datos.Manzana}</p>
                    <p><strong>Solar:</strong> ${datos.Solar}</p>
                    <p><strong>Genero:</strong> ${datos.Genero}</p>
                    <p><strong>Rol:</strong> ${datos.Rol}</p>
                    <p><strong>Estado:</strong> ${etiquetaEstado(datos.Activo)}</p>
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
                <p>Estado: ${etiquetaEstado(datos.Activo)}</p>
            `;

        } else {

            div_buscar1.innerHTML = "<h3>No encontrado</h3>";

        }
    })
    .catch(error => {
        console.error("Error:", error);
    })});
    