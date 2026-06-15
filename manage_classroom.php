<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include "database.php";
ob_start();
header('Content-Type: application/json');

$action = isset($_POST['action']) ? $_POST['action'] : '';

// ── ADD CLASSROOM ─────────────────────────────────────────────────────
if ($action === 'addClassroom') {
    $roomName     = mysqli_real_escape_string($connection, trim($_POST['roomName']));
    $roomFloor    = mysqli_real_escape_string($connection, trim($_POST['roomFloor']));
    $roomCapacity = intval($_POST['roomCapacity']);
    $roomStatus   = mysqli_real_escape_string($connection, trim($_POST['roomStatus']));

    if (empty($roomName) || empty($roomFloor) || $roomCapacity <= 0) {
        echo json_encode(['success' => false, 'message' => 'All fields are required.']);
        exit();
    }

    $result = mysqli_query($connection,
        "INSERT INTO classroom (roomName, roomFloor, roomCapacity, roomStatus)
         VALUES ('$roomName', '$roomFloor', $roomCapacity, '$roomStatus')");

    echo $result
        ? json_encode(['success' => true])
        : json_encode(['success' => false, 'message' => mysqli_error($connection)]);
    exit();
}

// ── EDIT CLASSROOM ────────────────────────────────────────────────────
if ($action === 'editClassroom') {
    $roomId       = intval($_POST['roomId']);
    $roomName     = mysqli_real_escape_string($connection, trim($_POST['roomName']));
    $roomFloor    = mysqli_real_escape_string($connection, trim($_POST['roomFloor']));
    $roomCapacity = intval($_POST['roomCapacity']);
    $roomStatus   = mysqli_real_escape_string($connection, trim($_POST['roomStatus']));

    $result = mysqli_query($connection,
        "UPDATE classroom SET roomName='$roomName', roomFloor='$roomFloor', roomCapacity=$roomCapacity, roomStatus='$roomStatus'
         WHERE roomId=$roomId");

    echo $result
        ? json_encode(['success' => true])
        : json_encode(['success' => false, 'message' => mysqli_error($connection)]);
    exit();
}

// ── DELETE CLASSROOM ──────────────────────────────────────────────────
if ($action === 'deleteClassroom') {
    $roomId = intval($_POST['roomId']);

    // Check if this room has any linked reservations
    $check = mysqli_query($connection, "SELECT COUNT(*) AS cnt FROM reservation WHERE roomId = $roomId");
    $row   = mysqli_fetch_assoc($check);

    if ($row['cnt'] > 0) {
        // Delete linked reservations first, then delete the classroom
        mysqli_query($connection, "DELETE FROM reservation WHERE roomId = $roomId");
    }

    $result = mysqli_query($connection, "DELETE FROM classroom WHERE roomId=$roomId");

    echo $result
        ? json_encode(['success' => true])
        : json_encode(['success' => false, 'message' => mysqli_error($connection)]);
    exit();
}

// ── GET ALL CLASSROOMS ────────────────────────────────────────────────
if ($action === 'getClassrooms') {
    $result = mysqli_query($connection,
        "SELECT roomId, roomName, roomFloor, roomCapacity, roomStatus FROM classroom ORDER BY roomFloor, roomName");
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) $rows[] = $row;
    echo json_encode(['success' => true, 'data' => $rows]);
    exit();
}

echo json_encode(['success' => false, 'message' => 'Unknown action.']);
?>
