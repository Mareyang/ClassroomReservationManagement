<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include "database.php";
ob_start();
header('Content-Type: application/json');

$sql = "
    SELECT 
        r.reservationId,
        r.reservationDate,
        r.startTime,
        r.endTime,
        r.purpose,
        r.status,
        r.approvedBy,
        COALESCE(c.roomName, '(Room removed)') AS roomName,
        CASE
            WHEN r.requestedByProfessor IS NOT NULL THEN CONCAT(p.firstName, ' ', p.lastName)
            WHEN r.requestedByStudent   IS NOT NULL THEN CONCAT(s.firstName, ' ', s.lastName)
            ELSE 'Unknown'
        END AS requesterName,
        CASE
            WHEN r.requestedByProfessor IS NOT NULL THEN 'Professor'
            WHEN r.requestedByStudent   IS NOT NULL THEN 'Student'
            ELSE 'Unknown'
        END AS requesterType
    FROM reservation r
    LEFT JOIN classroom c ON c.roomId = r.roomId
    LEFT JOIN professor p ON p.professorId = r.requestedByProfessor
    LEFT JOIN student   s ON s.studentId   = r.requestedByStudent
    ORDER BY r.reservationDate DESC";

$result = mysqli_query($connection, $sql);

if (!$result) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => mysqli_error($connection)]);
    exit();
}

$rows = [];
while ($row = mysqli_fetch_assoc($result)) {
    $rows[] = [
        'id'        => $row['reservationId'],
        'requestor' => htmlspecialchars($row['requesterName']),
        'type'      => htmlspecialchars($row['requesterType']),
        'room'      => htmlspecialchars($row['roomName']),
        'date'      => htmlspecialchars($row['reservationDate']),
        'time'      => htmlspecialchars($row['startTime']) . ' - ' . htmlspecialchars($row['endTime']),
        'purpose'   => htmlspecialchars($row['purpose']),
        'status'    => strtoupper($row['status']),
        'approvedBy'=> $row['approvedBy'],
    ];
}

ob_end_clean();
echo json_encode(['success' => true, 'data' => $rows]);
?>
