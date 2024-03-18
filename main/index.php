 
<?php 

require "../sql/database.php";
require "./partials/session_handler.php"; 

//si la sesion no existe, mandar al login.php y dejar de ejecutar el resto; se puede hacer un required para ahorra codigo
if (!isset($_SESSION["user"])) {
  header("Location: ../login-form/login.php");
  return;
}

date_default_timezone_set('America/Lima'); 


require "./partials/header.php";
require "./partials/dashboard.php"; 



  if ($_SESSION["user"]["usu_rol"] == 1) :
    
    require './home/admin.php';

  elseif ($_SESSION["user"]["usu_rol"] == 2) :

    require './home/adminDisenio.php';

  elseif ($_SESSION["user"]["usu_rol"] == 3) :

    require './home/diseniador.php';

  endif;



require "./partials/footer.php"; 

?>


