<?php 
include("../config/db.php");
include("../includes/header.php");

if(isset($_POST["assign"])){
    $member = $_POST["member_id"];
    $start = $_POST["start_date"];
    $end = $_POST["end_date"];

    $sql = "INSERT INTO member_seat(member_id, start_date, end_date)
            VALUES(?,?,?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $member, $start, $end);
    $stmt->execute();
}
?>

<div class="container mt-4">
    <h2>Seat Assignment</h2>

    <form method="POST" class="card p-3 mt-3">
        <h4>Assign Seat</h4>

        <select name="member_id" class="form-control mt-2">
            <?php
            $m = $conn->query("SELECT * FROM member");
            while($row = $m->fetch_assoc()){
                echo "<option value='{$row['member_id']}'>{$row['name']}</option>";
            }
            ?>
        </select>

        <label class="mt-2">Start Date</label>
        <input type="date" name="start_date" class="form-control mt-2" required>

        <label class="mt-2">End Date</label>
        <input type="date" name="end_date" class="form-control mt-2">

        <button class="btn btn-primary mt-3" name="assign">Assign Seat</button>
    </form>

    <table class="table table-bordered mt-4">
        <tr>
            <th>Assign ID</th><th>Member</th><th>Start</th><th>End</th>
        </tr>
        <?php 
        $assign = $conn->query("SELECT ms.*, m.name 
                                FROM member_seat ms
                                JOIN member m ON ms.member_id = m.member_id");
        while($row = $assign->fetch_assoc()){
            echo "<tr>
                    <td>{$row['assign_id']}</td>
                    <td>{$row['name']}</td>
                    <td>{$row['start_date']}</td>
                    <td>{$row['end_date']}</td>
                  </tr>";
        }
        ?>
    </table>

</div>

<?php include("../includes/footer.php"); ?>
