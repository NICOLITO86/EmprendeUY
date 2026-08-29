const contenedor = document.getElementById("publicaciones");

function cargarMisPublicaciones() {

    fetch("../PHP/mostrar_mis_publicaciones.php")
    .then(res => res.json())
    .then(datos => {

        if (!datos.exito) {
            contenedor.innerHTML = `<p>${datos.mensaje}</p>`;
            return;
        }

        contenedor.innerHTML = "";

        if (datos.publicaciones.length === 0) {
            contenedor.innerHTML = "<p>Todavía no tenés publicaciones. Creá la primera desde \"+ Nueva publicación\".</p>";
            return;
        }

        datos.publicaciones.forEach(p => {

            const bloqueada = p.estado === "Bloqueada";

            // Si esta bloqueada por el admin, no le damos selector: solo un aviso.
            // Si no, le damos un <select> para alternar entre Activa y Pausada.
            const controlEstado = bloqueada
                ? `<p class="aviso-bloqueo">Bloqueada por un administrador</p>`
                : `<select class="selector-estado" data-id="${p.id}">
                       <option value="Activa" ${p.estado === "Activa" ? "selected" : ""}>Activa</option>
                       <option value="Pausada" ${p.estado === "Pausada" ? "selected" : ""}>Pausada</option>
                   </select>`;

            contenedor.innerHTML += `
                <div class="publicacion">
                    <img src="../PHP/mostrar_imagen.php?id=${p.id}">
                    <h3>${p.titulo}</h3>
                    <p>${p.descripcion}</p>
                    <p><strong>$${p.precio}</strong> · ${p.categoria}</p>
                    <div class="estado estado-${p.estado.toLowerCase()}">${p.estado}</div>
                    ${controlEstado}
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

function cambiarEstado(evento) {

    const select = evento.target;
    const id = select.dataset.id;
    const nuevoEstado = select.value;

    fetch("../PHP/cambiar_estado.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `id=${id}&estado=${encodeURIComponent(nuevoEstado)}`
    })
    .then(res => res.json())
    .then(datos => {

        if (!datos.exito) {
            alert(datos.mensaje);
            cargarMisPublicaciones(); // recarga para volver el select a lo que hay en la base
        }

    });

}

cargarMisPublicaciones();