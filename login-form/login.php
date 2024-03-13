<?php

require "../sql/database.php";


$error = null;
//identifica el metodo que usa el server, en este caso si el metodo es POST procesa el if
if ($_SERVER["REQUEST_METHOD"] == "POST"){
  //validamos que los campos no se manden vacios
  if (empty($_POST["user"]) || empty($_POST["password"])){
    $error = "Llena todos los campos";
  } else {
    //verificamos que el user existe
    $statement = $conn->prepare("SELECT * FROM usuarios WHERE usu_user = :user LIMIT 1");
    $statement->bindParam(":user", $_POST["user"]);
    $statement->execute();
    //COMPROBAMOS QUE EL ID EXISTA, EN CASO DE QUE EL USUARIO NO SEA UN NAVEGADOR, Y SI NO EXISTE EL ID MANDAMOS UN ERROR
    if ($statement->rowCount() == 0) {
      $error = "Credenciales invalidas u.";
    } else {
      //obtenemos los datos de usuario y asignamos a una variable user y lo pedimos en fetch assoc para que lo mande en un formato asociativo
      $user = $statement->fetch(PDO::FETCH_ASSOC);
      //comparamos si la contrasenia ingresada en el form es igual a la contrasenia que obtuvimos en la variable user
      if (!password_verify($_POST["password"], $user["usu_password"])) {
        $error = "Credenciales invalidas p";
      } else {
        //borramos por asi decir la contrasenia del usuario en la secion para que no almacene ese valor y por seguridad
        unset($user["usu_password"]);
        //iniciamos una sesion la cual es una cookie que es como un hash almacenado en el pc usuario para que almacene compruebe el usuario, asi la manera  de acceder a la sesion es por medio de la cockie y si alguien intenta hackear necesita el hash para poder hacer peticiones al servidor en lugar de solo necesitas el id
        session_start();
        //asignamos el usuario que se logueo a la secion iniciada
        $_SESSION["user"] = $user;
        

        //redirige al home.php
        header("Location: ../main/index.php");
      }
    }
  }
}
?>


<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="fonts/icomoon/style.css">

    <link rel="stylesheet" href="css/owl.carousel.min.css">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    
    <!-- Style -->
    <link rel="stylesheet" href="css/style.css">

    <title>Login</title>
  </head>
  <body>
  

  
  <div class="content">
    <div class="container">
    <div class="body d-md-flex align-items-center justify-content-between">
      <div class="row">
        <div class="col-md-6">
          <img src="images/undraw_remotely_2j6y.svg" alt="Image" class="img-fluid">
        </div>
        <div class="col-md-6 contents">
          <div class="row justify-content-center">
            <div class="col-md-8">
              <div class="mb-4">
              <h3>Iniciar Sesión</h3>
            </div>
            
            <form action="login.php" method="POST">
              <!-- si hay un error mandar un danger -->
              <?php if ($error): ?> 
                <p class="text-danger">
                  <?= $error ?>
                </p>
              <?php endif ?>
              <div class="form-group first">
                <label for="user">Usuario</label>
                <input type="text" class="form-control" id="user" name="user" required autocomplete="user" autofocus>

              </div>
              <div class="form-group last mb-4">
                <label for="password">Contraseña</label>
                <input type="password" class="form-control" id="password" name="password" required autocomplete="password" autofocus>
                
              </div>
              
              <div class="d-flex mb-5 align-items-center">
                <label class="control control--checkbox mb-0"><span class="caption">Recordar credenciales</span>
                  <input type="checkbox" checked="checked"/>
                  <div class="control__indicator"></div>
                </label> 
              </div>

              <button type="submit" class="btn btn-block btn-info">Iniciar Sesión</button>

              
              
              
            </form>
            </div>
          </div>
          
        </div>
        
      </div>
    </div> 
    </div>
  </div>

  
    <script src="js/jquery-3.3.1.min.js"></script>
    <script src="js/popper.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/main.js"></script>
  </body>
</html>

