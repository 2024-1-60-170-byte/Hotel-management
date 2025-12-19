<?php 
include("../config/db.php");
include("../includes/header.php");

if(isset($_POST["add_role"])){
    $name = $_POST["role_name"];
    $sql = "INSERT INTO role(Role_name) VALUES(?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $name);
    $stmt->execute();
}
?>

<div class="container mt-4">
    <h2>Roles</h2>

    <form method="POST" class="card p-3 mt-3">
        <h4>Add Role</h4>
        <input name="role_name" class="form-control mt-2" placeholder="Role Name" required>
        <button class="btn btn-primary mt-3" name="add_role">Add Role</button>
    </form>

    <table class="table table-bordered mt-4">
        <tr>
            <th>ID</th><th>Name</th>
        </tr>
        <?php 
        $roles = $conn->query("SELECT * FROM role");
        while($row = $roles->fetch_assoc()){
            echo "<tr>
                    <td>{$row['Role_id']}</td>
                    <td>{$row['Role_name']}</td>
                  </tr>";
        }
        ?>
    </table>
</div>

<?php include("../includes/footer.php"); ?>
