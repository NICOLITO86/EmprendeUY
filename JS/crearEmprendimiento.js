const formulario = document.getElementById("formulario");
const div = document.getElementById("div1");
formulario.addEventListener("submit", async (y) => {

    y.preventDefault();

    let form=new FormData(formulario)

    const tokenRes = await fetch("../PHP/csrf_token.php");
    const tokenDatos = await tokenRes.json();
    form.append("csrf_token", tokenDatos.token);

    fetch("../PHP/crear_Emprendimiento.php",
    {method:"post",
        body: form})


        .then(res=> res.json())
    .then(emprendimiento=>{ 
        console.log(emprendimiento);
        if(emprendimiento.exito){
            div.innerHTML = '<h3>Agregado</h3>';
        }else{
            div.innerHTML = '<h3>Error</h3>';
        }

    })
     })