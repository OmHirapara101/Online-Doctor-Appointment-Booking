<?php

    session_start();

    if(isset($_SESSION["user"])){
        if(($_SESSION["user"])=="" or $_SESSION['usertype']!='p'){
            header("location: ../login.php");
        }else{
            $useremail=$_SESSION["user"];
        }

    }else{
        header("location: ../login.php");
    }
    

    include("../connection.php");
    $sqlmain= "select * from patient where pemail=?";
    $stmt = $database->prepare($sqlmain);
    $stmt->bind_param("s",$useremail);
    $stmt->execute();
    $userrow = $stmt->get_result();
    $userfetch=$userrow->fetch_assoc();
    $userid= $userfetch["pid"];
    $username=$userfetch["pname"];

    // Initialize error message variable
    $error_message = "";
    $show_error = false;

    if($_POST){
        if(isset($_POST["booknow"])){
            $apponum=$_POST["apponum"];
            $scheduleid=$_POST["scheduleid"];
            $date=$_POST["date"];

            // CHECK FOR DUPLICATE BOOKING FIRST
            $sql_check_duplicate = "SELECT * FROM appointment WHERE pid = ? AND scheduleid = ?";
            $stmt_check = $database->prepare($sql_check_duplicate);
            $stmt_check->bind_param("ii", $userid, $scheduleid);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();
            
            if($result_check->num_rows > 0) {
                // Store error message instead of showing alert
                $error_message = "You have already booked this session! Duplicate bookings are not allowed.";
                $show_error = true;
            } else {
                $sql_count = "SELECT nop FROM schedule WHERE scheduleid = $scheduleid";
                $stmt_count = $database->prepare($sql_count);
                $stmt_count->execute();
                $result_count = $stmt_count->get_result();
                $row_count = $result_count->fetch_assoc();
        
                $booked_count = $row_count['nop'];
                
                if ($apponum <= $booked_count)
                {
                    $sql2="insert into appointment(pid,apponum,scheduleid,appodate) values ($userid,$apponum,$scheduleid,'$date')";
                    $result= $database->query($sql2);
                    header("location: appointment.php?action=booking-added&id=".$apponum."&titleget=none");
                    exit();
                }
                else {
                    $error_message = "Appointment is full. Please choose another slot.";
                    $show_error = true;
                }
            }
        }
    }

    // If there's an error, redirect back to booking page with error message
    if($show_error) {
        header("location: booking.php?id=" . $scheduleid . "&error=" . urlencode($error_message));
        exit();
    }
?>