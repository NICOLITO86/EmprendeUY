const formulario = document.getElementById("crearpublicaiones");
const div=document.getElementById("div")

formulario.addEventListener("submit", async (y) => {

    y.preventDefault();

    let form=new FormData(crearpublicaiones)

    const tokenRes = await fetch("../PHP/csrf_token.php");
    const tokenDatos = await tokenRes.json();
    form.append("csrf_token", tokenDatos.token);

    fetch("../PHP/crear_publicaciones.php",
    {method:"post",
        body: form})


        .then(res=> res.json())
    .then(publicacion=>{ 
        console.log(publicacion);
        if(publicacion.exito){
            div.innerHTML = '<h3>Agregado</h3>';
        }else{
            div.innerHTML = '<h3>Error</h3>';
        }

    })
     })