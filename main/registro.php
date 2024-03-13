<?php

require "../sql/database.php";
require "./partials/session_handler.php"; 


// Si la sesión no existe, redirigir al login.php y dejar de ejecutar el resto
if (!isset($_SESSION["user"])) {
    header("Location: ../login.php");
    return;
}

?>


<?php require "./partials/header.php"; ?>
<?php require "./partials/dashboard.php"; ?>

<div class="col-lg-12">

    <div class="card">
    <div class="card-body">
        <h5 class="card-title">Vertical Form</h5>

        <!-- Vertical Form -->
        <form class="row g-3">
        <div class="col-12">
            <label for="inputNanme4" class="form-label">Your Name</label>
            <input type="text" class="form-control" id="inputNanme4">
        </div>
        <div class="col-12">
            <label for="inputEmail4" class="form-label">Email</label>
            <input type="email" class="form-control" id="inputEmail4">
        </div>
        <div class="col-12">
            <label for="inputPassword4" class="form-label">Password</label>
            <input type="password" class="form-control" id="inputPassword4">
        </div>
        <div class="col-12">
            <label for="inputAddress" class="form-label">Address</label>
            <input type="text" class="form-control" id="inputAddress" placeholder="1234 Main St">
        </div>
        <div class="text-center">
            <button type="submit" class="btn btn-primary">Submit</button>
            <button type="reset" class="btn btn-secondary">Reset</button>
        </div>
        </form><!-- Vertical Form -->

    </div>
    </div>
</div>

<?php require "./partials/footer.php"; ?>