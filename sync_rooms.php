<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include "database.php";

$now  = date('Y-m-d H:i:s');
$today = date('Y-m-d');
$time  = date('H:i:s');

// Mark rooms as Occupied if there is an approved reservation happening RIGHT NOW
$sqlOccupy = "UPDATE classroom SET roomStatus = 'Occupied'
              WHERE roomId IN (
                  SELECT roomId FROM reservation
                  WHERE status = 'Approved'
                  AND reservationDate = '$today'
                  AND startTime <= '$time'
                  AND endTime > '$time'
              )";
mysqli_query($connection, $sqlOccupy);

// Mark rooms back to Available when no active approved reservation exists right now
// Only reset rooms that are currently Occupied (not ones manually set to Maintenance)
$sqlRelease = "UPDATE classroom SET roomStatus = 'Available'
               WHERE roomStatus = 'Occupied'
               AND roomId NOT IN (
                   SELECT roomId FROM reservation
                   WHERE status = 'Approved'
                   AND reservationDate = '$today'
                   AND startTime <= '$time'
                   AND endTime > '$time'
               )";
mysqli_query($connection, $sqlRelease);
?>
