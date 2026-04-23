<?php
session_start();

if(isset($_SESSION["user"])){
    if(($_SESSION["user"])=="" or $_SESSION['usertype']!='d'){
        header("location: ../login.php");
        exit();
    }else{
        $useremail=$_SESSION["user"];
    }
}else{
    header("location: ../login.php");
    exit();
}

//import database
include("../connection.php");
$userrow = $database->query("select * from doctor where docemail='$useremail'");
$userfetch=$userrow->fetch_assoc();
$userid= $userfetch["docid"];
$username=$userfetch["docname"];
$useremail_display = $userfetch["docemail"];
$userphone = $userfetch["doctel"];
$usernic = $userfetch["docnic"];
$specialties = $userfetch["specialties"];
$hospital = $userfetch["hospital"];

// Fetch specialty name from specialties table
$specialty_row = $database->query("SELECT sname FROM specialties WHERE id='$specialties'");
$specialty_fetch = $specialty_row->fetch_assoc();
$specialty_name = $specialty_fetch["sname"];

// Handle doctor photo
if (!empty($userfetch["photo"])) {
    $doctor_photo = "../uploads/doctors/" . $userfetch["photo"];
} else {
    $doctor_photo = "../img/default-doctor.png";
}

// Handle GET parameters BEFORE any HTML output
$show_delete_popup = false;
$show_view_popup = false;
$delete_id = null;
$delete_name = '';
$view_id = null;
$edit_id = null;

if(isset($_GET) && !empty($_GET)){
    if(isset($_GET["id"]) && isset($_GET["action"])){
        $id = $_GET["id"];
        $action = $_GET["action"];
        
        if($action == 'drop'){
            $show_delete_popup = true;
            $delete_id = $id;
            $delete_name = isset($_GET["name"]) ? $_GET["name"] : '';
        } 
        elseif($action == 'view'){
            $show_view_popup = true;
            $view_id = $id;
        }
        elseif($action == 'edit'){
            header("location: edit-session.php?id=$id");
            exit();
        }
    } else {
        header("location: schedule.php");
        exit();
    }
}

// Prepare the main query for displaying sessions
$sqlmain = "SELECT schedule.scheduleid, schedule.title, doctor.docname, schedule.scheduledate, schedule.scheduletime, schedule.nop 
            FROM schedule 
            INNER JOIN doctor ON schedule.docid = doctor.docid 
            WHERE doctor.docid = $userid ";

if($_POST){
    if(!empty($_POST["sheduledate"])){
        $sheduledate = $_POST["sheduledate"];
        $sqlmain .= " AND schedule.scheduledate = '$sheduledate' ";
    }
}

// For view popup data
$view_data = null;
$view_appointments = null;
if($show_view_popup){
    $sqlmain_view = "SELECT schedule.scheduleid, schedule.title, doctor.docname, schedule.scheduledate, schedule.scheduletime, schedule.nop 
                    FROM schedule 
                    INNER JOIN doctor ON schedule.docid = doctor.docid  
                    WHERE schedule.scheduleid = $view_id";
    $view_data = $database->query($sqlmain_view);
    
    $sqlmain_appointments = "SELECT appointment.*, patient.* 
                            FROM appointment 
                            INNER JOIN patient ON patient.pid = appointment.pid 
                            WHERE appointment.scheduleid = $view_id";
    $view_appointments = $database->query($sqlmain_appointments);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/animations.css">  
    <link rel="stylesheet" href="../css/main.css">  
    <link rel="stylesheet" href="../css/admin.css">
        
    <title>Schedule</title>
    <style>
        .popup{
            animation: transitionIn-Y-bottom 0.5s;
        }
        .sub-table{
            animation: transitionIn-Y-bottom 0.5s;
        }
        .add-session-btn {
            background-color: #2f4f68;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            margin-left: 20px;
        }
        .add-session-btn:hover {
            background-color: #1b62b3;
        }
    </style>
</head>
<body>
<?php include("sidebar.php")?>
<div class="dash-body">
    <table border="0" width="100%" style="border-spacing:0;margin:0;padding:0;margin-top:25px;">
        <tr>
            <td width="13%">
                <a href="schedule.php">
                    <button class="login-btn btn-primary-soft btn btn-icon-back" style="padding-top:11px;padding-bottom:11px;margin-left:20px;width:125px">
                        <font class="tn-in-text">Back</font>
                    </button>
                </a>
            </td>
            <td width="60%">
                <p style="font-size:23px;padding-left:12px;font-weight:600;">My Sessions</p>
            </td>
            <td width="15%">
                <p style="font-size:14px;color:rgb(119,119,119);padding:0;margin:0;text-align:right;">
                    Today's Date
                </p>
                <p class="heading-sub12" style="padding:0;margin:0;">
                    <?php 
                    date_default_timezone_set('Asia/Kolkata');
                    $today = date('Y-m-d');
                    echo $today;
                    $list110 = $database->query("select * from schedule where docid=$userid;");
                    ?>
                </p>
            </td>
            <td width="10%">
                <button class="btn-label" style="display:flex;justify-content:center;align-items:center;">
                    <img src="../img/calendar.svg" width="100%">
                </button>
            </td>
        </tr>
        
        <tr>
            <td colspan="4" style="padding-top:10px;">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <p class="heading-main12" style="margin-left:45px;font-size:18px;color:rgb(78, 78, 78)">
                        My Sessions (<?php echo $list110->num_rows; ?>)
                    </p>
                    <a href="add-session.php" class="add-session-btn">
                        + Add New Session
                    </a>
                </div>
            </td>
        </tr>
        
        <tr>
            <td colspan="4" style="padding-top:0px;">
                <center>
                    <table class="filter-container" border="0">
                        <tr>
                            <td width="10%"></td>
                            <td width="5%" style="text-align:center;">Date:</td>
                            <td width="30%">
                                <form action="" method="post">
                                    <input type="date" name="sheduledate" id="date" class="input-text filter-container-items" style="margin:0;width:95%;">
                                </form>
                            </td>
                            <td width="12%">
                                <input type="submit" name="filter" value="Filter" class="btn-primary-soft btn button-icon btn-filter" style="padding:15px; margin:0;width:100%">
                            </td>
                        </tr>
                    </table>
                </center>
            </td>
        </tr>
        
        <tr>
            <td colspan="4">
                <center>
                    <div class="abc scroll">
                        <table width="93%" class="sub-table scrolldown" border="0">
                            <thead>
                                <tr>
                                    <th class="table-headin">Session Title</th>
                                    <th class="table-headin">Scheduled Date & Time</th>
                                    <th class="table-headin">Max Bookings</th>
                                    <th class="table-headin">Booked</th>
                                    <th class="table-headin">Events</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $result = $database->query($sqlmain);
                                
                                if($result->num_rows == 0){
                                    echo '<tr>
                                        <td colspan="5">
                                            <br><br><br><br>
                                            <center>
                                                <img src="../img/notfound.svg" width="25%">
                                                <br>
                                                <p class="heading-main12" style="margin-left:45px;font-size:20px;color:rgb(49,49,49)">No sessions found!</p>
                                                <a class="non-style-link" href="add-session.php">
                                                    <button class="login-btn btn-primary-soft btn" style="display:flex;justify-content:center;align-items:center;margin-left:20px;">
                                                        &nbsp; Add New Session &nbsp;
                                                    </button>
                                                </a>
                                            </center>
                                            <br><br><br><br>
                                        </td>
                                    </tr>';
                                } else {
                                    for($x=0; $x<$result->num_rows; $x++){
                                        $row = $result->fetch_assoc();
                                        $scheduleid = $row["scheduleid"];
                                        $title = $row["title"];
                                        $docname = $row["docname"];
                                        $scheduledate = $row["scheduledate"];
                                        $scheduletime = $row["scheduletime"];
                                        $nop = $row["nop"];
                                        
                                        // Get number of booked appointments for this session
                                        $booked_count = $database->query("SELECT COUNT(*) as booked FROM appointment WHERE scheduleid = $scheduleid");
                                        $booked_row = $booked_count->fetch_assoc();
                                        $booked = $booked_row["booked"];
                                        
                                        echo '<tr>
                                            <td>&nbsp;' . substr($title, 0, 50) . '</td>
                                            <td style="text-align:center;">' . substr($scheduledate, 0, 10) . ' ' . substr($scheduletime, 0, 5) . '</td>
                                            <td style="text-align:center;">' . $nop . '</td>
                                            <td style="text-align:center;">' . $booked . '</td>
                                            <td>
                                                <div style="display:flex;justify-content:center;">
                                                    <a href="?action=view&id=' . $scheduleid . '" class="non-style-link">
                                                        <button class="btn-primary-soft btn button-icon btn-view" style="padding-left:40px;padding-top:12px;padding-bottom:12px;margin-top:10px;">
                                                            <font class="tn-in-text">View</font>
                                                        </button>
                                                    </a>
                                                    &nbsp;&nbsp;&nbsp;
                                                    <a href="?action=edit&id=' . $scheduleid . '" class="non-style-link">
                                                        <button class="btn-primary-soft btn button-icon btn-edit" style="padding-left:40px;padding-top:12px;padding-bottom:12px;margin-top:10px;">
                                                            <font class="tn-in-text">Edit</font>
                                                        </button>
                                                    </a>
                                                    &nbsp;&nbsp;&nbsp;
                                                    <a href="?action=drop&id=' . $scheduleid . '&name=' . urlencode($title) . '" class="non-style-link">
                                                        <button class="btn-primary-soft btn button-icon btn-delete" style="padding-left:40px;padding-top:12px;padding-bottom:12px;margin-top:10px;">
                                                            <font class="tn-in-text">Cancel</font>
                                                        </button>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>';
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </center>
            </td>
        </tr>
    </table>
</div>

<!-- Delete Confirmation Popup -->
<?php if($show_delete_popup): ?>
<div id="popup1" class="overlay">
    <div class="popup">
        <center>
            <h2>Are you sure?</h2>
            <a class="close" href="schedule.php">&times;</a>
            <div class="content">
                You want to delete this session<br>("<?php echo substr(urldecode($delete_name), 0, 40); ?>").<br>
                <span style="color: red; font-size: 14px;">Note: This will also delete all associated appointments!</span>
            </div>
            <div style="display:flex;justify-content:center;">
                <a href="delete-session.php?id=<?php echo $delete_id; ?>" class="non-style-link">
                    <button class="btn-primary btn" style="display:flex;justify-content:center;align-items:center;margin:10px;padding:10px;">
                        <font class="tn-in-text">&nbsp;Yes&nbsp;</font>
                    </button>
                </a>&nbsp;&nbsp;&nbsp;
                <a href="schedule.php" class="non-style-link">
                    <button class="btn-primary btn" style="display:flex;justify-content:center;align-items:center;margin:10px;padding:10px;">
                        <font class="tn-in-text">&nbsp;&nbsp;No&nbsp;&nbsp;</font>
                    </button>
                </a>
            </div>
        </center>
    </div>
</div>
<?php endif; ?>

<!-- View Session Popup -->
<?php if($show_view_popup && $view_data && $view_data->num_rows > 0): 
    $row = $view_data->fetch_assoc();
    $docname = $row["docname"];
    $scheduleid = $row["scheduleid"];
    $title = $row["title"];
    $scheduledate = $row["scheduledate"];
    $scheduletime = $row["scheduletime"];
    $nop = $row['nop'];
?>
<div id="popup1" class="overlay">
    <div class="popup" style="width:70%;">
        <center>
            <h2>Session Details</h2>
            <a class="close" href="schedule.php">&times;</a>
            <div class="content">
                <table width="100%" class="sub-table" border="0">
                    <tr>
                        <td class="label-td"><label class="form-label">Session Title:</label></td>
                        <td><?php echo $title; ?></td>
                    </tr>
                    <tr>
                        <td class="label-td"><label class="form-label">Doctor:</label></td>
                        <td><?php echo $docname; ?></td>
                    </tr>
                    <tr>
                        <td class="label-td"><label class="form-label">Scheduled Date:</label></td>
                        <td><?php echo $scheduledate; ?></td>
                    </tr>
                    <tr>
                        <td class="label-td"><label class="form-label">Scheduled Time:</label></td>
                        <td><?php echo $scheduletime; ?></td>
                    </tr>
                    <tr>
                        <td class="label-td"><label class="form-label">Maximum Bookings:</label></td>
                        <td><?php echo $nop; ?></td>
                    </tr>
                    <tr>
                        <td class="label-td"><label class="form-label"><b>Registered Patients:</b> (<?php echo $view_appointments ? $view_appointments->num_rows : 0; ?>/<?php echo $nop; ?>)</label></td>
                        <td>
                            <div class="abc scroll" style="max-height:300px;">
                                <table width="100%" class="sub-table" border="0">
                                    <thead>
                                        <tr>
                                            <th class="table-headin">Patient ID</th>
                                            <th class="table-headin">Patient Name</th>
                                            <th class="table-headin">Appointment Number</th>
                                            <th class="table-headin">Phone</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if($view_appointments && $view_appointments->num_rows == 0){
                                            echo '<tr><td colspan="4"><center>No patients registered for this session yet.</center></td></tr>';
                                        } else if($view_appointments) {
                                            for($x=0; $x<$view_appointments->num_rows; $x++){
                                                $app_row = $view_appointments->fetch_assoc();
                                                $apponum = $app_row["apponum"];
                                                $pid = $app_row["pid"];
                                                $pname = $app_row["pname"];
                                                $ptel = $app_row["ptel"];
                                                echo '<tr>
                                                    <td style="text-align:center;">' . $pid . '</td>
                                                    <td style="font-weight:600;text-align:center;">' . $pname . '</td>
                                                    <td style="text-align:center;font-size:23px;font-weight:500;">' . $apponum . '</td>
                                                    <td style="text-align:center;">' . $ptel . '</td>
                                                </tr>';
                                            }
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </center>
    </div>
</div>
<?php endif; ?>

</body>
</html>