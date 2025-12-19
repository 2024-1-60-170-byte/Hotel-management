<?php
session_start();
include("../config/db.php");
include("../includes/header.php");

// Fetch logged in user info
$currentUserEmail = $_SESSION["user"] ?? null;

$sql = "SELECT member_id, role_id FROM member WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $currentUserEmail);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$userRole = $user["role_id"];
$userId   = $user["member_id"];

?>

<div class="container mt-4">
    <h2 class="mb-4">Seat Information</h2>

    <?php
    // Check if user already booked a seat
    $checkSeat = $conn->query("
        SELECT member_seat.*, room.room_number 
        FROM member_seat 
        JOIN room ON member_seat.room_id = room.room_id
        WHERE member_id = $userId AND start_date IS NOT NULL
    ");
    ?>

    <!-- MEMBER VIEW -->
    <?php if ($userRole == 2): ?>

        <?php if ($checkSeat->num_rows == 0): ?>

            <div class="alert alert-warning">
                You have not booked any seat yet.<br>
                Please go to <a href="rooms.php" class="btn btn-primary btn-sm">Rooms</a> to book.
            </div>

        <?php else: 
            $mySeat = $checkSeat->fetch_assoc();
        ?>

            <div class="card p-3">
                <h4>Your Booked Seat</h4>
                <p><strong>Room Number:</strong> <?= $mySeat["room_number"]; ?></p>
                <p><strong>Start Date:</strong> <?= $mySeat["start_date"]; ?></p>
                <p><strong>Status:</strong> <span class="text-success">Active</span></p>
            </div>

        <?php endif; ?>

    <?php else: ?>

        <!-- ADMIN + MANAGER VIEW -->
        <table class="table table-bordered mt-4">
            <thead>
                <tr>
                    <th>Assign ID</th>
                    <th>Member Name</th>
                    <th>Room Number</th>
                    <th>Start Date</th>
                </tr>
            </thead>

            <tbody>
            <?php
            $allSeats = $conn->query("
                SELECT member_seat.*, member.name, room.room_number
                FROM member_seat
                JOIN member ON member.member_id = member_seat.member_id
                JOIN room ON room.room_id = member_seat.room_id
                ORDER BY assign_id DESC
            ");

            while ($row = $allSeats->fetch_assoc()):
            ?>
                <tr>
                    <td><?= $row["assign_id"]; ?></td>
                    <td><?= $row["name"]; ?></td>
                    <td><?= $row["room_number"]; ?></td>
                    <td><?= $row["start_date"]; ?></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>

    <?php endif; ?>

</div>

<?php include("../includes/footer.php"); ?>
