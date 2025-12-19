<?php 
session_start();
include("../config/db.php");

if(isset($_POST["login"])){
    $email = $_POST["email"];
    $password = $_POST["password"];

    $sql = "SELECT * FROM member WHERE email = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows == 1){
        $_SESSION["user"] = $email;
        header("Location: index.php");
    } else {
        $error = "Invalid Login Details!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Login</title>
<link rel="stylesheet" href="../assets/bootstrap.css">
</head>
<body class="container mt-5">

<h2 class="text-center">EWU Hostel Management System</h2>
<h3 class="text-center">Enter your credentials</h3>
<form method="POST" class="mt-4">
    <input type="email" name="email" class="form-control" placeholder="Email" required><br>
    <input type="password" name="password" class="form-control" placeholder="Password" required><br>
    <button type="submit" name="login" class="btn btn-primary w-100">Login</button>
</form>

<?php if(isset($error)) echo "<p class='text-danger mt-2'>$error</p>"; ?>
</body>
</html>
