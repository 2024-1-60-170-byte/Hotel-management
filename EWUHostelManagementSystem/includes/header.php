<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Hostel Management System</title>

    <!-- Local Bootstrap CSS -->
    <link rel="stylesheet" href="../assets/bootstrap.css">

    <!-- Optional: Small custom styling -->
    <style>
        body {
            background-color: #f5f5f5;
        }
        .navbar-brand {
            font-weight: bold;
        }
    </style>
</head>
<body>

<!-- Navigation Bar -->
<nav class="navbar navbar-dark bg-dark">
    <a href="index.php" class="navbar-brand ms-3">EWU Hostel Management System</a>

    <div class="d-flex me-3">
        <a href="members.php" class="btn btn-outline-light me-2">Members</a>
        <a href="expenses.php" class="btn btn-outline-light me-2">Expenses</a>
        <a href="meals.php" class="btn btn-outline-light me-2">Meals</a>
        <a href="rooms.php" class="btn btn-outline-light me-2">Rooms</a>
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>
</nav>

<div class="container mt-4">
