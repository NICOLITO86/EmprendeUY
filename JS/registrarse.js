const formulario = document.getElementById("formulario");

/* ----------------------------------------------------------
   Plan elegido en la portada (Registrarse.html?plan=premium)
   Solo se acepta uno de los planes que existen.
   ---------------------------------------------------------- */
const PLANES_VALIDOS = ["basico", "destacado", "premium"];
const planParam = new URLSearchParams(location.search).get("plan");
const plan = PLANES_VALIDOS.includes(planParam) ? planParam : null;

// Los planes son para emprendedores: se deja el rol ya elegido al cargar la página
if (plan) {
    document.getElementById("Rol").value = "emprendedor";
}

/* A dónde ir después de registrarse:
   - emprendedor que venía de elegir un plan -> directo a pagar
   - cualquier otro caso -> lo que indique el servidor */
function destinoFinal(redirect, rol) {
    if (!plan || rol !== "emprendedor") return redirect;

    // Si el servidor lo manda a iniciar sesión, el plan viaja con él
    // (después de iniciar sesión hay que seguir a Pago.html?plan=...)
    if (/iniciar_sesion/i.test(redirect)) {
        return `${redirect}${redirect.includes("?") ? "&" : "?"}plan=${plan}`;
    }

    return `Pago.html?plan=${plan}`;
}

formulario.addEventListener("submit", (e) => {
    e.preventDefault();

    const cedula = document.getElementById("cedula").value;
    const telefono = document.getElementById("Num_Telefono").value;
    const fechaNacimiento = document.getElementById("Fecha_Nacimiento").value;

    if (!validarCedula(cedula)) {
        Swal.fire({
            icon: "error",
            title: "Cédula inválida",
            text: "La cédula debe tener exactamente 8 dígitos numéricos (sin puntos ni guiones)."
        });
        return;
    }

    if (!validarTelefono(telefono)) {
        Swal.fire({
            icon: "error",
            title: "Teléfono inválido",
            text: "El número de teléfono debe tener exactamente 9 dígitos."
        });
        return;
    }

    const edad = calcularEdad(fechaNacimiento);

    if (edad === null) {
        Swal.fire({
            icon: "error",
            title: "Fecha inválida",
            text: "Ingresá una fecha de nacimiento válida."
        });
        return;
    }

    if (edad < EDAD_MINIMA) {
        Swal.fire({
            icon: "error",
            title: "No podés registrarte",
            text: `Tenés que tener al menos ${EDAD_MINIMA} años para crear una cuenta en EmprendeUY.`
        });
        return;
    }

    const form = new FormData(formulario);
    const rol = form.get("Rol");

    fetch("../PHP/registro.php", {
        method: "post",
        body: form
    })
        .then((res) => res.json())
        .then((datos) => {
            if (datos.exito) {
                Swal.fire({
                    title: "Registro exitoso!",
                    icon: "success",
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = destinoFinal(datos.redirect, rol);
                });
            } else {
                Swal.fire({
                    icon: "error",
                    title: "No fue agregado..",
                    text: datos.msg
                });
            }
        })
        .catch(() => {
            // Sin esto, un error de red o una respuesta que no es JSON no mostraba nada
            Swal.fire({
                icon: "error",
                title: "Algo salió mal",
                text: "No pudimos completar el registro. Probá de nuevo en unos minutos."
            });
        });
});
