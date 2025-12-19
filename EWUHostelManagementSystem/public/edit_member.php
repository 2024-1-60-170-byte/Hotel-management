<?php
session_start();
include("../config/db.php");
include("../includes/header.php");

// Get logged user
$currentUserEmail = $_SESSION["user"] ?? null;

$sql = "SELECT member_id, role_id FROM member WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $currentUserEmail);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$userRole = $user["role_id"]; // 1=Admin, 3=Manager

// Get member ID to edit
$editId = $_GET['id'] ?? 0;

$member = $conn->query("SELECT * FROM member WHERE member_id = $editId")->fetch_assoc();

if (!$member) {
    echo "<h3 class='text-danger text-center mt-5'>Invalid Member ID!</h3>";
    include("../includes/footer.php");
    exit();
}

// Manager cannot edit Admin
if ($userRole == 3 && $member["role_id"] == 1) {
    echo "<h3 class='text-danger text-center mt-5'>Manager cannot edit Admin!</h3>";
    include("../includes/footer.php");
    exit();
}


// ===================================================================
// UPDATE MEMBER
// ===================================================================
if (isset($_POST["update_member"])) {

    $name  = $_POST["name"];
    $email = $_POST["email"];
    $phone = $_POST["phone"];

    // Admin can edit everything
    if ($userRole == 1) {
        $password = $_POST["password"];
        $role_id  = $_POST["role_id"];

        $update = $conn->prepare("
            UPDATE member 
            SET name=?, email=?, password=?, phone=?, role_id=? 
            WHERE member_id=?
        ");
        $update->bind_param("ssssii", $name, $email, $password, $phone, $role_id, $editId);
    }

    // Manager can NOT edit password or role
    else if ($userRole == 3) {
        $update = $conn->prepare("
            UPDATE member 
            SET name=?, email=?, phone=? 
            WHERE member_id=?
        ");
        $update->bind_param("sssi", $name, $email, $phone, $editId);
    }

    $update->execute();

    header("Location: members.php");
    exit();
}

?>

<div class="container mt-5">
    <h3>Edit Member</h3>

    <form method="POST" class="card p-4 mt-3">

        <label class="mt-2">Name</label>
        <input name="name" value="<?= $member['name']; ?>" class="form-control" required>

        <label class="mt-2">Email</label>
        <input name="email" value="<?= $member['email']; ?>" class="form-control" required>

        <label class="mt-2">Phone</label>
        <input name="phone" value="<?= $member['phone']; ?>" class="form-control" required>

        <?php if ($userRole == 1): ?>
            <!-- ADMIN ONLY FIELDS -->
            <label class="mt-2">Password</label>
            <input name="password" value="<?= $member['password']; ?>" class="form-control" required>

            <label class="mt-2">Role</label>
            <select name="role_id" class="form-control" required>
                <option value="1" <?= $member["role_id"] == 1 ? "selected" : "" ?>>Admin</option>
                <option value="2" <?= $member["role_id"] == 2 ? "selected" : "" ?>>Member</option>
                <option value="3" <?= $member["role_id"] == 3 ? "selected" : "" ?>>Manager</option>
            </select>
        <?php else: ?>
            <!-- MANAGER ONLY -->
            <label class="mt-2">Password</label>
            <input class="form-control" value="Not Accessible" disabled>

            <label class="mt-2">Role</label>
            <input class="form-control" value="<?= ($member['role_id']==1?'Admin':($member['role_id']==2?'Member':'Manager')); ?>" disabled>
        <?php endif; ?>

        <button name="update_member" class="btn btn-info mt-4">Update Member</button>

    </form>
</div>

<?php include("../includes/footer.php"); ?>
