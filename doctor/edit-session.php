<?php
session_start();

if (isset($_SESSION["user"])) {
    if (($_SESSION["user"]) == "" or $_SESSION['usertype'] != 'd') {
        header("location: ../login.php");
        exit();
    } else {
        $useremail = $_SESSION["user"];
    }
} else {
    header("location: ../login.php");
    exit();
}

include("../connection.php");

// Get doctor details
$stmt = $database->prepare("SELECT * FROM doctor WHERE docemail = ?");
$stmt->bind_param("s", $useremail);
$stmt->execute();
$userrow = $stmt->get_result();
$userfetch = $userrow->fetch_assoc();
$docid = $userfetch["docid"];
$stmt->close();

if (!isset($_GET['id'])) {
    header("location: schedule.php");
    exit();
}

$schedule_id = intval($_GET['id']); // Convert to integer for security

// Fetch session details with prepared statement
$sql = "SELECT * FROM schedule WHERE scheduleid = ? AND docid = ?";
$stmt = $database->prepare($sql);
$stmt->bind_param("ii", $schedule_id, $docid);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("location: schedule.php");
    exit();
}

$session = $result->fetch_assoc();
$stmt->close();

$error_message = "";
$success_message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = mysqli_real_escape_string($database, trim($_POST["title"]));
    $nop = intval($_POST["nop"]);
    $date = mysqli_real_escape_string($database, $_POST["date"]);
    $time = mysqli_real_escape_string($database, $_POST["time"]);

    $errors = array();

    if (empty($title)) {
        $errors[] = "Session title is required";
    }
    if (empty($nop) || $nop < 1) {
        $errors[] = "Number of patients must be at least 1";
    }
    if (empty($date)) {
        $errors[] = "Date is required";
    }
    if (empty($time)) {
        $errors[] = "Time is required";
    }

    // Check if date is not in the past
    $today = date('Y-m-d');
    if ($date < $today) {
        $errors[] = "Session date cannot be in the past";
    }

    // Check if there are existing appointments for this session
    $check_appointments = $database->prepare("SELECT COUNT(*) as count FROM appointment WHERE scheduleid = ?");
    $check_appointments->bind_param("i", $schedule_id);
    $check_appointments->execute();
    $appointment_count = $check_appointments->get_result()->fetch_assoc();
    $check_appointments->close();

    // If there are appointments, check if reducing capacity below booked count
    if ($appointment_count['count'] > 0 && $nop < $appointment_count['count']) {
        $errors[] = "Cannot reduce capacity below " . $appointment_count['count'] . " (current bookings)";
    }

    if (empty($errors)) {
        $update_sql = "UPDATE schedule 
                       SET title = ?, 
                           scheduledate = ?, 
                           scheduletime = ?, 
                           nop = ? 
                       WHERE scheduleid = ? AND docid = ?";

        $update_stmt = $database->prepare($update_sql);
        $update_stmt->bind_param("sssiii", $title, $date, $time, $nop, $schedule_id, $docid);

        if ($update_stmt->execute()) {
            $success_message = "Session updated successfully!";
            // Refresh session data
            $session['title'] = $title;
            $session['scheduledate'] = $date;
            $session['scheduletime'] = $time;
            $session['nop'] = $nop;
        } else {
            $error_message = "Error updating session: " . $database->error;
        }
        $update_stmt->close();
    } else {
        $error_message = implode("<br>", $errors);
    }
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
    <!-- jQuery and jQuery Validation CDN -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
    <title>Edit Session</title>
    <style>
        .form-container {
            max-width: 600px;
            margin: 50px auto;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #ffc107;
        }

        .btn-primary {
            background-color: #ffc107;
            color: #333;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        .btn-primary:hover {
            background-color: #e0a800;
            color: #333;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.3s;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
            color: white;
            text-decoration: none;
        }

        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #dc3545;
        }

        h2 {
            margin-bottom: 20px;
            text-align: center;
            color: #333;
        }

        .info-note {
            background-color: #fff3cd;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #ffc107;
            color: #856404;
        }

        .current-info {
            background-color: #e7f3ff;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #007bff;
            color: #0056b3;
        }

        .small-text {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
            display: block;
        }

        /* jQuery Validation Styles */
        label.error {
            color: #dc3545 !important;
            font-size: 12px !important;
            margin-top: 5px !important;
            display: block !important;
            font-weight: normal !important;
        }

        input.error {
            border-color: #dc3545 !important;
            background-color: #fff8f8 !important;
        }

        input.valid {
            border-color: #28a745 !important;
            background-color: #f0fff4 !important;
        }

        .validation-message {
            color: #dc3545;
            font-size: 12px;
            margin-top: 5px;
            display: none;
        }
    </style>
</head>

<body>
    <?php include("sidebar.php"); ?>

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
                <td>
                    <p style="font-size:23px;padding-left:12px;font-weight:600;">Edit Session</p>
                </td>
                <td width="15%">
                    <p style="font-size:14px;color:rgb(119,119,119);padding:0;margin:0;text-align:right;">
                        Today's Date
                    </p>
                    <p class="heading-sub12" style="padding:0;margin:0;">
                        <?php
                        date_default_timezone_set('Asia/Kolkata');
                        echo date('Y-m-d');
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
                <td colspan="4" style="padding-top:20px;">
                    <center>
                        <div class="form-container">
                            <h2> Edit Session</h2>

                            <?php if ($success_message): ?>
                                <div class="success-message">
                                    <?php echo $success_message; ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($error_message): ?>
                                <div class="error-message">
                                    <?php echo $error_message; ?>
                                </div>
                            <?php endif; ?>

                            <div class="info-note">
                                <strong> Note:</strong> Modifying this session may affect existing appointments. Changes to date/time will be reflected for all booked patients.
                            </div>

                            <div class="current-info">
                                <strong> Current Session Information:</strong><br>
                                Session ID: <?php echo $schedule_id; ?><br>
                                Current Title: <?php echo htmlspecialchars($session['title']); ?><br>
                                Current Date: <?php echo $session['scheduledate']; ?><br>
                                Current Time: <?php echo $session['scheduletime']; ?><br>
                                Current Capacity: <?php echo $session['nop']; ?> patients
                            </div>

                            <form method="POST" action="" id="editSessionForm">
                                <div class="form-group">
                                    <label for="title"> Session Title *</label>
                                    <input type="text" name="title" id="title" value="<?php echo htmlspecialchars($session['title']); ?>">
                                    <label for="title" class="validation-message" id="title-error" generated="true" style="display: none;"></label>
                                    <small class="small-text">Example: Cardiology Consultation, General Checkup, etc.</small>
                                </div>

                                <div class="form-group">
                                    <label for="date"> Session Date *</label>
                                    <input type="date" name="date" id="date" value="<?php echo $session['scheduledate']; ?>" min="<?php echo date('Y-m-d'); ?>">
                                    <label for="date" class="validation-message" id="date-error" generated="true" style="display: none;"></label>
                                    <small class="small-text">Select a date for this session (cannot be in the past)</small>
                                </div>

                                <div class="form-group">
                                    <label for="time"> Session Time *</label>
                                    <input type="time" name="time" id="time" value="<?php echo $session['scheduletime']; ?>">
                                    <label for="time" class="validation-message" id="time-error" generated="true" style="display: none;"></label>
                                    <small class="small-text">Select the time when this session will start</small>
                                </div>

                                <div class="form-group">
                                    <label for="nop"> Maximum Bookings *</label>
                                    <input type="number" name="nop" id="nop" min="1" max="100" value="<?php echo $session['nop']; ?>">
                                    <label for="nop" class="validation-message" id="nop-error" generated="true" style="display: none;"></label>
                                    <small class="small-text">Maximum number of patients that can book this session (1-100)</small>
                                </div>

                                <div style="display: flex; gap: 10px; justify-content: center; margin-top: 30px;">
                                    <button type="submit" class="btn-primary" id="submitBtn"> Update Session</button>
                                    <a href="schedule.php" class="btn-secondary"> Cancel</a>
                                </div>
                            </form>
                        </div>
                    </center>
                </td>
            </tr>
        </table>
    </div>

    <script>
        $(document).ready(function() {
            // Custom validation method for time format (24-hour)
            $.validator.addMethod("validTime", function(value, element) {
                if (value === "") return true;
                var timeRegex = /^([01]\d|2[0-3]):([0-5]\d)$/;
                return timeRegex.test(value);
            }, "Please enter a valid time in HH:MM format (e.g., 14:30)");

            // Custom validation method for future date
            $.validator.addMethod("futureDate", function(value, element) {
                if (value === "") return true;
                var selectedDate = new Date(value);
                var today = new Date();
                today.setHours(0, 0, 0, 0);
                return selectedDate >= today;
            }, "Session date cannot be in the past");

            // Initialize form validation
            $("#editSessionForm").validate({
                rules: {
                    title: {
                        required: true,
                        minlength: 3,
                        maxlength: 100
                    },
                    date: {
                        required: true,
                        date: true,
                        futureDate: true
                    },
                    time: {
                        required: true,
                        validTime: true
                    },
                    nop: {
                        required: true,
                        digits: true,
                        min: 1,
                        max: 100
                    }
                },
                messages: {
                    title: {
                        required: "Please enter a session title",
                        minlength: "Session title must be at least 3 characters",
                        maxlength: "Session title cannot exceed 100 characters"
                    },
                    date: {
                        required: "Please select a session date",
                        date: "Please enter a valid date",
                        futureDate: "Session date cannot be in the past"
                    },
                    time: {
                        required: "Please select a session time",
                        validTime: "Please enter a valid time in HH:MM format (e.g., 14:30)"
                    },
                    nop: {
                        required: "Please enter number of patients",
                        digits: "Please enter a valid number",
                        min: "Number of patients must be at least 1",
                        max: "Number of patients cannot exceed 100"
                    }
                },
                errorClass: "error",
                validClass: "valid",
                errorElement: "label",
                errorPlacement: function(error, element) {
                    // Place error message in the hidden label
                    var errorId = element.attr("id") + "-error";
                    $("#" + errorId).text(error.text()).show();
                },
                highlight: function(element) {
                    $(element).addClass("error").removeClass("valid");
                    // Hide the hidden label error
                    var errorId = $(element).attr("id") + "-error";
                    $("#" + errorId).hide();
                },
                unhighlight: function(element) {
                    $(element).removeClass("error").addClass("valid");
                    // Hide the hidden label error
                    var errorId = $(element).attr("id") + "-error";
                    $("#" + errorId).hide();
                },
                onkeyup: function(element) {
                    this.element(element);
                },
                onclick: function(element) {
                    this.element(element);
                },
                onfocusout: function(element) {
                    this.element(element);
                },
                submitHandler: function(form) {
                    // Disable submit button to prevent double submission
                    var $submitBtn = $("#submitBtn");
                    $submitBtn.prop("disabled", true).text("⏳ Updating...");

                    // Submit the form
                    form.submit();
                }
            });

            // Additional real-time validation for better UX
            $("#title").on("keyup change", function() {
                $(this).valid();
            });

            $("#date").on("change", function() {
                $(this).valid();
            });

            $("#time").on("change", function() {
                $(this).valid();
            });

            $("#nop").on("keyup change", function() {
                $(this).valid();
            });
        });
    </script>
</body>

</html>