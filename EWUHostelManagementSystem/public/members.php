<?php
session_start();
include("../config/db.php");
include("../includes/header.php");

// Fetch logged-in user info
$currentUserEmail = $_SESSION["user"] ?? null;

$sql = "SELECT member_id, role_id FROM member WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $currentUserEmail);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$userId   = $user["member_id"];
$userRole = $user["role_id"];


// =============================================================
// ADD MEMBER (Admin only) + EMAIL DUPLICATE CHECK + MD5 PASSWORD
// =============================================================
if (isset($_POST["add_member"]) && $userRole == 1) {

    $name    = $_POST["name"];
    $email   = $_POST["email"];
    $pass    = md5($_POST["password"]); // 🔐 MD5 encryption
    $phone   = $_POST["phone"];
    $role_id = $_POST["role_id"];

    // CHECK IF EMAIL ALREADY EXISTS
    $check = $conn->prepare("SELECT member_id FROM member WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo "<script>alert('Email already exists! Please use a different email.');</script>";
    } else {

        $sql = "INSERT INTO member(name, email, password, phone, join_date, role_id)
                VALUES (?, ?, ?, ?, CURDATE(), ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $name, $email, $pass, $phone, $role_id);
        $stmt->execute();

        echo "<script>alert('Member added successfully');</script>";
    }
}


// =============================================================
// DELETE MEMBER (Admin only)
// =============================================================
if (isset($_GET["delete"]) && $userRole == 1) {
    $id = $_GET["delete"];
    $stmt = $conn->prepare("DELETE FROM member WHERE member_id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: members.php");
    exit();
}
?>

<div class="container mt-4">

<h2 class="mb-4">Members</h2>

<!-- ADD MEMBER FORM (Admin only) -->
<?php if ($userRole == 1): ?>
<form method="POST" class="card p-3 mb-4">
    <h4>Add Member</h4>

    <input name="name" class="form-control mt-2" placeholder="Name" required>
    <input name="email" class="form-control mt-2" placeholder="Email" required>
    <input name="password" class="form-control mt-2" placeholder="Password" required>
    <input name="phone" class="form-control mt-2" placeholder="Phone" required>

    <select name="role_id" class="form-control mt-2" required>
        <option disabled selected>Select Role</option>
        <option value="1">Admin</option>
        <option value="2">Member</option>
        <option value="3">Manager</option>
    </select>

    <button class="btn btn-info mt-3" name="add_member">Add Member</button>
</form>
<?php endif; ?>


<!-- MEMBER TABLE -->
<table class="table table-bordered">
<thead>
<tr>
    <th>ID</th>
    <th>Name</th>
    <th>Email</th>
    <th>Password (Hashed)</th>
    <th>Phone</th>
    <th>Role</th>
    <th>Action</th>
</tr>
</thead>

<tbody>
<?php
$members = $conn->query("
    SELECT member.*, role.role_name 
    FROM member 
    JOIN role ON role.role_id = member.role_id
    ORDER BY member_id DESC
");

while ($row = $members->fetch_assoc()):
?>
<tr>
    <td><?= $row["member_id"] ?></td>
    <td><?= $row["name"] ?></td>
    <td><?= $row["email"] ?></td>

    <!-- PASSWORD COLUMN -->
    <td>
        <?php
        if ($userRole == 1) {
            echo htmlspecialchars($row["password"]); // MD5 hash only
        } else {
            echo "Not Accessible";
        }
        ?>
    </td>

    <td><?= $row["phone"] ?></td>
    <td><?= $row["role_name"] ?></td>

    <td>
        <!-- Edit: Admin + Manager -->
        <?php if ($userRole == 1 || $userRole == 3): ?>
            <a href="edit_member.php?id=<?= $row['member_id'] ?>" class="btn btn-primary btn-sm">Edit</a>
        <?php endif; ?>

        <!-- Delete: Admin only -->
        <?php if ($userRole == 1): ?>
            <a href="members.php?delete=<?= $row['member_id'] ?>" 
               class="btn btn-danger btn-sm"
               onclick="return confirm('Delete this member?')">
               Delete
            </a>
        <?php endif; ?>
    </td>

</tr>
<?php endwhile; ?>
</tbody>
</table>

</div>

<?php include("../includes/footer.php"); ?>
