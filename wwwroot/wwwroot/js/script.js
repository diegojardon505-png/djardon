function resetImage(){
    alert("La imagen se va a reiniciar");
    $.ajax({
        url: '/ajax-jquery/img-woman.html',
        cache: false,
        success: function(result) {
            $('#contenido-imagen').html(result);
        }
    });
}

function changeImage(){
    alert("La imagen se va a cambiar");
    $.ajax({
        url: '/ajax-jquery/img-man.html',
        cache: false,
        success: function(result) {
            $('#contenido-imagen').html(result);
        }
    });
}

function registroUsuarios(){
    let formData = new FormData();
    formData.append("nombre", $("#nombre").val());
    formData.append("email",  $("#email").val());
    formData.append("pwd",    $("#pwd").val());

    $.ajax({
        url: "/ajax-jquery/usuarios.php",
        data: formData,
        processData: false,
        contentType: false,
        type: "POST",
        cache: false,
        success: function(result){
            $('#form-registro-usuarios')[0].reset();
            $('#main').html(result);
        }
    });
}

function login(){
    let formData = new FormData();
    formData.append("email", $("#email").val());
    formData.append("pwd",   $("#pwd").val());

    $.ajax({
        url: "/ajax-jquery/login.php",
        data: formData,
        processData: false,
        contentType: false,
        type: "POST",
        cache: false,
        success: function(result){
            $('#main').html(result);
        }
    });
}