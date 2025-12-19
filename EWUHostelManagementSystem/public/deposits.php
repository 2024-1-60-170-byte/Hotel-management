<?php 
include("../config/db.php");
include("../includes/header.php");

if(isset($_POST["add_deposit"])){
    $date = $_POST["date"];
    $amount = $_POST["amount"];

    $sql = "INSERT INTO deposit(date, amount) VALUES(?,?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sd", $date, $amount);
    $stmt->execute();
}
?>

<div class="container mt-4">
    <h2>Deposits</h2>

    <form method="POST" class="card p-3 mt-3">
        <h4>Add Deposit</h4>
        <input type="date" name="date" class="form-control mt-2" required>
        <input name="amount" class="form-control mt-2" placeholder="Amount" required>
        <button class="btn btn-success mt-3" name="add_deposit">Add Deposit</button>
    </form>

    <table class="table table-bordered mt-4">
        <tr>
            <th>ID</th><th>Date</th><th>Amount</th>
        </tr>
        <?php 
        $dep = $conn->query("SELECT * FROM deposit");
        while($row = $dep->fetch_assoc()){
            echo "<tr>
                    <td>{$row['deposit_id']}</td>
                    <td>{$row['date']}</td>
                    <td>{$row['amount']}</td>
                  </tr>";
        }
        ?>
    </table>
</div>

<?php include("../includes/footer.php"); ?>
