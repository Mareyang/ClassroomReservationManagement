<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include "database.php";

// Suppress any output before JSON header
ob_start();

header('Content-Type: application/json');

$result = mysqli_query($connection,
    "SELECT roomId, roomName, roomFloor, roomCapacity, roomStatus FROM classroom ORDER BY roomFloor, roomName");

if (!$result) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => mysqli_error($connection)]);
    exit();
}

$rows = [];
while ($row = mysqli_fetch_assoc($result)) {
    $rows[] = $row;
}

ob_end_clean();
echo json_encode(['success' => true, 'data' => $rows]);
?>
