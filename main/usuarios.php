<?php

require "../sql/database.php";
require "./partials/kardex.php";
require "./partials/session_handler.php"; 



// Si la sesión no existe, redirigir al login.php y dejar de ejecutar el resto
if (!isset($_SESSION["user"])) {
    header("Location: ../login-form/login.php");
    return;
}

// Declaramos la variable error que nos ayudará a mostrar errores, etc.
$error = null;
$id = isset($_GET["id"]) ? $_GET["id"] : null; 
$usuarioEditar = null;

if ($_SESSION["user"]["usu_rol"] && $_SESSION["user"]["usu_rol"] == 1) {
    // Llamamos los contactos de la base de datos y especificamos que sean los que tengan el usu_id de la función session_start
    $usuarios = $conn->query("SELECT * FROM usuarios");
    $personas = $conn->query("SELECT * FROM personas");

    // Verificamos el método que usa el formulario con un if
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Validamos que no se manden datos vacíos
        if (empty($_POST["cedula"]) || empty($_POST["usuario"]) || empty($_POST["rol"])) {
            $error = "POR FAVOR RELLENA TODOS LOS CAMPOS.";
        } else {
            // Verificamos si ya existe un registro para el usuario actual
            $existingStatement = $conn->prepare("SELECT id_user FROM usuarios WHERE cedula = :cedula");
            $existingStatement->execute([":cedula" => $_POST['cedula']]);
            $existingUsuario = $existingStatement->fetch(PDO::FETCH_ASSOC);
        
            if ($existingUsuario) {
                // Si existe, actualizamos el registro existente
                $statement = $conn->prepare("UPDATE usuarios SET
                    usu_user = :usuario,
                    usu_rol = :rol
                    WHERE id_user = :id");
        
                $statement->execute([
                    ":id" => $existingUsuario["id_user"],
                    ":usuario" => $_POST["usuario"],
                    ":rol" => $_POST["rol"],
                ]);
                // Registramos el movimiento en el kardex
                registrarEnKardex($_SESSION["user"]["cedula"], "EDITÓ", 'usuarios', $_POST["usuario"]);
            } else {
                // Validamos la contraseña si es un nuevo registro
                if (empty($_POST["password"])) {
                    $error = "POR FAVOR RELLENA EL CAMPO DE CONTRASEÑA.";
                } elseif (!preg_match('/^(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#$%^&*()-_+=])[A-Za-z0-9!@#$%^&*()-_+=]{6,}$/', $_POST["password"])) {
                    $error = "LA CONTRASEÑA DEBE TENER AL MENOS 6 CARÁCTERES Y CONTENER AL MENOS UNA LETRA MAYÚSCULA, UN NÚMERO Y UN CARÁCTER ESPECIAL."; 
                } else {
                    // Si no existe, insertamos un nuevo registro
                    $statement = $conn->prepare("INSERT INTO usuarios (cedula, usu_user, usu_password, usu_rol, usu_registro) 
                    VALUES (:cedula, :usuario, :password, :rol, CURRENT_TIMESTAMP)");

                    $statement->execute([
                    ":cedula" => $_POST["cedula"],
                    ":usuario" => $_POST["usuario"],
                    ":password" => password_hash($_POST["password"], PASSWORD_BCRYPT),
                    ":rol" => $_POST["rol"],
                    ]);
                    // Registramos el movimiento en el kardex
                    registrarEnKardex($_SESSION["user"]["cedula"], "CREÓ", 'USUARIOS', $_POST["usuario"]);

                }
            }
        
            // Redirigimos a home.php
            header("Location: usuarios.php");
            return;
        }
    }
} else {
    header("Location: ./index.php");
    return;
}
?>





<?php require "./partials/header.php"; ?>
<?php require "./partials/dashboard.php"; ?>
<section class="section">
    <div class="row">
        <div class="">
            <?php if (empty($id)) : ?>
            <div class="card accordion" id="accordionExample">
                <div class="card-body accordion-item">
                    <h5 class="card-title accordion-header" id="headingOne">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                            NUEVO USUARIO
                        </button>
                    </h5>

                    <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                        <div class="accordion-body">
                            <!-- si hay un error mandar un danger -->
                            <?php if ($error): ?> 
                                <p class="text-danger">
                                    <?= $error ?>
                                </p>
                            <?php endif ?>
                            <form class="row g-3" method="POST" action="usuarios.php">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="number" class="form-control" id="cedula" name="cedula" placeholder="Buscar por Cedula" list="cedulaList" oninput="buscarPorCedula()" autocomplete="cedula" required>
                                    <label for="cedula">Cédula</label>
                                    <datalist id="cedulaList">
                                        <?php foreach ($personas as $persona): ?>
                                        <option value="<?= $persona["cedula"]?>">
                                        <?php endforeach ?>
                                    </datalist>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Trabajador" readonly>
                                    <label for="nombre">TRABAJADOR</label>
                                </div>
                            </div>




                            <div class="col-md-6">
                                <div class="form-floating">
                                <input type="text" class="form-control" id="usuario" name="usuario" placeholder="usuario" autocomplete="usuario" required>
                                <label for="usuario">USUARIO</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating d-flex">
                                <input type="password" class="form-control" id="password" name="password" placeholder="password" autocomplete="password" required>
                                <label for="password">CONTRASEÑA</label>
                                <button id="show_password" class="btn btn-primary" type="button" onclick="mostrarPassword()"> <span class="fa fa-eye-slash icon"></span> </button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                <select class="form-select" id="rol" aria-label="State" name="rol">
                                    <option value="1">SUPER ADMINISTRADOR</option>
                                    <option value="2">ADMIN DISEÑO</option>
                                    <option value="3">DISEÑADOR</option>
                                    <option value="4">ADMIN PRODUCCIÓN</option>
                                    <option value="5">PRODUCCIÓN</option>
                                    <option value="6">PERSONAL</option>
                                    <option value="7">PRESENTACIÓN</option>
                                </select>
                                <label for="rol">ROL DE USUARIO</label>
                                </div>
                            </div>
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary">GUARDAR</button>
                                <button type="reset" class="btn btn-secondary">LIMPIAR</button>
                            </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php else : ?>
                <?php 
                    $statement = $conn->prepare("SELECT U.*, P.* 
                                                FROM usuarios U
                                                INNER JOIN personas P ON U.cedula = P.cedula
                                                WHERE U.id_user = :id");

                    $statement->bindParam(":id", $id);
                    $statement->execute();
                    $usuarioEditar = $statement->fetch(PDO::FETCH_ASSOC);

                ?>
                <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Editar Usuario</h5>

                    <!-- si hay un error mandar un danger -->
                    <?php if ($error): ?> 
                        <p class="text-danger">
                            <?= $error ?>
                        </p>
                    <?php endif ?>
                    <form class="row g-3" method="POST" action="usuarios.php">
                    <?php
                    $nombreTrabajador = isset($usuarioEditar['per_nombres']) ? $usuarioEditar['per_nombres'] : '';
                    $nombreTrabajador .= isset($usuarioEditar['per_apellidos']) ? ' ' . $usuarioEditar['per_apellidos'] : '';
                    ?>
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input value="<?= $usuarioEditar['cedula'] ?>" type="text" class="form-control" id="cedula" name="cedula" placeholder="Buscar por Cedula" list="cedulaList" oninput="buscarPorCedula()" readonly>
                            <label for="cedula">CÉDULA</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input value="<?= $nombreTrabajador ?>" id="trabajadorInfo" id="trabajadorInfo" type="text" class="form-control" id="nombre" name="nombre" placeholder="Trabajador" readonly>
                            <label for="nombre">TRABAJADOR</label>
                        </div>
                    </div>




                    <div class="col-md-6">
                        <div class="form-floating">
                        <input value="<?= $usuarioEditar['usu_user'] ?>" type="text" class="form-control" id="usuario" name="usuario" placeholder="usuario">
                        <label for="usuario">USUARIO</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <select class="form-select" id="rol" aria-label="State" name="rol">
                                <option value="1" <?= ($usuarioEditar['usu_rol'] == 1) ? 'selected' : '' ?>>SUPER ADMINISTRADOR</option>
                                <option value="2" <?= ($usuarioEditar['usu_rol'] == 2) ? 'selected' : '' ?>>ADMIN DISEÑO</option>
                                <option value="3" <?= ($usuarioEditar['usu_rol'] == 3) ? 'selected' : '' ?>>DISEÑADOR</option>
                                <option value="4" <?= ($usuarioEditar['usu_rol'] == 4) ? 'selected' : '' ?>>ADMIN PRODUCCIÓN</option>
                                <option value="5" <?= ($usuarioEditar['usu_rol'] == 5) ? 'selected' : '' ?>>PRODUCCIÓN</option>
                                <option value="6" <?= ($usuarioEditar['usu_rol'] == 6) ? 'selected' : '' ?>>PERSONAL</option>
                                <option value="7" <?= ($usuarioEditar['usu_rol'] == 7) ? 'selected' : '' ?>>PRESENTACIÓN</option>
                            </select>

                            <label for="rol">ROL DE USUARIO</label>
                        </div>
                    </div>

                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">ACTUALIZAR</button>
                        <button type="reset" class="btn btn-secondary">LIMPIAR</button>
                    </div>
                    </form>

                </div>
            </div>
            <?php endif ?>
            <section class="section">
                <div class="row">
                    <div class="col-lg-12">

                    <div class="card">
                        <div class="card-body">
                        <h5 class="card-title">USUARIO</h5>
                        <!-- si el array asociativo $teachers no tiene nada dentro, entonces imprimir el siguiente div -->
                        <?php if ($usuarios->rowCount() == 0): ?>
                            <div class= "col-md-4 mx-auto mb-3">
                                <div class= "card card-body text-center">
                                    <p>NO HAY USUARIOS AÚN.</p>
                                </div>
                            </div>
                        <?php else: ?>
                        <!-- Table with stripped rows -->
                        <table class="table datatable">
                            <thead>
                            <tr>
                                <th>CÉDULA</th>
                                <th>USUARIO</th>
                                <th>ROL</th>
                                <th>REGISTRO</th>
                                <th></th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($usuarios as $usu): ?>
                                <tr>
                                <th><?= $usu["cedula"]?></th>
                                <td><?= $usu["usu_user"]?></td>
                                <td>
                                    <?php if( $usu["usu_rol"] == 1): ?>
                                        SUPER ADMINISTRADOR
                                    <?php elseif( $usu["usu_rol"] == 2): ?>
                                        ADMIN DISEÑO
                                    <?php elseif( $usu["usu_rol"] == 3): ?>
                                        DISEÑADOR
                                    <?php elseif( $usu["usu_rol"] == 4): ?>
                                        ADMIN PRODUCCIÓN
                                    <?php elseif( $usu["usu_rol"] == 5): ?>
                                        PRODUCCIÓN
                                    <?php elseif( $usu["usu_rol"] == 6): ?>
                                        PERSONAL
                                    <?php elseif( $usu["usu_rol"] == 7): ?>
                                        PRESENTACIÓN
                                    <?php endif ?>
                                </td>
                                <td><?= $usu["usu_registro"]?></td>
                                <td>
                                    <a href="usuarios.php?id=<?= $usu["id_user"] ?>" class="btn btn-secondary mb-2">EDITAR</a>
                                </td>
                                <td>
                                    <a href="cambiar_contrasena.php?id=<?= $usu["cedula"] ?>" class="btn btn-danger mb-2">CAMBIAR CONTRASEÑA</a>
                                </td>
                                </tr>
                            <?php endforeach ?>
                            
                            </tbody>
                        </table>
                        <?php endif ?>
                        </div>
                    </div>

                    </div>
                </div>
                </section>

        </div>
    </div>
</section>

<?php require "./partials/footer.php"; ?>
