const formulario = document.getElementById("crearpublicaiones");
const div=document.getElementById("div")

formulario.addEventListener("submit", async (y) => {

    y.preventDefault();

    let form=new FormData(crearpublicaiones)
    fetch("../PHP/crear_publicaciones.php",
    {method:"post",
        body: form})


        .then(res=> res.text())
    .then(publicacion=>{ 
        console.log(publicacion);
        if(publicacion.exito){
            div.innerHTML = '<h3>Agregado</h3>';
        }else{
            div.innerHTML = '<h3>Error</h3>';
        }

    })
     })