<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include "database.php";

if (isset($_POST['reservationId']) && isset($_POST['newStatus'])) {

    $reservationId = intval($_POST['reservationId']);
    $newStatus     = $_POST['newStatus'];
    $adminId       = isset($_SESSION['admin_id']) ? intval($_SESSION['admin_id']) : NULL;

    // Only allow valid status values
    if (!in_array($newStatus, ['Approved', 'Rejected'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid status.']);
        exit();
    }

    $approvedBy = $adminId ? $adminId : 'NULL';

    $sql = "UPDATE reservation 
            SET status = '$newStatus', approvedBy = $approvedBy 
            WHERE reservationId = $reservationId";

    $result = mysqli_query($connection, $sql);

    if ($result) {
        // After approving, sync room status immediately
        if ($newStatus === 'Approved') {
            include "sync_rooms.php";
        }
        echo json_encode(['success' => true, 'message' => "Reservation $reservationId updated to $newStatus."]);
    } else {
        echo json_encode(['success' => false, 'message' => mysqli_error($connection)]);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Missing parameters.']);
}
?>
