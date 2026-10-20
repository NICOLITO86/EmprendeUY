const contenedor = document.getElementById("publicaciones");

// Guardamos las publicaciones cargadas por id para poder rellenar el formulario de edición
let publicacionesPorId = {};

// Cambia cada vez que se edita algo, para que el navegador no muestre una imagen vieja en caché
let versionImagen = Date.now();

// SEC-04: evita insertar texto de usuario (título, descripción) tal cual en innerHTML.
function escapeHtml(texto) {
    const div = document.createElement("div");
    div.textContent = texto ?? "";
    return div.innerHTML;
}

// El backend guarda título y descripción con caracteres especiales codificados (&amp;, &#39;...).
// Para mostrarlos en un input los volvemos a su forma normal.
function decodificarHtml(texto) {
    const area = document.createElement("textarea");
    area.innerHTML = texto ?? "";
    return area.value;
}

function cargarMisPublicaciones() {

    fetch("../PHP/mostrar_mis_publicaciones.php")
    .then(res => res.json())
    .then(datos => {

        if (!datos.exito) {
            contenedor.innerHTML = `<p>${datos.mensaje}</p>`;
            return;
        }

        contenedor.innerHTML = "";
        publicacionesPorId = {};

        if (datos.publicaciones.length === 0) {
            contenedor.innerHTML = "<p>Todavía no tenés publicaciones. Creá la primera desde \"+ Nueva publicación\".</p>";
            return;
        }

        datos.publicaciones.forEach(p => {

            publicacionesPorId[p.id] = p;

            const bloqueada = p.estado === "Bloqueada";

            // Si esta bloqueada por el admin, no le damos selector ni botón de editar: solo un aviso.
            // Si no, le damos un <select> para alternar entre Activa y Pausada, y el botón Editar.
            const controles = bloqueada
                ? `<p class="aviso-bloqueo">Bloqueada por un administrador</p>`
                : `<select class="selector-estado" data-id="${p.id}">
                       <option value="Activa" ${p.estado === "Activa" ? "selected" : ""}>Activa</option>
                       <option value="Pausada" ${p.estado === "Pausada" ? "selected" : ""}>Pausada</option>
                   </select>
                   <button type="button" class="btn-editar" data-id="${p.id}">Editar</button>`;

            contenedor.innerHTML += `
                <div class="publicacion">
                    <img src="../PHP/mostrar_imagen.php?id=${p.id}&v=${versionImagen}">
                    <h3>${escapeHtml(p.titulo)}</h3>
                    <p>${escapeHtml(p.descripcion)}</p>
                    <p><strong>$${p.precio}</strong> · ${escapeHtml(p.categoria)}</p>
                    <div class="estado estado-${p.estado.toLowerCase()}">${p.estado}</div>
                    <div class="acciones">${controles}</div>
                </div>
            `;

        });

        // Recien ahora existen los <select> en el DOM, asi que
        // enganchamos el evento despues de haberlos dibujado.
        document.querySelectorAll(".selector-estado").forEach(select => {
            select.addEventListener("change", cambiarEstado);
        });

    });

}

async function cambiarEstado(evento) {

    const select = evento.target;
    const id = select.dataset.id;
    const nuevoEstado = select.value;

    const tokenRes = await fetch("../PHP/csrf_token.php");
    const tokenDatos = await tokenRes.json();

    fetch("../PHP/cambiar_estado.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `id=${id}&estado=${encodeURIComponent(nuevoEstado)}&csrf_token=${encodeURIComponent(tokenDatos.token)}`
    })
    .then(res => res.json())
    .then(datos => {

        if (!datos.exito) {
            alert(datos.mensaje);
            cargarMisPublicaciones(); // recarga para volver el select a lo que hay en la base
        }

    });

}

/* ---------- Edición de publicaciones ---------- */

const dialogoEditar = document.getElementById("dialogoEditar");
const formEditar = document.getElementById("formEditar");
const errorEditar = document.getElementById("errorEditar");
const btnGuardar = document.getElementById("btnGuardarEdicion");

// Delegación de eventos: un solo listener que sirve para todos los botones,
// incluso los que se dibujan después de recargar el catálogo.
contenedor.addEventListener("click", evento => {

    const boton = evento.target.closest(".btn-editar");
    if (!boton) return;

    const p = publicacionesPorId[boton.dataset.id];
    if (!p) return;

    document.getElementById("editId").value = p.id;
    document.getElementById("editTitulo").value = decodificarHtml(p.titulo);
    document.getElementById("editDescripcion").value = decodificarHtml(p.descripcion);
    document.getElementById("editCategoria").value = p.categoria;
    document.getElementById("editPrecio").value = p.precio;
    document.getElementById("editFoto").value = "";
    document.getElementById("editImagenActual").src = `../PHP/mostrar_imagen.php?id=${p.id}&v=${versionImagen}`;

    errorEditar.textContent = "";
    dialogoEditar.showModal();

});

document.getElementById("btnCancelarEdicion").addEventListener("click", () => {
    dialogoEditar.close();
});

formEditar.addEventListener("submit", async evento => {

    evento.preventDefault();
    errorEditar.textContent = "";
    btnGuardar.disabled = true;
    btnGuardar.textContent = "Guardando...";

    try {

        const datosForm = new FormData(formEditar);

        const tokenRes = await fetch("../PHP/csrf_token.php");
        const tokenDatos = await tokenRes.json();
        datosForm.append("csrf_token", tokenDatos.token);

        const res = await fetch("../PHP/editar_publicacion.php", {
            method: "POST",
            body: datosForm
        });
        const datos = await res.json();

        if (datos.exito) {
            versionImagen = Date.now();
            dialogoEditar.close();
            cargarMisPublicaciones();
        } else {
            errorEditar.textContent = datos.mensaje || "No se pudieron guardar los cambios.";
        }

    } catch (error) {
        errorEditar.textContent = "No se pudo conectar con el servidor. Probá de nuevo.";
    } finally {
        btnGuardar.disabled = false;
        btnGuardar.textContent = "Guardar cambios";
    }

});

cargarMisPublicaciones();
