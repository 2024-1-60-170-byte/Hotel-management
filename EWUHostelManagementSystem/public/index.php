<?php 
session_start();
if(!isset($_SESSION["user"])) header("Location: login.php");
include("../includes/header.php");
?>

<div class="container mt-4">
    <h2>Dashboard</h2>

    <div class="row mt-3">
        <div class="col-md-3">
            <a href="members.php" class="btn btn-success w-100">Manage Members</a>
        </div>
        <div class="col-md-3">
            <a href="expenses.php" class="btn btn-warning w-100">Expenses</a>
        </div>
        <div class="col-md-3">
            <a href="meals.php" class="btn btn-info w-100">Meals</a>
        </div>
        <div class="col-md-3">
            <a href="rooms.php" class="btn btn-dark w-100">Rooms</a>
        </div>
    </div>
</div>

<?php include("../includes/footer.php"); ?>
