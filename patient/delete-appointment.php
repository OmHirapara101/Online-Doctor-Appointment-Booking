<?php

session_start();

if(isset($_SESSION["user"])){
    // Change this condition - allow both 'p' (patient) and 'a' (admin)
    if(($_SESSION["user"])=="" or ($_SESSION['usertype']!='p' && $_SESSION['usertype']!='a')){
        header("location: ../login.php");
        exit();
    }

}else{
    header("location: ../login.php");
    exit();
}

if($_GET){
    // Check if 'id' parameter exists (your appointment.php uses 'id' not 'aid')
    if(isset($_GET["id"])){
        $aid = $_GET["id"];
    } elseif(isset($_GET["aid"])) {
        $aid = $_GET["aid"];
    } else {
        header("location: appointment.php");
        exit();
    }
    
    // Import database
    include("../connection.php");
    
    // Verify that this appointment belongs to the logged-in patient (for security)
    if($_SESSION['usertype'] == 'p') {
        $useremail = $_SESSION["user"];
        
        // Get patient ID
        $sql_patient = "SELECT pid FROM patient WHERE pemail = ?";
        $stmt = $database->prepare($sql_patient);
        $stmt->bind_param("s", $useremail);
        $stmt->execute();
        $result = $stmt->get_result();
        $patient = $result->fetch_assoc();
        $patient_id = $patient['pid'];
        
        // Check if appointment belongs to this patient
        $check_sql = "SELECT * FROM appointment WHERE appoid = ? AND pid = ?";
        $check_stmt = $database->prepare($check_sql);
        $check_stmt->bind_param("ii", $aid, $patient_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if($check_result->num_rows == 0) {
            // Appointment doesn't belong to this user
            header("location: appointment.php?error=unauthorized");
            exit();
        }
    }
    
    // Delete the appointment
    $sql = "DELETE FROM appointment WHERE appoid = ?";
    $stmt = $database->prepare($sql);
    $stmt->bind_param("i", $aid);
    
    if($stmt->execute()) {
        // Successful deletion
        header("location: appointment.php?message=deleted");
        exit();
    } else {
        // Error occurred
        header("location: appointment.php?error=delete_failed");
        exit();
    }
} else {
    // No GET parameters
    header("location: appointment.php");
    exit();
}

?>