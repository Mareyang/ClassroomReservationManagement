<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include "database.php";

header('Content-Type: application/json');

$type = isset($_POST['type']) ? $_POST['type'] : '';
$id   = intval($_POST['id']);

if ($type === 'student') {
    $result = mysqli_query($connection, "DELETE FROM student WHERE studentId = $id");
} elseif ($type === 'professor') {
    $result = mysqli_query($connection, "DELETE FROM professor WHERE professorId = $id");
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid user type.']);
    exit();
}

if ($result) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => mysqli_error($connection)]);
}
?>
