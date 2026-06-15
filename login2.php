<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include "database.php";


if(isset($_POST['Submit'])){

    $username = $_POST['username'];
    $password = $_POST['password'];

    
    $adminUser = "SELECT * FROM admin 
                 WHERE username='$username' 
                 AND password='$password'";

    $adminResult = mysqli_query($connection, $adminUser);

     $professorUser = "SELECT * FROM professor
                    WHERE employeeNumber='$username' 
                    AND password='$password'";

        $professorResult = mysqli_query($connection, $professorUser);

    $studentUser = "SELECT * FROM student
                 WHERE studentNumber='$username' 
                 AND password='$password'";
    $studentResult = mysqli_query($connection, $studentUser);


    if(mysqli_num_rows($adminResult) > 0){
         $user = mysqli_fetch_assoc($adminResult);

        $_SESSION['admin_id'] = $user['admin_id'];
        $_SESSION['username'] = $user['username'];

        echo '<script>alert("Welcome Admin!");</script>';
        echo '<script>window.location.href="admin.php";</script>';

    } 
    else if(mysqli_num_rows($adminResult) < 0){

        echo '<script>alert("Invalid username or password!");</script>';
        echo '<script>window.location.href="login.php";</script>';
    }

    else if (mysqli_num_rows($professorResult) > 0) {
         $user = mysqli_fetch_assoc($professorResult);

        $_SESSION['professorId'] = $user['professorId'];
        $_SESSION['employeeNumber'] = $user['employeeNumber'];


        echo '<script>alert("Login Successful!");</script>';
        echo '<script>window.location.href="professor.php";</script>';
} 
    else if(mysqli_num_rows($professorResult) < 0){
            echo '<script>alert("Invalid username or password!");</script>';
            echo '<script>window.location.href="login.php";</script>';
        } 
        else if(mysqli_num_rows($studentResult) > 0){
             $user = mysqli_fetch_assoc($studentResult);

        $_SESSION['studentId'] = $user['studentId'];
        $_SESSION['studentNumber'] = $user['studentNumber'];
        header("Location: student.php");
        exit();

        echo '<script>alert("Welcome User!");</script>';
        echo '<script>window.location.href="student.php";</script>';

    } else {
        echo '<script>alert("Invalid username or password!");</script>';
        echo '<script>window.location.href="login.php";</script>';
    }
    } 
?>
