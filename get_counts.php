<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include "database.php";

header('Content-Type: application/json');

$total    = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) AS cnt FROM reservation"))['cnt'];
$approved = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) AS cnt FROM reservation WHERE status = 'Approved'"))['cnt'];
$pending  = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) AS cnt FROM reservation WHERE status = 'Pending'"))['cnt'];
$rejected = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) AS cnt FROM reservation WHERE status = 'Rejected'"))['cnt'];
$totalRooms     = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) AS cnt FROM classroom"))['cnt'];
$availableRooms = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) AS cnt FROM classroom WHERE roomStatus = 'Available'"))['cnt'];

echo json_encode([
    'total'          => $total,
    'approved'       => $approved,
    'pending'        => $pending,
    'rejected'       => $rejected,
    'totalRooms'     => $totalRooms,
    'availableRooms' => $availableRooms,
]);
?>
