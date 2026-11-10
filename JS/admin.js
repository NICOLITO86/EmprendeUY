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
            .then(res => res.text().then(texto => {
                try { return JSON.parse(texto); }
                catch (e) { return { exito: false, mensaje: "Respuesta inesperada del servidor: " + texto.replace(/<[^>]*>/g, " ").trim().slice(0, 300) }; }
            }))
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

// Botón Activar / Suspender para cada usuario (en la búsqueda y en "Mostrar todo").
// Los administradores no se pueden suspender, así que no llevan botón.
function botonEstado(usuario) {
    if (usuario.Rol === "administrador") return "";
    const activo = Number(usuario.Activo) === 1;
    return `<button type="button" class="accion ${activo ? "accion--peligro" : ""} btn-estado"
                data-cedula="${escapeHtml(usuario.Cedula)}"
                data-accion="${activo ? "suspender_usuario" : "reactivar_usuario"}">
                ${activo ? "Suspender" : "Activar"} usuario</button>`;
}

document.addEventListener("click", (e) => {
    const boton = e.target.closest(".btn-estado");
    if (!boton) return;

    boton.disabled = true;
    let form = new FormData();
    form.append("cedula", boton.dataset.cedula);
    form.append("accion", boton.dataset.accion);
    form.append("csrf_token", csrfToken);

    // Después de cambiar el estado se vuelve a cargar la búsqueda o el listado donde estaba el botón.
    const recargar = boton.closest("#lista") ? from_buscar2 : from_buscar;

    fetch("../PHP/admin.php", { method: "post", body: form })
        .then(res => res.json())
        .then(datos => {
            if (!datos.exito) {
                alert(datos.mensaje || "No se pudo completar la operación.");
                boton.disabled = false;
                return;
            }
            recargar.dispatchEvent(new Event("submit", { cancelable: true }));
        })
        .catch(() => { alert("Error de conexión"); boton.disabled = false; });
});

function configurarSuspensionEmprendimiento(formId, divId, accion) {
    const formulario = document.getElementById(formId);
    const salida = document.getElementById(divId);

    formulario.addEventListener("submit", (x) => {
        x.preventDefault();

        let form = new FormData(formulario);
        form.append("accion", accion);
        form.append("csrf_token", csrfToken);

        fetch("../PHP/admin.php", { method: "post", body: form })
            .then(res => res.text().then(texto => {
                try { return JSON.parse(texto); }
                catch (e) { return { exito: false, mensaje: "Respuesta inesperada del servidor: " + texto.replace(/<[^>]*>/g, " ").trim().slice(0, 300) }; }
            }))
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
                    <p><strong>Genero:</strong> ${a.Genero}</p>
                    <p><strong>Rol:</strong> ${a.Rol}</p>
                    <p><strong>Estado:</strong> ${etiquetaEstado(a.Activo)}</p>
                    ${botonEstado(a)}
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
                    <p><strong>Genero:</strong> ${datos.Genero}</p>
                    <p><strong>Rol:</strong> ${datos.Rol}</p>
                    <p><strong>Estado:</strong> ${etiquetaEstado(datos.Activo)}</p>
                    ${botonEstado(datos)}
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
    