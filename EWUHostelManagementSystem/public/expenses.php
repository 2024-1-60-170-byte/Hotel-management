<?php
session_start();
include("../config/db.php");
include("../includes/header.php");

// Logged in user
$currentUserEmail = $_SESSION["user"] ?? null;

$sql = "SELECT member_id, role_id FROM member WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $currentUserEmail);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$userId   = $user["member_id"];
$userRole = $user["role_id"];


// ===================================================================
// ADD EXPENSE (ADMIN + MANAGER ONLY)
// ===================================================================
if (isset($_POST["add_expense"]) && ($userRole == 1 || $userRole == 3)) {

    $utility     = $_POST["utility"];
    $food        = $_POST["food"];
    $market      = $_POST["market"];
    $amount      = $_POST["amount"];
    $description = $_POST["description"];
    $memberId    = $_POST["member_id"];

    $sql = "
        INSERT INTO expense (utility, food, market, amount, description, member_id, paid_status)
        VALUES (?, ?, ?, ?, ?, ?, 'No')
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssisi", $utility, $food, $market, $amount, $description, $memberId);
    $stmt->execute();
}


// ===================================================================
// UPDATE EXPENSE STATUS (ADMIN + MANAGER ONLY)
// ===================================================================
if (isset($_GET["pay"]) && ($userRole == 1 || $userRole == 3)) {

    $id = $_GET["pay"];

    $sql = "UPDATE expense SET paid_status='Yes' WHERE expense_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: expenses.php");
    exit();
}


// ===================================================================
// DELETE EXPENSE (ADMIN + MANAGER ONLY)
// ===================================================================
if (isset($_GET["delete"]) && ($userRole == 1 || $userRole == 3)) {

    $id = $_GET["delete"];

    $sql = "DELETE FROM expense WHERE expense_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: expenses.php");
    exit();
}

?>

<div class="container mt-4">
    <h2 class="mb-4">Expenses</h2>


    <!-- ADD FORM (Admin + Manager only) -->
    <?php if ($userRole == 1 || $userRole == 3): ?>
    <form method="POST" class="card p-3 mt-4 mb-4">
        <h4>Add Expense</h4>

        <input name="utility" class="form-control mt-2" placeholder="Utility" required>
        <input name="food" class="form-control mt-2" placeholder="Food" required>
        <input name="market" class="form-control mt-2" placeholder="Market" required>
        <input name="amount" class="form-control mt-2" placeholder="Amount" required>
        <input name="description" class="form-control mt-2" placeholder="Description" required>

        <select name="member_id" class="form-control mt-2" required>
            <option disabled selected>Select Member</option>
            <?php
            $members = $conn->query("SELECT * FROM member");
            while ($m = $members->fetch_assoc()) {
                echo "<option value='{$m['member_id']}'>{$m['name']} ({$m['email']})</option>";
            }
            ?>
        </select>

        <button class="btn btn-info mt-3" name="add_expense">Add Expense</button>
    </form>
    <?php endif; ?>


    <!-- EXPENSE LIST TABLE -->
    <table class="table table-bordered mt-4">
        <thead>
            <tr>
                <th>ID</th>
                <th>Utility</th>
                <th>Food</th>
                <th>Market</th>
                <th>Amount</th>
                <th>Description</th>
                <th>Member</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody>
        <?php
        // MEMBER only sees his own expenses
        if ($userRole == 2) {
            $sql = "
                SELECT expense.*, member.name 
                FROM expense
                JOIN member ON member.member_id = expense.member_id
                WHERE expense.member_id = {$userId}
                ORDER BY expense_id DESC
            ";
        } 
        // Admin + Manager see all
        else {
            $sql = "
                SELECT expense.*, member.name 
                FROM expense
                JOIN member ON member.member_id = expense.member_id
                ORDER BY expense_id DESC
            ";
        }

        $data = $conn->query($sql);

        while ($row = $data->fetch_assoc()):
        ?>

            <tr>
                <td><?= $row["expense_id"] ?></td>
                <td><?= $row["utility"] ?></td>
                <td><?= $row["food"] ?></td>
                <td><?= $row["market"] ?></td>
                <td><?= $row["amount"] ?></td>
                <td><?= $row["description"] ?></td>
                <td><?= $row["name"] ?></td>

                <td>
                    <?php if ($row["paid_status"] == "Yes"): ?>
                        <span class="text-success">Paid</span>
                    <?php else: ?>
                        <span class="text-danger">Not Paid</span>
                    <?php endif; ?>
                </td>

                <td>
                    <?php if ($userRole == 1 || $userRole == 3): ?>

                        <?php if ($row["paid_status"] == "No"): ?>
                            <a href="expenses.php?pay=<?= $row['expense_id']; ?>"
                               class="btn btn-primary btn-sm">Pay</a>
                        <?php endif; ?>

                        <a href="expenses.php?delete=<?= $row['expense_id']; ?>"
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('Delete this expense?');">Delete</a>

                    <?php else: ?>
                        No Action
                    <?php endif; ?>
                </td>
            </tr>

        <?php endwhile; ?>
        </tbody>
    </table>

</div>

<?php include("../includes/footer.php"); ?>
