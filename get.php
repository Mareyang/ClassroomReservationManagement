<?php 
    include "database.php";
    include "login2.php";
    include "sync_rooms.php";

    $queryRooms = "SELECT * FROM classroom;";
    $sqlRooms = mysqli_query($connection, $queryRooms);

    $queryAvailableRooms = "SELECT roomName, roomFloor FROM classroom WHERE roomStatus = 'Available' ORDER BY roomFloor, roomName";
    $sqlAvailableRooms = mysqli_query($connection, $queryAvailableRooms);

    $querySections = "SELECT * FROM section;";
    $sqlSections = mysqli_query($connection, $querySections);

    // ── Professor session ──────────────────────────────────────────────
    $professorId = isset($_SESSION['professorId']) ? $_SESSION['professorId'] : 0;

    // Professor profile
    $sqlProfProfile = mysqli_query($connection, "SELECT professorId, employeeNumber, professorType, firstName, middleName, lastName FROM professor WHERE professorId = '$professorId'");
    $profProfile = $sqlProfProfile ? mysqli_fetch_assoc($sqlProfProfile) : [];

    // Professor reservations (My Reservations + My Schedule)
    $queryPSchedule = "SELECT reservation.reservationId, classroom.roomName, professor.lastName,
        reservation.reservationDate, reservation.startTime, reservation.endTime,
        reservation.status, reservation.approvedBy
        FROM reservation
        INNER JOIN classroom ON classroom.roomId = reservation.roomId
        INNER JOIN professor ON professor.professorId = reservation.requestedByProfessor
        WHERE reservation.requestedByProfessor = '$professorId'
        ORDER BY reservation.reservationDate DESC";
    $sqlPSchedule = mysqli_query($connection, $queryPSchedule);
    if (!$sqlPSchedule) { die("SQL Error: " . mysqli_error($connection)); }

    // Professor dashboard counts
    $sqlPApproved = mysqli_query($connection, "SELECT COUNT(*) AS cnt FROM reservation WHERE requestedByProfessor = '$professorId' AND status = 'Approved'");
    $sqlPPending  = mysqli_query($connection, "SELECT COUNT(*) AS cnt FROM reservation WHERE requestedByProfessor = '$professorId' AND status = 'Pending'");
    $sqlPRejected = mysqli_query($connection, "SELECT COUNT(*) AS cnt FROM reservation WHERE requestedByProfessor = '$professorId' AND status = 'Rejected'");

    $countPApproved = mysqli_fetch_assoc($sqlPApproved)['cnt'];
    $countPPending  = mysqli_fetch_assoc($sqlPPending)['cnt'];
    $countPRejected = mysqli_fetch_assoc($sqlPRejected)['cnt'];

    // Professor reservations rows for My Reservations tab
    $queryPReservations = "SELECT reservation.reservationId, classroom.roomName,
        reservation.reservationDate, reservation.startTime, reservation.endTime,
        reservation.purpose, reservation.status
        FROM reservation
        INNER JOIN classroom ON classroom.roomId = reservation.roomId
        WHERE reservation.requestedByProfessor = '$professorId'
        ORDER BY reservation.reservationDate DESC";
        
    $sqlPReservations = mysqli_query($connection, $queryPReservations);
    if (!$sqlPReservations) { die("SQL Error: " . mysqli_error($connection)); }

    // ── Student session ────────────────────────────────────────────────
    $studentId = isset($_SESSION['studentId']) ? $_SESSION['studentId'] : 0;

    // Student profile
    $sqlStudentProfile = mysqli_query($connection, "SELECT studentNumber, firstName, middleName, lastName FROM student WHERE studentId = '$studentId'");
    $studentProfile = $sqlStudentProfile ? mysqli_fetch_assoc($sqlStudentProfile) : [];

    // Student reservations for My Reservations tab
    $queryStudentReservations = "SELECT reservation.reservationId, classroom.roomName,
        reservation.reservationDate, reservation.startTime, reservation.endTime,
        reservation.purpose, reservation.status, reservation.approvedBy
        FROM reservation
        INNER JOIN classroom ON classroom.roomId = reservation.roomId
        WHERE reservation.requestedByStudent = '$studentId'
        ORDER BY reservation.reservationDate DESC";
    $sqlStudentReservations = mysqli_query($connection, $queryStudentReservations);
    if (!$sqlStudentReservations) { die("SQL Error: " . mysqli_error($connection)); }

    // Student schedule (My Schedule tab — same data, kept for compatibility)
    $queryStudentSchedule = "SELECT reservation.reservationId,
        reservation.roomId, reservation.requestedByStudent,
        reservation.reservationDate, reservation.startTime, reservation.endTime,
        reservation.status, reservation.approvedBy
        FROM reservation
        WHERE requestedByStudent = '$studentId'";
    $sqlStudentSchedule = mysqli_query($connection, $queryStudentSchedule);
    if (!$sqlStudentSchedule) { die("SQL Error: " . mysqli_error($connection)); }

    // Student dashboard counts
    $sqlSApproved = mysqli_query($connection, "SELECT COUNT(*) AS cnt FROM reservation WHERE requestedByStudent = '$studentId' AND status = 'Approved'");
    $sqlSPending  = mysqli_query($connection, "SELECT COUNT(*) AS cnt FROM reservation WHERE requestedByStudent = '$studentId' AND status = 'Pending'");
    $sqlSRejected = mysqli_query($connection, "SELECT COUNT(*) AS cnt FROM reservation WHERE requestedByStudent = '$studentId' AND status = 'Rejected'");

    $countSApproved = mysqli_fetch_assoc($sqlSApproved)['cnt'];
    $countSPending  = mysqli_fetch_assoc($sqlSPending)['cnt'];
    $countSRejected = mysqli_fetch_assoc($sqlSRejected)['cnt'];

    // ── Admin — Students & Teachers directory ─────────────────────────
    $sqlAdminStudents = mysqli_query($connection,
        "SELECT s.studentNumber, s.firstName, s.middleName, s.lastName,
                IFNULL(section.sectionCode, '—') AS sectionName
         FROM student s
         LEFT JOIN section section ON section.sectionId = s.sectionId
         ORDER BY s.lastName, s.firstName");

    $adminStudents = [];
    if ($sqlAdminStudents) {
        while ($row = mysqli_fetch_assoc($sqlAdminStudents)) {
            $adminStudents[] = [
                'studentNumber' => $row['studentNumber'],
                'fullName'      => trim($row['firstName'] . ' ' .
                                   ($row['middleName'] ? $row['middleName'] . ' ' : '') .
                                   $row['lastName']),
                'section'       => $row['sectionName'],
            ];
        }
    }
    $queryAllReservations = "
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
    $sqlAllReservations = mysqli_query($connection, $queryAllReservations);
    if (!$sqlAllReservations) { die("SQL Error: " . mysqli_error($connection)); }

    // Admin dashboard counts
    $sqlAdminTotal    = mysqli_query($connection, "SELECT COUNT(*) AS cnt FROM reservation");
    $sqlAdminApproved = mysqli_query($connection, "SELECT COUNT(*) AS cnt FROM reservation WHERE status = 'Approved'");
    $sqlAdminPending  = mysqli_query($connection, "SELECT COUNT(*) AS cnt FROM reservation WHERE status = 'Pending'");
    $sqlAdminRejected = mysqli_query($connection, "SELECT COUNT(*) AS cnt FROM reservation WHERE status = 'Rejected'");

    $countAdminTotal    = mysqli_fetch_assoc($sqlAdminTotal)['cnt'];
    $countAdminApproved = mysqli_fetch_assoc($sqlAdminApproved)['cnt'];
    $countAdminPending  = mysqli_fetch_assoc($sqlAdminPending)['cnt'];
    $countAdminRejected = mysqli_fetch_assoc($sqlAdminRejected)['cnt'];

?>