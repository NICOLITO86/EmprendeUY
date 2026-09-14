// auth-guard.js
// Reemplaza la verificación que antes hacía requirePageRole() del lado del
// servidor (en las versiones .php de las páginas de HTML). Ahora la página
// es un .html normal (no ejecuta PHP) y este script, apenas carga, le
// pregunta a auth.php si hay sesión activa y con qué rol.
//
// Uso: agregar en la página protegida, dentro del <body>, ANTES que
// cualquier otro script:
//
//   <script src="../JS/auth-guard.js" data-roles="emprendedor"></script>
//
// Para permitir más de un rol: data-roles="emprendedor,administrador"
//
// Mientras se resuelve la verificación, el <body> debe estar oculto con
// CSS (style="display:none" o una clase) para que no se vea el contenido
// protegido ni una fracción de segundo. Este script lo vuelve a mostrar
// si el acceso es válido.

(function () {
    const scriptActual = document.currentScript;
    const rolesPermitidos = (scriptActual?.dataset.roles || "")
        .split(",")
        .map((r) => r.trim())
        .filter(Boolean);

    if (rolesPermitidos.length === 0) {
        console.error('auth-guard.js: falta el atributo data-roles, ej: data-roles="emprendedor"');
        return;
    }

    fetch("../PHP/auth.php", { credentials: "same-origin" })
        .then((res) => res.json())
        .then((datos) => {
            const tienePermiso = datos.autenticado && rolesPermitidos.includes(datos.rol);

            if (!tienePermiso) {
                window.location.replace("acceso-denegado.html");
                return;
            }

            // Acceso permitido: mostramos el contenido de la página.
            document.body.style.display = "";
        })
        .catch((error) => {
            console.error("Error al verificar acceso:", error);
            window.location.replace("acceso-denegado.html");
        });
})();
