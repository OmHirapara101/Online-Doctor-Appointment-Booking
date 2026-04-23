<?php
// Start session at the VERY beginning - no spaces, no HTML before this
session_start();

if (isset($_SESSION["user"])) {
    if (($_SESSION["user"]) == "" or $_SESSION['usertype'] != 'p') {
        header("location: ../login.php");
        exit();
    } else {
        $useremail = $_SESSION["user"];
    }
} else {
    header("location: ../login.php");
    exit();
}

//import database
include("../connection.php");

$sqlmain = "SELECT * FROM patient WHERE pemail = ?";
$stmt = $database->prepare($sqlmain);
$stmt->bind_param("s", $useremail);
$stmt->execute();
$userrow = $stmt->get_result();
$userfetch = $userrow->fetch_assoc();

// Check if userfetch exists
if (!$userfetch) {
    header("location: ../login.php");
    exit();
}

$userid = $userfetch["pid"];
$username = $userfetch["pname"];
$patient_photo = $userfetch["pphoto"];

// Set profile picture path
if (!empty($patient_photo) && $patient_photo != "") {
    $photo_path = "../uploads/patients/" . $patient_photo;
    if (file_exists($photo_path)) {
        $profile_pic = $photo_path;
    } else {
        $profile_pic = "../img/user.png";
    }
} else {
    $profile_pic = "../img/user.png";
}

date_default_timezone_set('Asia/Kolkata');
$today = date('Y-m-d');
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

    <title>Sessions</title>
    <style>
        .popup {
            animation: transitionIn-Y-bottom 0.5s;
        }

        .sub-table {
            animation: transitionIn-Y-bottom 0.5s;
        }

        /* Alert Card Styles */
        .alert-card {
            position: fixed;
            top: 20px;
            right: 20px;
            min-width: 320px;
            max-width: 450px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            z-index: 9999;
            animation: slideInRight 0.3s ease-out;
            overflow: hidden;
        }

        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .alert-card-content {
            display: flex;
            align-items: center;
            padding: 16px 20px;
            gap: 15px;
        }

        .alert-card-icon {
            flex-shrink: 0;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            font-weight: bold;
        }

        .alert-card-icon.error {
            background-color: #fee2e2;
            color: #dc2626;
        }

        .alert-card-icon.success {
            background-color: #dcfce7;
            color: #16a34a;
        }

        .alert-card-icon.warning {
            background-color: #fffbeb;
            color: #d97706;
        }

        .alert-card-message {
            flex: 1;
        }

        .alert-card-message h4 {
            margin: 0 0 5px 0;
            font-size: 16px;
            font-weight: 600;
            color: #1f2937;
        }

        .alert-card-message p {
            margin: 0;
            font-size: 14px;
            color: #6b7280;
            line-height: 1.4;
        }

        .alert-card-close {
            cursor: pointer;
            font-size: 20px;
            color: #9ca3af;
            transition: color 0.2s;
            line-height: 1;
            background: none;
            border: none;
            padding: 0;
        }

        .alert-card-close:hover {
            color: #374151;
        }

        .alert-card-progress {
            height: 4px;
            background-color: #e5e7eb;
        }

        .alert-card-progress-bar {
            height: 100%;
            width: 100%;
            animation: progress 5s linear forwards;
        }

        .alert-card-progress-bar.error {
            background-color: #dc2626;
        }

        .alert-card-progress-bar.success {
            background-color: #16a34a;
        }

        .alert-card-progress-bar.warning {
            background-color: #d97706;
        }

        @keyframes progress {
            from {
                width: 100%;
            }

            to {
                width: 0%;
            }
        }

        /* Overlay for alert card */
        .alert-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.3);
            z-index: 9998;
            display: none;
        }

        .alert-overlay.show {
            display: block;
            animation: fadeIn 0.2s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }
    </style>
</head>

<body>
    <?php include('sidebar.php'); ?>

    <div class="dash-body">
        <table border="0" width="100%" style=" border-spacing: 0;margin:0;padding:0;margin-top:25px; ">
            <tr>
                <td width="13%">
                    <a href="schedule.php"><button class="login-btn btn-primary-soft btn btn-icon-back" style="padding-top:11px;padding-bottom:11px;margin-left:20px;width:125px">
                            <font class="tn-in-text">Back</font>
                        </button></a>
                </td>
                <td>
                    <form action="schedule.php" method="post" class="header-search">

                        <input type="search" name="search" class="input-text header-searchbar" placeholder="Search Doctor name or Email or Date (YYYY-MM-DD)" list="doctors">&nbsp;&nbsp;

                        <?php
                        echo '<datalist id="doctors">';
                        $list11 = $database->query("select DISTINCT * from  doctor;");
                        $list12 = $database->query("select DISTINCT * from  schedule GROUP BY title;");

                        for ($y = 0; $y < $list11->num_rows; $y++) {
                            $row00 = $list11->fetch_assoc();
                            $d = $row00["docname"];
                            echo "<option value='$d'><br/>";
                        };

                        for ($y = 0; $y < $list12->num_rows; $y++) {
                            $row00 = $list12->fetch_assoc();
                            $d = $row00["title"];
                            echo "<option value='$d'><br/>";
                        };

                        echo ' </datalist>';
                        ?>

                        <input type="Submit" value="Search" class="login-btn btn-primary btn" style="padding-left: 25px;padding-right: 25px;padding-top: 10px;padding-bottom: 10px;">
                    </form>
                </td>
                <td width="15%">
                    <p style="font-size: 14px;color: rgb(119, 119, 119);padding: 0;margin: 0;text-align: right;">
                        Today's Date
                    </p>
                    <p class="heading-sub12" style="padding: 0;margin: 0;">
                        <?php echo $today; ?>
                    </p>
                </td>
                <td width="10%">
                    <button class="btn-label" style="display: flex;justify-content: center;align-items: center;"><img src="../img/calendar.svg" width="100%"></button>
                </td>
            </tr>

            <tr>
                <td colspan="4" style="padding-top:10px;width: 100%;">
                    <!-- <p class="heading-main12" style="margin-left: 45px;font-size:18px;color:rgb(49, 49, 49);font-weight:400;">Scheduled Sessions / Booking / <b>Review Booking</b></p> -->
                </td>
            </tr>

            <tr>
                <td colspan="4">
                    <center>
                        <div class="abc scroll">
                            <table width="100%" class="sub-table scrolldown" border="0" style="padding: 50px;border:none">
                                <tbody>
                                    <?php
                                    if (isset($_GET["id"])) {
                                        $id = $_GET["id"];

                                        $sqlmain = "SELECT * FROM schedule INNER JOIN doctor ON schedule.docid=doctor.docid WHERE schedule.scheduleid=? ORDER BY schedule.scheduledate DESC";
                                        $stmt = $database->prepare($sqlmain);
                                        $stmt->bind_param("i", $id);
                                        $stmt->execute();
                                        $result = $stmt->get_result();
                                        
                                        if ($row = $result->fetch_assoc()) {
                                            $scheduleid = $row["scheduleid"];
                                            $title = $row["title"];
                                            $docname = $row["docname"];
                                            $docemail = $row["docemail"];
                                            $scheduledate = $row["scheduledate"];
                                            $scheduletime = $row["scheduletime"];
                                            
                                            $sql2 = "SELECT * FROM appointment WHERE scheduleid=$id";
                                            $result12 = $database->query($sql2);
                                            $apponum = ($result12->num_rows) + 1;

                                            echo '
                                            <form action="booking-complete.php" method="post">
                                                <input type="hidden" name="scheduleid" value="' . $scheduleid . '">
                                                <input type="hidden" name="apponum" value="' . $apponum . '">
                                                <input type="hidden" name="date" value="' . $today . '">
                                            ';
                                            
                                            echo '
                                            <td style="width: 50%;" rowspan="2">
                                                <div class="dashboard-items search-items">
                                                    <div style="width:100%">
                                                        <div class="h1-search" style="font-size:25px;">Session Details</div><br><br>
                                                        <div class="h3-search" style="font-size:18px;line-height:30px">
                                                            Doctor name: &nbsp;&nbsp;<b>' . htmlspecialchars($docname) . '</b><br>
                                                            Doctor Email: &nbsp;&nbsp;<b>' . htmlspecialchars($docemail) . '</b>
                                                        </div><br>
                                                        <div class="h3-search" style="font-size:18px;">
                                                            Session Title: ' . htmlspecialchars($title) . '<br>
                                                            Session Scheduled Date: ' . htmlspecialchars($scheduledate) . '<br>
                                                            Session Starts: ' . htmlspecialchars($scheduletime) . '<br>
                                                            Channeling fee: <b>LKR.2 000.00</b>
                                                        </div><br>
                                                    </div>
                                                </div>
                                            </td>
                                            
                                            <td style="width: 25%;">
                                                <div class="dashboard-items search-items">
                                                    <div style="width:100%;padding-top: 15px;padding-bottom: 15px;">
                                                        <div class="h1-search" style="font-size:20px;line-height:35px;margin-left:8px;text-align:center;">
                                                            Your Appointment Number
                                                        </div>
                                                        <center>
                                                            <div class="dashboard-icons" style="margin-left:0px;width:90%;font-size:70px;font-weight:800;text-align:center;color:var(--btnnictext);background-color:var(--btnice)">
                                                                ' . $apponum . '
                                                            </div>
                                                        </center>
                                                    </div>
                                                </div>
                                            </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <input type="Submit" class="login-btn btn-primary btn btn-book" style="margin-left:10px;padding-left:25px;padding-right:25px;padding-top:10px;padding-bottom:10px;width:95%;text-align:center;" value="Book now" name="booknow">
                                                </td>
                                            </tr>
                                            </form>';
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
</body>

<script>
    function showAlertCard(type, title, message, duration = 5000) {
        let existingCard = document.querySelector('.alert-card');
        if(existingCard) {
            existingCard.remove();
        }
        
        const alertCard = document.createElement('div');
        alertCard.className = 'alert-card';
        
        let iconSymbol = '';
        if(type === 'error') {
            iconSymbol = '✕';
        } else if(type === 'success') {
            iconSymbol = '✓';
        } else {
            iconSymbol = '⚠';
        }
        
        alertCard.innerHTML = `
            <div class="alert-card-content">
                <div class="alert-card-icon ${type}">
                    ${iconSymbol}
                </div>
                <div class="alert-card-message">
                    <h4>${title}</h4>
                    <p>${message}</p>
                </div>
                <button class="alert-card-close" onclick="this.closest('.alert-card').remove()">×</button>
            </div>
            <div class="alert-card-progress">
                <div class="alert-card-progress-bar ${type}"></div>
            </div>
        `;
        
        document.body.appendChild(alertCard);
        
        setTimeout(() => {
            if(alertCard && alertCard.parentNode) {
                alertCard.remove();
            }
        }, duration);
    }
    
    <?php if(isset($_GET['error'])): ?>
        showAlertCard('error', 'Booking Failed', '<?php echo htmlspecialchars($_GET["error"]); ?>');
        const url = new URL(window.location.href);
        url.searchParams.delete('error');
        window.history.replaceState({}, document.title, url);
    <?php endif; ?>
    
    <?php if(isset($_GET['success'])): ?>
        showAlertCard('success', 'Success!', '<?php echo htmlspecialchars($_GET["success"]); ?>');
        const successUrl = new URL(window.location.href);
        successUrl.searchParams.delete('success');
        window.history.replaceState({}, document.title, successUrl);
    <?php endif; ?>
</script>
</html>