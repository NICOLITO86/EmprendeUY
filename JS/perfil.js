// perfil.js
// Página "Mi perfil": carga los datos del usuario, guarda cambios, sube la foto
// y administra las direcciones. Se apoya en:
//   PHP/mostrar_perfil.php, PHP/actualizar_perfil.php, PHP/subir_foto_perfil.php, PHP/direcciones.php
// y en JS/validaciones.js (validarTelefono, cumpleEdadMinima).

const paginaPerfil = document.querySelector(".perfil");

const formDatos     = document.getElementById("form-datos");
const formFoto      = document.getElementById("form-foto");
const formDireccion = document.getElementById("form-direccion");
const detalleNueva  = document.querySelector(".nueva-direccion");
const listaDirec    = document.getElementById("lista-direcciones");
const imgFoto       = document.getElementById("foto-perfil");

const ETIQUETAS_ROL = {
    administrador: "Administrador",
    emprendedor: "Emprendedor",
    cliente: "Cliente",
    usuario: "Usuario",
};

let direcciones = [];   // direcciones cargadas del servidor
let editandoId  = null; // id de la dirección que se está editando (null = dirección nueva)

/* ---------- Utilidades ---------- */

// Usa SweetAlert (igual que la tienda) si está cargado; si no, alert().
function avisar(mensaje, tipo = "info") {
    if (window.Swal) {
        return Swal.fire({ text: mensaje, icon: tipo, confirmButtonText: "Aceptar" });
    }
    alert(mensaje);
    return Promise.resolve();
}

async function confirmar(mensaje, textoBoton) {
    if (window.Swal) {
        const r = await Swal.fire({
            text: mensaje,
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: textoBoton,
            cancelButtonText: "Cancelar",
        });
        return r.isConfirmed;
    }
    return confirm(mensaje);
}

// Crea un elemento con texto (textContent), así nada de lo que escribe el usuario se interpreta como HTML.
function crear(etiqueta, clase, texto) {
    const el = document.createElement(etiqueta);
    if (clase) el.className = clase;
    if (texto !== undefined) el.textContent = texto;
    return el;
}

async function obtenerToken() {
    const res = await fetch("../PHP/csrf_token.php", { credentials: "same-origin" });
    const datos = await res.json();
    return datos.token;
}

// POST con token CSRF. Recibe un FormData y devuelve el JSON del servidor.
async function enviar(url, formData) {
    formData.append("csrf_token", await obtenerToken());

    const res = await fetch(url, { method: "POST", body: formData, credentials: "same-origin" });

    if (res.status === 401) {
        window.location.replace("iniciar_sesion.html");
        throw new Error("Sesión vencida");
    }
    return res.json();
}

function bloquearBoton(boton, bloqueado, textoOcupado) {
    if (!boton) return;
    if (bloqueado) {
        boton.dataset.textoOriginal = boton.textContent;
        boton.textContent = textoOcupado;
    } else if (boton.dataset.textoOriginal) {
        boton.textContent = boton.dataset.textoOriginal;
    }
    boton.disabled = bloqueado;
}

/* ---------- Carga del perfil ---------- */

function mostrarFoto(ruta) {
    if (ruta) {
        imgFoto.src = ruta;
        imgFoto.alt = "Foto de perfil";
    } else {
        imgFoto.removeAttribute("src");
        imgFoto.alt = "";
    }
}

function pintarUsuario(u) {
    document.getElementById("nombre-completo").textContent = `${u.nombre} ${u.apellido}`.trim();
    document.getElementById("rol-usuario").textContent = ETIQUETAS_ROL[u.rol] || u.rol;
    mostrarFoto(u.foto);

    document.getElementById("nombre").value           = u.nombre;
    document.getElementById("apellido").value         = u.apellido;
    const correo = document.getElementById("correo");
    correo.value = u.correo;
    correo.defaultValue = u.correo; // referencia para detectar si el usuario lo cambió
    document.getElementById("Fecha_Nacimiento").value = u.Fecha_Nacimiento;
    document.getElementById("genero").value           = u.genero;
    document.getElementById("cedula").value           = u.cedula;
    document.getElementById("Num_Telefono").value     = u.Num_Telefono;
    document.getElementById("sobre_mi").value         = u.sobre_mi || "";
}

async function cargarPerfil() {
    try {
        const res = await fetch("../PHP/mostrar_perfil.php", { credentials: "same-origin" });

        if (res.status === 401) {
            window.location.replace("iniciar_sesion.html");
            return;
        }

        const datos = await res.json();

        if (!datos.exito) {
            paginaPerfil.replaceChildren(crear("p", "vacio", datos.mensaje || "No se pudo cargar tu perfil."));
            return;
        }

        pintarUsuario(datos.usuario);
        direcciones = datos.direcciones;
        pintarDirecciones();

    } catch (error) {
        console.error("Error al cargar el perfil:", error);
        paginaPerfil.replaceChildren(crear("p", "vacio", "No se pudo cargar tu perfil. Revisá tu conexión e intentá de nuevo."));
    }
}

/* ---------- Datos personales ---------- */

formDatos.addEventListener("submit", async (evento) => {
    evento.preventDefault();

    const telefono = document.getElementById("Num_Telefono").value;
    const fecha    = document.getElementById("Fecha_Nacimiento").value;

    if (!validarTelefono(telefono)) {
        avisar("El teléfono debe tener exactamente 9 dígitos.", "warning");
        return;
    }
    if (!cumpleEdadMinima(fecha)) {
        avisar(`La fecha de nacimiento no es válida o sos menor de ${EDAD_MINIMA} años.`, "warning");
        return;
    }

    const boton = formDatos.querySelector("button[type=submit]");
    const datosForm = new FormData(formDatos);
    datosForm.delete("cedula"); // la cédula no se cambia; el servidor usa la de la sesión

    // Si cambia el correo, hay que verificarlo de nuevo y se cierra la sesión: avisamos antes.
    // (Se compara con el valor que cargó el servidor, guardado en defaultValue.)
    const correoInput = document.getElementById("correo");
    if (correoInput.value.trim().toLowerCase() !== correoInput.defaultValue.trim().toLowerCase()) {
        const seguir = await confirmar(
            "Si cambiás el correo vas a tener que verificarlo con un código y se va a cerrar tu sesión. ¿Continuar?",
            "Sí, cambiar"
        );
        if (!seguir) return;
    }

    bloquearBoton(boton, true, "Guardando...");
    try {
        const r = await enviar("../PHP/actualizar_perfil.php", datosForm);

        if (!r.exito) {
            avisar(r.mensaje, "error");
            return;
        }

        if (r.verificacion) {
            await avisar(r.mensaje, "info");
            window.location.href = r.redirect;
            return;
        }

        document.getElementById("nombre-completo").textContent = `${r.nombre} ${r.apellido}`.trim();
        correoInput.defaultValue = correoInput.value;
        avisar(r.mensaje, "success");

    } catch (error) {
        if (error.message !== "Sesión vencida") {
            console.error(error);
            avisar("No se pudo guardar. Intentá de nuevo.", "error");
        }
    } finally {
        bloquearBoton(boton, false);
    }
});

// Teléfono: solo dígitos mientras escribe.
document.getElementById("Num_Telefono").addEventListener("input", (e) => {
    e.target.value = e.target.value.replace(/\D/g, "").slice(0, 9);
});
document.getElementById("codigo_postal").addEventListener("input", (e) => {
    e.target.value = e.target.value.replace(/\D/g, "").slice(0, 5);
});

/* ---------- Foto de perfil ---------- */

const TIPOS_FOTO = ["image/jpeg", "image/png", "image/webp"];
const MAX_FOTO_BYTES = 2 * 1024 * 1024;

// Vista previa apenas elige el archivo (todavía no se guardó).
document.getElementById("foto").addEventListener("change", (e) => {
    const archivo = e.target.files[0];
    if (!archivo) return;

    if (!TIPOS_FOTO.includes(archivo.type)) {
        avisar("Formato no permitido. Usá JPG, PNG o WEBP.", "warning");
        e.target.value = "";
        return;
    }
    if (archivo.size > MAX_FOTO_BYTES) {
        avisar("La imagen es muy pesada (máximo 2 MB).", "warning");
        e.target.value = "";
        return;
    }

    const lector = new FileReader();
    lector.onload = () => mostrarFoto(lector.result);
    lector.readAsDataURL(archivo);
});

formFoto.addEventListener("submit", async (evento) => {
    evento.preventDefault();

    const archivo = document.getElementById("foto").files[0];
    if (!archivo) {
        avisar("Elegí una imagen primero.", "warning");
        return;
    }

    const boton = formFoto.querySelector("button[type=submit]");
    bloquearBoton(boton, true, "Subiendo...");
    try {
        const r = await enviar("../PHP/subir_foto_perfil.php", new FormData(formFoto));

        if (!r.exito) {
            avisar(r.mensaje, "error");
            cargarPerfil(); // vuelve a mostrar la foto guardada en vez de la vista previa
            return;
        }

        mostrarFoto(r.foto);
        formFoto.reset();
        avisar(r.mensaje, "success");

    } catch (error) {
        if (error.message !== "Sesión vencida") {
            console.error(error);
            avisar("No se pudo subir la foto. Intentá de nuevo.", "error");
        }
    } finally {
        bloquearBoton(boton, false);
    }
});

/* ---------- Direcciones ---------- */

function crearTarjetaDireccion(d) {
    const tarjeta = crear("article", d.es_principal ? "direccion direccion--principal" : "direccion");

    const cabecera = crear("div", "direccion__cabecera");
    cabecera.append(crear("h3", "", d.alias));
    if (d.es_principal) cabecera.append(crear("span", "direccion__etiqueta", "Principal"));
    tarjeta.append(cabecera);

    let linea1 = `${d.calle} ${d.numero_puerta}`;
    if (d.apartamento) linea1 += `, apto ${d.apartamento}`;
    if (d.esquina) linea1 += ` (esquina ${d.esquina})`;
    tarjeta.append(crear("p", "", linea1));

    let linea2 = `${d.barrio}, ${d.ciudad}, ${d.departamento}`;
    if (d.codigo_postal) linea2 += ` · CP ${d.codigo_postal}`;
    tarjeta.append(crear("p", "", linea2));

    if (d.indicaciones) tarjeta.append(crear("p", "direccion__indicaciones", d.indicaciones));

    const acciones = crear("div", "direccion__acciones");

    if (!d.es_principal) {
        const btnPrincipal = crear("button", "boton boton--chico boton--suave", "Hacer principal");
        btnPrincipal.type = "button";
        btnPrincipal.addEventListener("click", () => hacerPrincipal(d.id));
        acciones.append(btnPrincipal);
    }

    const btnEditar = crear("button", "boton boton--chico boton--suave", "Editar");
    btnEditar.type = "button";
    btnEditar.addEventListener("click", () => empezarEdicion(d));

    const btnEliminar = crear("button", "boton boton--chico boton--peligro", "Eliminar");
    btnEliminar.type = "button";
    btnEliminar.addEventListener("click", () => eliminarDireccion(d));

    acciones.append(btnEditar, btnEliminar);
    tarjeta.append(acciones);

    return tarjeta;
}

function pintarDirecciones() {
    if (direcciones.length === 0) {
        listaDirec.replaceChildren(crear("p", "vacio", "Todavía no agregaste ninguna dirección."));
        return;
    }
    listaDirec.replaceChildren(...direcciones.map(crearTarjetaDireccion));
}

async function recargarDirecciones() {
    const res = await fetch("../PHP/mostrar_perfil.php", { credentials: "same-origin" });
    const datos = await res.json();
    if (datos.exito) {
        direcciones = datos.direcciones;
        pintarDirecciones();
    }
}

// Formulario: modo "nueva" o modo "editar".
function textoBotonDireccion() {
    return formDireccion.querySelector("button[type=submit]");
}

function salirDeEdicion() {
    editandoId = null;
    formDireccion.reset();
    textoBotonDireccion().textContent = "Guardar dirección";
}

function empezarEdicion(d) {
    editandoId = d.id;

    document.getElementById("alias_direccion").value = d.alias;
    document.getElementById("calle").value           = d.calle;
    document.getElementById("numero_puerta").value   = d.numero_puerta;
    document.getElementById("apartamento").value     = d.apartamento || "";
    document.getElementById("esquina").value         = d.esquina || "";
    document.getElementById("barrio").value          = d.barrio;
    document.getElementById("ciudad").value          = d.ciudad;
    document.getElementById("departamento").value    = d.departamento;
    document.getElementById("codigo_postal").value   = d.codigo_postal || "";
    document.getElementById("indicaciones").value    = d.indicaciones || "";

    const principal = document.getElementById("es_principal");
    principal.checked  = d.es_principal;
    principal.disabled = d.es_principal; // la principal sigue siéndolo hasta que se elija otra

    textoBotonDireccion().textContent = "Guardar cambios de la dirección";
    detalleNueva.open = true;
    detalleNueva.scrollIntoView({ behavior: "smooth", block: "center" });
}

// Al cerrar el desplegable se descarta lo que se estaba editando.
detalleNueva.addEventListener("toggle", () => {
    if (!detalleNueva.open) {
        salirDeEdicion();
        document.getElementById("es_principal").disabled = false;
    }
});

formDireccion.addEventListener("submit", async (evento) => {
    evento.preventDefault();

    const cp = document.getElementById("codigo_postal").value;
    if (cp !== "" && !/^\d{5}$/.test(cp)) {
        avisar("El código postal debe tener 5 dígitos.", "warning");
        return;
    }

    const datosForm = new FormData(formDireccion);
    datosForm.set("accion", editandoId ? "editar" : "crear");
    if (editandoId) datosForm.set("id_direccion", editandoId);
    // Un checkbox deshabilitado no se envía, así que lo mandamos a mano al editar la principal.
    if (document.getElementById("es_principal").checked) datosForm.set("es_principal", "1");

    const boton = textoBotonDireccion();
    bloquearBoton(boton, true, "Guardando...");
    try {
        const r = await enviar("../PHP/direcciones.php", datosForm);

        if (!r.exito) {
            avisar(r.mensaje, "error");
            return;
        }

        detalleNueva.open = false; // dispara salirDeEdicion() y limpia el formulario
        await recargarDirecciones();
        avisar(r.mensaje, "success");

    } catch (error) {
        if (error.message !== "Sesión vencida") {
            console.error(error);
            avisar("No se pudo guardar la dirección. Intentá de nuevo.", "error");
        }
    } finally {
        bloquearBoton(boton, false);
    }
});

async function hacerPrincipal(id) {
    const datosForm = new FormData();
    datosForm.set("accion", "principal");
    datosForm.set("id_direccion", id);

    try {
        const r = await enviar("../PHP/direcciones.php", datosForm);
        if (!r.exito) {
            avisar(r.mensaje, "error");
            return;
        }
        await recargarDirecciones();
    } catch (error) {
        if (error.message !== "Sesión vencida") avisar("No se pudo actualizar. Intentá de nuevo.", "error");
    }
}

async function eliminarDireccion(d) {
    if (!(await confirmar(`¿Eliminar la dirección "${d.alias}"?`, "Sí, eliminar"))) return;

    const datosForm = new FormData();
    datosForm.set("accion", "eliminar");
    datosForm.set("id_direccion", d.id);

    try {
        const r = await enviar("../PHP/direcciones.php", datosForm);
        if (!r.exito) {
            avisar(r.mensaje, "error");
            return;
        }
        if (editandoId === d.id) detalleNueva.open = false;
        await recargarDirecciones();
    } catch (error) {
        if (error.message !== "Sesión vencida") avisar("No se pudo eliminar. Intentá de nuevo.", "error");
    }
}

cargarPerfil();
