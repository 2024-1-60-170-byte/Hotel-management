<?php
include("../config/db.php");

if(isset($_GET['id'])){
    $id = $_GET['id'];

    $sql = "DELETE FROM member WHERE member_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

header("Location: members.php");
exit();
?>
