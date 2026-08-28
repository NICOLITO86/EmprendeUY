const div=document.getElementById("div")
const from_borrar = document.getElementById("from_borrar");
from_borrar.addEventListener ("submit",(x)=> {
    x.preventDefault()

let form=new FormData(from_borrar)
    fetch("../PHP/borrar.php",
    {method:"post",
        body: form})

    .then(res=> res.json())
    .then(datos=>{ 
        console.log(datos)
        if(datos.exito){
            div.innerHTML = '<h3> Borrado</h3>'
        }else{
            div.innerHTML = '<h3> No se pudo borrar</h3>'
        }

    })
})

const div1=document.getElementById("div1")
const from_borrar1 = document.getElementById("from_borrar1");
from_borrar1.addEventListener ("submit",(x)=> {
    x.preventDefault()

let form=new FormData(from_borrar1)
    fetch("../PHP/borrar_Emprendimiento.php",
    {method:"post",
        body: form})

    .then(res=> res.json())
    .then(datos=>{ 
        console.log(datos)
        // Antes escribia en "div" (el del formulario de Borrar Usuario, de mas arriba)
        // en vez de "div1", que es el que corresponde a este formulario.
        if(datos.exito){
            div1.innerHTML = '<h3> Borrado</h3>'
        }else{
            div1.innerHTML = '<h3> No se pudo borrar</h3>'
        }

    })
})

const from_buscar2 = document.getElementById("from_buscar2");

from_buscar2.addEventListener("submit", (e) => {
const lista = document.getElementById("lista");
    e.preventDefault();

    let form = new FormData(from_buscar2);

    fetch("../PHP/mostrartodo.php", {
        method: "POST",
        body: form
    })
    .then(res => res.json())
    .then(datos => {

        console.log(datos);

        // Limpia la lista antes de mostrar los datos
        lista.innerHTML = "";

        datos.forEach(a => {

            lista.innerHTML += `
                <div>
                    <p><strong>Cedula:</strong> ${a.Cedula}</p>
                    <p><strong>Nombre:</strong> ${a.Nombre}</p>
                    <p><strong>Apellido:</strong> ${a.Apellido}</p>
                    <p><strong>Fecha de nacimiento:</strong> ${a.Fecha_Nacimiento}</p>
                    <p><strong>Edad:</strong> ${a.Edad}</p>
                    <p><strong>Gmail:</strong> ${a.Gmail}</p>
                    <p><strong>Telefono:</strong> ${a.Num_Telefono}</p>
                    <p><strong>Domicilio:</strong> ${a.Domicilio}</p>
                    <p><strong>Calle:</strong> ${a.Calle}</p>
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

    let form = new FormData(from_buscar);

    fetch("../PHP/buscar.php", {
        method: "post",
        body: form
    })
    .then(res => res.json())
    .then(datos => {

        console.log(datos);

        if (datos.Nombre) {

            div_buscar.innerHTML = `
                <h3>${datos.Nombre} ${datos.Apellido}</h3>
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

    // Crear el FormData del formulario
    let form = new FormData(from_buscar1);

    console.log("ID enviado:", form.get("ID"));

    fetch("../PHP/buscar_Emprendimiento.php", {
        method: "POST",
        body: form
    })
    // Antes se usaba res.text() + JSON.parse() a mano como parche porque
    // el PHP devolvia una respuesta vacia (ver fix de buscar_Emprendimiento.php).
    // Ya arreglado del lado del servidor, alcanza con res.json() directo.
    .then(res => res.json())
    .then(datos => {

        console.log("JSON recibido:", datos);

        if (datos.ID) {

            div_buscar1.innerHTML = `
                <h3>${datos.Nombre}</h3>
                <p>ID: ${datos.ID}</p>
                <p>Descripción: ${datos.Descripcion}</p>
            `;

        } else {

            div_buscar1.innerHTML = "<h3>No encontrado</h3>";

        }
    })
    .catch(error => {
        console.error("Error:", error);
    })});