<?php
require "../sql/database.php";
session_start();

// Si la sesión no existe, redirigir al login.php y dejar de ejecutar el resto
if (!isset($_SESSION["user"])) {
    header("Location: ../login-form/login.php");
    return;
}

// declaramos la variable error
$error = null;

// Validar si el usuario es un empleado
if ($_SESSION["user"]["usu_rol"] == 6 || $_SESSION["user"]["usu_rol"] == 1) {
    // Obtener la cédula del empleado
    $empleado = $_SESSION["user"]["cedula"];
} else {
    // Redirigimos a la página principal o a donde desees
    header("Location: pages-error-404.html");
    return;
}
