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

include("../connection.php");

// Get doctor details
$userrow = $database->query("SELECT * FROM doctor WHERE docemail='$useremail'");
$userfetch = $userrow->fetch_assoc();
$docid = $userfetch["docid"];
$docname = $userfetch["docname"];

if($_POST){
    $title = mysqli_real_escape_string($database, $_POST["title"]);
    $nop = mysqli_real_escape_string($database, $_POST["nop"]);
    $date = mysqli_real_escape_string($database, $_POST["date"]);
    $time = mysqli_real_escape_string($database, $_POST["time"]);
    
    // Validate inputs
    $errors = array();
    
    if(empty($title)){
        $errors[] = "Session title is required";
    }
    if(empty($nop) || $nop < 1){
        $errors[] = "Number of patients must be at least 1";
    }
    if(empty($date)){
        $errors[] = "Date is required";
    }
    if(empty($time)){
        $errors[] = "Time is required";
    }
    
    // Check if date is not in the past
    $today = date('Y-m-d');
    if($date < $today){
        $errors[] = "Session date cannot be in the past";
    }
    
    if(empty($errors)){
        $sql = "INSERT INTO schedule (docid, title, scheduledate, scheduletime, nop) 
                VALUES ($docid, '$title', '$date', '$time', $nop)";
        
        if($database->query($sql)){
            header("location: schedule.php?action=session-added&title=" . urlencode($title));
            exit();
        } else {
            $error_message = "Error adding session: " . $database->error;
        }
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
    <title>Add New Session</title>
    <style>
        .form-container {
            max-width: 600px;
            margin: 50px auto;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 20px;
            position: relative;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        .btn-primary {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .btn-primary:disabled {
            background-color: #6c757d;
            cursor: not-allowed;
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
        }
        .btn-secondary:hover {
            background-color: #5a6268;
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
        hr {
            margin: 20px 0;
            border: none;
            border-top: 1px solid #ddd;
        }
        .info-note {
            background-color: #e7f3ff;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #007bff;
            color: #0056b3;
        }
        /* jQuery Validation Styles */
        label.error {
            color: #dc3545;
            font-size: 12px;
            margin-top: 5px;
            display: block;
            font-weight: normal;
        }
        input.error {
            border-color: #dc3545;
            background-color: #fff8f8;
        }
        input.valid {
            border-color: #28a745;
            background-color: #f0fff4;
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
                    <p style="font-size:23px;padding-left:12px;font-weight:600;">Add New Session</p>
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
                            <h2>Create New Session</h2>
                            
                            <div class="info-note">
                                <strong>Note:</strong> Please fill in all the details to create a new session. Patients will be able to book appointments for this session.
                            </div>
                            
                            <?php if(isset($error_message)): ?>
                                <div class="error-message"><?php echo $error_message; ?></div>
                            <?php endif; ?>
                            
                            <form method="POST" action="" id="sessionForm">
                                <div class="form-group">
                                    <label for="title">Session Title *</label>
                                    <input type="text" name="title" id="title" placeholder="e.g., Cardiology Consultation">
                                </div>
                                
                                <div class="form-group">
                                    <label for="date">Session Date *</label>
                                    <input type="date" name="date" id="date" min="<?php echo date('Y-m-d'); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="time">Session Time *</label>
                                    <input type="time" name="time" id="time">
                                </div>
                                
                                <div class="form-group">
                                    <label for="nop">Number of Patients (Max Bookings) *</label>
                                    <input type="number" name="nop" id="nop" min="1" max="100" value="10">
                                    <small>Maximum number of patients that can book this session</small>
                                </div>
                                
                                <div style="display: flex; gap: 10px; justify-content: center;">
                                    <button type="submit" class="btn-primary" id="submitBtn">Create Session</button>
                                    <a href="schedule.php" class="btn-secondary">Cancel</a>
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
        $("#sessionForm").validate({
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
                    validTime: "Please enter a valid time (HH:MM format)"
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
                error.insertAfter(element);
            },
            highlight: function(element) {
                $(element).addClass("error").removeClass("valid");
            },
            unhighlight: function(element) {
                $(element).removeClass("error").addClass("valid");
            },
            onkeyup: function(element, event) {
                // Validate on keyup for better UX
                if (this.elementValue(element) !== "") {
                    $(element).valid();
                }
            },
            submitHandler: function(form) {
                // Disable submit button to prevent double submission
                var $submitBtn = $("#submitBtn");
                $submitBtn.prop("disabled", true).text("Creating...");
                
                // Submit the form
                form.submit();
            }
        });

        // Real-time validation feedback - clears error as soon as you type
        $("#title").on("keyup change focus blur", function() {
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