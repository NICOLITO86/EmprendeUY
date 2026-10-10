// tienda-visitante.js
// Complemento OPCIONAL de tienda.js: no lo modifica, se carga despues de el.
//   1) Modo visitante: si no hay sesion, cambia los botones del encabezado, muestra un aviso
//      y el boton de cada producto invita a iniciar sesion en vez de comprar.
//   2) Selecciona la categoria cuando se entra desde la portada (tienda.html?categoria=ropa).
//   3) Muestra el nombre de la categoria como titulo de la lista.
// Si no queres usarlo, borra su <script> de tienda.html: la tienda sigue funcionando como antes.

(function () {

    const filtro = document.getElementById("filtro-categoria");
    const titulo = document.getElementById("titulo");
    const aviso = document.getElementById("aviso");
    const accionesVisitante = document.getElementById("acciones-visitante");
    const accionesUsuario = document.getElementById("acciones-usuario");

    // true = con sesión, false = visitante, null = no se pudo saber
    let logueado = null;

    function normalizar(texto) {
        return (texto ?? "").toString()
            .normalize("NFD").replace(/[\u0300-\u036f]/g, "")
            .trim().toLowerCase();
    }

    function actualizarTitulo() {
        if (!titulo) return;
        const hayCategoria = filtro.value && filtro.value !== "todas";
        titulo.textContent = hayCategoria ? filtro.value : "Todos los productos";
        titulo.hidden = false;
    }

    function irAIniciarSesion() {
        if (typeof Swal === "undefined") {
            location.href = "iniciar_sesion.html";
            return;
        }
        Swal.fire({
            title: "Iniciá sesión para comprar",
            text: "Podés mirar todo sin registrarte, pero para comprar necesitás una cuenta.",
            icon: "info",
            showCancelButton: true,
            confirmButtonText: "Iniciar sesión",
            cancelButtonText: "Seguir mirando"
        }).then(r => {
            if (r.isConfirmed) location.href = "iniciar_sesion.html";
        });
    }

    //1) Sesion / modo visitante
    fetch("../PHP/sesion.php")
        .then(res => res.json())
        .then(datos => { logueado = !!datos.logueado; })
        .catch(() => { logueado = null; })
        .finally(() => {
            if (logueado === false) {
                document.body.classList.add("es-visitante");
                if (aviso) aviso.hidden = false;
                if (accionesVisitante) accionesVisitante.hidden = false;
                if (accionesUsuario) accionesUsuario.hidden = true;
            }
        });

    // Si es visitante, el boton de compra de tienda.js muestra el aviso en vez de agregar al carrito.
    // (La validacion real sigue estando en Agregarcarro.php.)
    if (typeof window.agregarcarro === "function") {
        const agregarOriginal = window.agregarcarro;
        window.agregarcarro = function (idp) {
            if (logueado === false) {
                irAIniciarSesion();
                return;
            }
            return agregarOriginal(idp);
        };
    }

    //2) Categoria desde la direccion
    const original = (new URLSearchParams(location.search).get("categoria") || "").trim();
    const pedida = normalizar(original);
    let aplicada = false;

    function aplicarCategoria() {
        if (aplicada) return;
        aplicada = true;
        observador.disconnect();

        if (pedida) {
            const opcion = Array.from(filtro.options).find(o => normalizar(o.value) === pedida);
            if (opcion) {
            filtro.value = opcion.value;
            } else {
                // categorias.php solo lista categorias con publicaciones activas.
                // Si esta no esta, se agrega igual para que la lista diga "No se encontraron publicaciones".
                filtro.add(new Option(original.charAt(0).toUpperCase() + original.slice(1), original));
                filtro.value = original;
            }
            // tienda.js escucha este evento y vuelve a cargar los productos filtrados
            filtro.dispatchEvent(new Event("change"));
        }
        actualizarTitulo();
    }

    // Espera a que tienda.js termine de cargar las categorias (se agregan como <option>)
    let espera;
    const observador = new MutationObserver(() => {
        clearTimeout(espera);
        espera = setTimeout(aplicarCategoria, 250);
    });
    observador.observe(filtro, { childList: true });
    setTimeout(aplicarCategoria, 3000); // por si no hay categorias o categorias.php falla

    // 3) Titulo al cambiar de categoria
    filtro.addEventListener("change", actualizarTitulo);

})();
