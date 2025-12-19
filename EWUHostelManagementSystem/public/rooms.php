<?php
session_start();
include("../config/db.php");
include("../includes/header.php");

// FETCH LOGGED USER
$currentUserEmail = $_SESSION["user"] ?? null;

$sql = "SELECT member_id, role_id FROM member WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $currentUserEmail);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$userRole = $user["role_id"];
$userId   = $user["member_id"];


// =======================================================
//  ADMIN + MANAGER: ADD ROOM (with available seats init)
// =======================================================
if (isset($_POST["add_room"]) && ($userRole == 1 || $userRole == 3)) {

    $room_number = $_POST["room_number"];
    $total_seats = $_POST["total_seats"];

    $q = "INSERT INTO room(room_number, total_seats, available_seats)
          VALUES (?, ?, ?)";
    $st = $conn->prepare($q);
    $st->bind_param("iii", $room_number, $total_seats, $total_seats);
    $st->execute();

    echo "<script>alert('Room added successfully!');</script>";
}


// =======================================================
//  ADMIN + MANAGER: EDIT ROOM
// =======================================================
if (isset($_POST["edit_room"]) && ($userRole == 1 || $userRole == 3)) {

    $roomId      = $_POST["room_id"];
    $newNumber   = $_POST["room_number"];
    $newTotal    = $_POST["total_seats"];

    // count how many seats already booked
    $booked = $conn->query("
        SELECT COUNT(*) AS total 
        FROM member_seat 
        WHERE room_id = $roomId
    ")->fetch_assoc()["total"];

    // available seats = new total - booked
    $newAvailable = max(0, $newTotal - $booked);

    $update = $conn->prepare("
        UPDATE room 
        SET room_number = ?, total_seats = ?, available_seats = ?
        WHERE room_id = ?
    ");
    $update->bind_param("iiii", $newNumber, $newTotal, $newAvailable, $roomId);
    $update->execute();

    echo "<script>alert('Room updated successfully!');</script>";
}


// =======================================================
// MEMBER: BOOK SEAT
// =======================================================
if (isset($_GET["book"]) && $userRole == 2) {

    $roomId = $_GET["book"];

    // already booked?
    $check = $conn->query("
        SELECT * FROM member_seat 
        WHERE member_id = $userId AND start_date IS NOT NULL
    ");

    if ($check->num_rows > 0) {
        echo "<script>alert('You already booked a seat!');</script>";
        header("Location: rooms.php");
        exit();
    }

    $room = $conn->query("SELECT available_seats FROM room WHERE room_id = $roomId")->fetch_assoc();

    if ($room["available_seats"] <= 0) {
        echo "<script>alert('No seats available!');</script>";
        header("Location: rooms.php");
        exit();
    }

    // assign seat
    $assign = $conn->prepare("
        INSERT INTO member_seat(member_id, room_id, start_date)
        VALUES (?, ?, CURDATE())
    ");
    $assign->bind_param("ii", $userId, $roomId);
    $assign->execute();

    // decrease available seats
    $conn->query("UPDATE room SET available_seats = available_seats - 1 WHERE room_id = $roomId");

    echo "<script>alert('Seat booked successfully!');</script>";
    header("Location: rooms.php");
    exit();
}

?>

<div class="container mt-4">
    <h2 class="mb-4">Rooms</h2>

    <!-- ADD ROOM FORM -->
    <?php if ($userRole == 1 || $userRole == 3): ?>
        <form method="POST" class="card p-3">
            <h4>Add Room</h4>

            <input name="room_number" class="form-control mt-2" placeholder="Room Number" required>
            <input name="total_seats" class="form-control mt-2" placeholder="Total Seats" required>

            <button class="btn btn-success mt-3" name="add_room">Add Room</button>
        </form>
    <?php endif; ?>


    <table class="table table-bordered mt-4">
        <thead>
            <tr>
                <th>Room ID</th>
                <th>Room Number</th>
                <th>Total Seats</th>
                <th>Available Seats</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody>
        <?php
        $rooms = $conn->query("SELECT * FROM room");

        $alreadyBooked = $conn->query("
            SELECT * FROM member_seat
            WHERE member_id = $userId AND start_date IS NOT NULL
        ")->num_rows > 0;

        while ($row = $rooms->fetch_assoc()):
        ?>
            <tr>
                <td><?= $row["room_id"]; ?></td>
                <td><?= $row["room_number"]; ?></td>
                <td><?= $row["total_seats"]; ?></td>
                <td><?= $row["available_seats"]; ?></td>

                <td>
                    <?php if ($userRole == 2): ?>
                        <!-- MEMBER ACTIONS -->
                        <?php if ($alreadyBooked): ?>
                            <span class="text-primary">Already Booked</span>

                        <?php elseif ($row["available_seats"] > 0): ?>
                            <a href="rooms.php?book=<?= $row['room_id'] ?>" 
                               class="btn btn-primary btn-sm">Book Seat</a>

                        <?php else: ?>
                            <span class="text-danger">Not Available</span>
                        <?php endif; ?>

                    <?php else: ?>
                        <!-- ADMIN + MANAGER -->
                        <button class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                data-bs-target="#editModal<?= $row['room_id'] ?>">
                                Edit
                        </button>
                    <?php endif; ?>
                </td>
            </tr>


            <!-- EDIT ROOM MODAL -->
            <div class="modal fade" id="editModal<?= $row['room_id'] ?>">
                <div class="modal-dialog">
                    <div class="modal-content p-3">

                        <form method="POST">
                            <h4>Edit Room</h4>

                            <input type="hidden" name="room_id" value="<?= $row['room_id'] ?>">

                            <input name="room_number" class="form-control mt-2"
                                   value="<?= $row['room_number'] ?>" required>

                            <input name="total_seats" class="form-control mt-2"
                                   value="<?= $row['total_seats'] ?>" required>

                            <button name="edit_room" class="btn btn-success mt-3">Save Changes</button>
                        </form>

                    </div>
                </div>
            </div>

        <?php endwhile; ?>
        </tbody>
    </table>

</div>

<?php include("../includes/footer.php"); ?>
