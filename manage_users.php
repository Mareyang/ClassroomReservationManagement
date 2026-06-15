<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include "database.php";

header('Content-Type: application/json');

$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');

// ── CREATE STUDENT ────────────────────────────────────────────────────
if ($action === 'addStudent') {
    $studentNumber = mysqli_real_escape_string($connection, trim($_POST['studentNumber']));
    $password      = mysqli_real_escape_string($connection, $_POST['password']);
    $firstName     = mysqli_real_escape_string($connection, trim($_POST['firstName']));
    $middleName    = !empty($_POST['middleName']) ? "'" . mysqli_real_escape_string($connection, trim($_POST['middleName'])) . "'" : "NULL";
    $lastName      = mysqli_real_escape_string($connection, trim($_POST['lastName']));
    $sectionId     = intval($_POST['sectionId']);

    $check = mysqli_query($connection, "SELECT studentId FROM student WHERE studentNumber = '$studentNumber'");
    if (mysqli_num_rows($check) > 0) { echo json_encode(['success' => false, 'message' => 'Student number already exists.']); exit(); }

    $result = mysqli_query($connection,
        "INSERT INTO student (studentNumber, password, firstName, middleName, lastName, sectionId)
         VALUES ('$studentNumber', '$password', '$firstName', $middleName, '$lastName', $sectionId)");
    echo $result ? json_encode(['success' => true]) : json_encode(['success' => false, 'message' => mysqli_error($connection)]);
    exit();
}

// ── EDIT STUDENT ──────────────────────────────────────────────────────
if ($action === 'editStudent') {
    $studentId     = intval($_POST['studentId']);
    $studentNumber = mysqli_real_escape_string($connection, trim($_POST['studentNumber']));
    $firstName     = mysqli_real_escape_string($connection, trim($_POST['firstName']));
    $middleName    = mysqli_real_escape_string($connection, trim($_POST['middleName']));
    $lastName      = mysqli_real_escape_string($connection, trim($_POST['lastName']));

    $result = mysqli_query($connection,
        "UPDATE student SET studentNumber='$studentNumber', firstName='$firstName',
         middleName='$middleName', lastName='$lastName' WHERE studentId=$studentId");
    echo $result ? json_encode(['success' => true]) : json_encode(['success' => false, 'message' => mysqli_error($connection)]);
    exit();
}

// ── CREATE PROFESSOR ──────────────────────────────────────────────────
if ($action === 'addProfessor') {
    $employeeNumber = mysqli_real_escape_string($connection, trim($_POST['employeeNumber']));
    $password       = mysqli_real_escape_string($connection, $_POST['password']);
    $firstName      = mysqli_real_escape_string($connection, trim($_POST['firstName']));
    $middleName     = !empty($_POST['middleName']) ? "'" . mysqli_real_escape_string($connection, trim($_POST['middleName'])) . "'" : "NULL";
    $lastName       = mysqli_real_escape_string($connection, trim($_POST['lastName']));
    $professorType  = mysqli_real_escape_string($connection, $_POST['professorType']);

    $check = mysqli_query($connection, "SELECT professorId FROM professor WHERE employeeNumber = '$employeeNumber'");
    if (mysqli_num_rows($check) > 0) { echo json_encode(['success' => false, 'message' => 'Employee number already exists.']); exit(); }

    $result = mysqli_query($connection,
        "INSERT INTO professor (employeeNumber, password, firstName, middleName, lastName, professorType)
         VALUES ('$employeeNumber', '$password', '$firstName', $middleName, '$lastName', '$professorType')");
    echo $result ? json_encode(['success' => true]) : json_encode(['success' => false, 'message' => mysqli_error($connection)]);
    exit();
}

// ── EDIT PROFESSOR ────────────────────────────────────────────────────
if ($action === 'editProfessor') {
    $professorId    = intval($_POST['professorId']);
    $employeeNumber = mysqli_real_escape_string($connection, trim($_POST['employeeNumber']));
    $firstName      = mysqli_real_escape_string($connection, trim($_POST['firstName']));
    $middleName     = mysqli_real_escape_string($connection, trim($_POST['middleName']));
    $lastName       = mysqli_real_escape_string($connection, trim($_POST['lastName']));
    $professorType  = mysqli_real_escape_string($connection, $_POST['professorType']);

    $result = mysqli_query($connection,
        "UPDATE professor SET employeeNumber='$employeeNumber', firstName='$firstName',
         middleName='$middleName', lastName='$lastName', professorType='$professorType'
         WHERE professorId=$professorId");
    echo $result ? json_encode(['success' => true]) : json_encode(['success' => false, 'message' => mysqli_error($connection)]);
    exit();
}

// ── CREATE ADMIN ──────────────────────────────────────────────────────
if ($action === 'addAdmin') {
    $username   = mysqli_real_escape_string($connection, trim($_POST['username']));
    $password   = mysqli_real_escape_string($connection, $_POST['password']);
    $firstName  = mysqli_real_escape_string($connection, trim($_POST['firstName']));
    $middleName = !empty($_POST['middleName']) ? "'" . mysqli_real_escape_string($connection, trim($_POST['middleName'])) . "'" : "NULL";
    $lastName   = mysqli_real_escape_string($connection, trim($_POST['lastName']));

    $check = mysqli_query($connection, "SELECT admin_id FROM admin WHERE username = '$username'");
    if (mysqli_num_rows($check) > 0) { echo json_encode(['success' => false, 'message' => 'Username already exists.']); exit(); }

    $result = mysqli_query($connection,
        "INSERT INTO admin (username, password, firstName, middleName, lastName)
         VALUES ('$username', '$password', '$firstName', $middleName, '$lastName')");
    echo $result ? json_encode(['success' => true]) : json_encode(['success' => false, 'message' => mysqli_error($connection)]);
    exit();
}

// ── GET SECTIONS (for dropdown) ───────────────────────────────────────
if ($action === 'getSections') {
    $result = mysqli_query($connection, "SELECT sectionId, sectionName FROM section ORDER BY sectionName");
    $rows = [];
    if ($result) { while ($row = mysqli_fetch_assoc($result)) $rows[] = $row; }
    echo json_encode(['success' => true, 'data' => $rows]);
    exit();
}

echo json_encode(['success' => false, 'message' => 'Unknown action.']);
?>
