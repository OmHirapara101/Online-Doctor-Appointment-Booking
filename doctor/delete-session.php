<?php
session_start();

if(isset($_SESSION["user"])){
    if(($_SESSION["user"])=="" or $_SESSION['usertype']!='d'){
        header("location: ../login.php");
    } else {
        $useremail = $_SESSION["user"];
    }
} else {
    header("location: ../login.php");
}

if($_GET){
    include("../connection.php");
    
    $id = $_GET["id"];
    
    // Get doctor ID from session
    $userrow = $database->query("SELECT * FROM doctor WHERE docemail='$useremail'");
    $userfetch = $userrow->fetch_assoc();
    $docid = $userfetch["docid"];
    
    // Check if this session belongs to the doctor
    $check_sql = "SELECT * FROM schedule WHERE scheduleid = '$id' AND docid = '$docid'";
    $check_result = $database->query($check_sql);
    
    if($check_result->num_rows > 0){
        // Check if there are any appointments for this session
        $appointment_check = $database->query("SELECT * FROM appointment WHERE scheduleid = '$id'");
        
        if($appointment_check->num_rows > 0){
            // Has appointments, redirect with error
            header("location: schedule.php?error=Cannot delete session with existing appointments");
        } else {
            // No appointments, safe to delete
            $database->query("DELETE FROM schedule WHERE scheduleid = '$id' AND docid = '$docid'");
            header("location: schedule.php?message=Session deleted successfully");
        }
    } else {
        header("location: schedule.php");
    }
} else {
    header("location: schedule.php");
}
?>