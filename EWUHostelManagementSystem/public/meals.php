<?php
session_start();
include("../config/db.php");
include("../includes/header.php");

// fetch logged user
$currentUserEmail = $_SESSION["user"] ?? null;

$sql = "SELECT member_id, role_id, spent, available FROM member WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $currentUserEmail);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$userRole  = $user["role_id"];
$userId    = $user["member_id"];
$userSpent = $user["spent"];
$userAvail = $user["available"];


// =====================================================
// ADMIN/MANAGER: ADD MEAL
// =====================================================
if (isset($_POST["add_meal"]) && ($userRole == 1 || $userRole == 3)) {

    $type   = $_POST["type"];
    $count  = $_POST["count"];
    $price  = $_POST["price"];
    $member = $_POST["member_id"];

    $total = $price * $count;

    $q = "INSERT INTO meal(type, meal_count, price, total_price, member_id)
          VALUES (?, ?, ?, ?, ?)";
    $st = $conn->prepare($q);
    $st->bind_param("siiii", $type, $count, $price, $total, $member);
    $st->execute();
}


// =====================================================
// MEMBER: PAY -> count-1, total update, member update
// =====================================================
if (isset($_GET["pay"]) && $userRole == 2) {

    $mealId = intval($_GET["pay"]);
    if ($mealId <= 0) {
        header("Location: meals.php");
        exit();
    }

    // fetch meal info
    $meal = $conn->query("SELECT * FROM meal WHERE meal_id = $mealId")->fetch_assoc();

    if (!$meal) {
        header("Location: meals.php");
        exit();
    }

    $type   = $meal["type"];
    $price  = $meal["price"];
    $count  = $meal["meal_count"];
    $member = $meal["member_id"];

    if ($count <= 0) {
        echo "<script>alert('No meals remaining!');</script>";
        header("Location: meals.php");
        exit();
    }

    // new count
    $newCount   = $count - 1;
    $newTotal   = $newCount * $price;

    // update meal record
    if ($newCount > 0) {
        $upMeal = $conn->prepare("
            UPDATE meal
            SET meal_count = ?, total_price = ?
            WHERE meal_id = ?
        ");
        $upMeal->bind_param("iii", $newCount, $newTotal, $mealId);
        $upMeal->execute();
    } else {
        // delete if no count left
        $conn->query("DELETE FROM meal WHERE meal_id = $mealId");
    }

    // update member balance
    $up = $conn->prepare("
        UPDATE member 
        SET spent = spent + ?, available = available - ?
        WHERE member_id = ?
    ");
    $up->bind_param("iii", $price, $price, $userId);
    $up->execute();

    echo "<script>alert('Payment Successful!');</script>";
    header("Location: meals.php");
    exit();
}


// =====================================================
// ADMIN/MANAGER: DELETE MEAL
// =====================================================
if (isset($_GET["delete"]) && ($userRole == 1 || $userRole == 3)) {

    $delId = $_GET["delete"];

    $d = $conn->prepare("DELETE FROM meal WHERE meal_id = ?");
    $d->bind_param("i", $delId);
    $d->execute();

    header("Location: meals.php");
    exit();
}

?>

<div class="container mt-4">
    <h2 class="mb-4">Meals</h2>

    <!-- ADMIN/MANAGER: ADD MEAL -->
    <?php if ($userRole == 1 || $userRole == 3): ?>
    <form method="POST" class="card p-3 mt-3">
        <h4>Add Meal</h4>

        <select name="type" class="form-control mt-2" required>
            <option disabled selected>Select Meal Type</option>
            <option>Breakfast</option>
            <option>Lunch</option>
            <option>Dinner</option>
        </select>

        <input name="count" class="form-control mt-2" placeholder="Meal Count" required>
        <input name="price" class="form-control mt-2" placeholder="Price Per Unit" required>

        <select name="member_id" class="form-control mt-2" required>
            <option disabled selected>Select Member</option>
            <?php
            $mem = $conn->query("SELECT * FROM member");
            while ($m = $mem->fetch_assoc()) {
                echo "<option value='{$m['member_id']}'>{$m['name']}</option>";
            }
            ?>
        </select>

        <button class="btn btn-info mt-3" name="add_meal">Add Meal</button>
    </form>
    <?php endif; ?>


    <!-- MEAL TABLE -->
    <table class="table table-bordered mt-4">
        <thead>
        <tr>
            <th>ID</th>
            <th>Type</th>
            <th>Meals Left</th>
            <th>Price Each</th>
            <th>Total Price</th>
            <th>Member</th>
            <th>Action</th>
        </tr>
        </thead>

        <tbody>
        <?php
        $q = "
            SELECT meal.*, member.name 
            FROM meal
            JOIN member ON member.member_id = meal.member_id
            ORDER BY meal_id DESC
        ";
        $res = $conn->query($q);

        while ($row = $res->fetch_assoc()):
        ?>
            <tr>
                <td><?= $row["meal_id"]; ?></td>
                <td><?= $row["type"]; ?></td>
                <td><?= $row["meal_count"]; ?></td>
                <td><?= $row["price"]; ?></td>
                <td><?= $row["total_price"]; ?></td>
                <td><?= $row["name"]; ?></td>

                <td>
                    <?php if ($userRole == 1 || $userRole == 3): ?>
                        <a href="meals.php?delete=<?= $row['meal_id']; ?>" 
                           class="btn btn-danger btn-sm">Delete</a>

                    <?php elseif ($userRole == 2): ?>
                        <?php if ($row["meal_count"] > 0): ?>
                            <a href="meals.php?pay=<?= $row['meal_id']; ?>" 
                               class="btn btn-primary btn-sm">Pay</a>
                        <?php else: ?>
                            <span class="text-danger">No Meals</span>
                        <?php endif; ?>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

</div>

<?php include("../includes/footer.php"); ?>
