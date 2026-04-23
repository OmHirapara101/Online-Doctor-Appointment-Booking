<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/animations.css">  
    <link rel="stylesheet" href="../css/main.css">  
    <link rel="stylesheet" href="../css/admin.css">
        
    <title>Edit Profile - Doctor</title>
    <style>
        .popup{
            animation: transitionIn-Y-bottom 0.5s;
        }
        .sub-table{
            animation: transitionIn-Y-bottom 0.5s;
        }
        .profile-container-edit {
            max-width: 600px;
            margin: 50px auto;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .profile-pic {
            text-align: center;
            margin-bottom: 20px;
        }
        .profile-pic img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #007bff;
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
        }
        .form-group input:focus {
            outline: none;
            border-color: #007bff;
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
        .readonly-field {
            background-color: #f8f9fa;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <?php
    session_start();

    if(isset($_SESSION["user"])){
        if(($_SESSION["user"])=="" or $_SESSION['usertype']!='d'){
            header("location: ../login.php");
            exit();
        }
    }else{
        header("location: ../login.php");
        exit();
    }
    
    //import database
    include("../connection.php");
    
    // Get doctor email from session - FIX: Check multiple possible session variables
    $doctor_email = "";
    if(isset($_SESSION['email'])) {
        $doctor_email = $_SESSION['email'];
    } elseif(isset($_SESSION['doctor_email'])) {
        $doctor_email = $_SESSION['doctor_email'];
    } elseif(isset($_SESSION['user']) && $_SESSION['usertype'] == 'd') {
        $doctor_email = $_SESSION['user'];
    }
    
    // If still empty, redirect to login
    if(empty($doctor_email)){
        header("location: ../login.php");
        exit();
    }
    
    // Fetch current doctor data
    $sql = "SELECT d.*, s.sname as specialty_name 
            FROM doctor d 
            LEFT JOIN specialties s ON d.specialties = s.id 
            WHERE d.docemail = ?";
    $stmt = $database->prepare($sql);
    $stmt->bind_param("s", $doctor_email);
    $stmt->execute();
    $result = $stmt->get_result();
    $doctor = $result->fetch_assoc();
    
    if(!$doctor){
        header("location: ../login.php");
        exit();
    }
    
    $success_message = "";
    $error_message = "";
    
    // Handle form submission
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        
        // Handle email update
        if(isset($_POST['update_email'])){
            $new_email = $_POST['new_email'];
            
            // Check if email already exists
            $check_sql = "SELECT docemail FROM doctor WHERE docemail = ? AND docemail != ?";
            $check_stmt = $database->prepare($check_sql);
            $check_stmt->bind_param("ss", $new_email, $doctor_email);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if($check_result->num_rows > 0){
                $error_message = "Email already exists!";
            }else{
                $update_sql = "UPDATE doctor SET docemail = ? WHERE docemail = ?";
                $update_stmt = $database->prepare($update_sql);
                $update_stmt->bind_param("ss", $new_email, $doctor_email);
                
                if($update_stmt->execute()){
                    // Update session variables
                    $_SESSION['email'] = $new_email;
                    $_SESSION['doctor_email'] = $new_email;
                    if(isset($_SESSION['user']) && $_SESSION['user'] == $doctor_email) {
                        $_SESSION['user'] = $new_email;
                    }
                    
                    $doctor_email = $new_email;
                    $success_message = "Email updated successfully!";
                    $doctor['docemail'] = $new_email;
                    
                    // Update webuser table as well
                    $update_webuser = "UPDATE webuser SET email = ? WHERE email = ?";
                    $webuser_stmt = $database->prepare($update_webuser);
                    $webuser_stmt->bind_param("ss", $new_email, $doctor_email);
                    $webuser_stmt->execute();
                }else{
                    $error_message = "Error updating email!";
                }
            }
        }
        
        // Handle phone update
        if(isset($_POST['update_phone'])){
            $new_phone = $_POST['new_phone'];
            
            $update_sql = "UPDATE doctor SET doctel = ? WHERE docemail = ?";
            $update_stmt = $database->prepare($update_sql);
            $update_stmt->bind_param("ss", $new_phone, $doctor_email);
            
            if($update_stmt->execute()){
                $success_message = "Phone number updated successfully!";
                $doctor['doctel'] = $new_phone;
            }else{
                $error_message = "Error updating phone number!";
            }
        }
        
        // Handle profile picture upload
        if(isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0){
            $target_dir = "../uploads/doctors/";
            
            // Create directory if it doesn't exist
            if(!file_exists($target_dir)){
                mkdir($target_dir, 0777, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES["profile_pic"]["name"], PATHINFO_EXTENSION));
            $allowed_types = array("jpg", "jpeg", "png", "gif");
            
            if(in_array($file_extension, $allowed_types)){
                $new_filename = "doctor_" . $doctor['docid'] . "_" . time() . "." . $file_extension;
                $target_file = $target_dir . $new_filename;
                
                if(move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file)){
                    // Delete old profile picture if exists
                    if(!empty($doctor['photo'])){
                        $old_file = "../uploads/doctors/" . $doctor['photo'];
                        if(file_exists($old_file)){
                            unlink($old_file);
                        }
                    }
                    
                    $update_sql = "UPDATE doctor SET photo = ? WHERE docemail = ?";
                    $update_stmt = $database->prepare($update_sql);
                    $update_stmt->bind_param("ss", $new_filename, $doctor_email);
                    
                    if($update_stmt->execute()){
                        $success_message = "Profile picture updated successfully!";
                        $doctor['photo'] = $new_filename;
                    }else{
                        $error_message = "Error updating profile picture!";
                    }
                }else{
                    $error_message = "Error uploading file!";
                }
            }else{
                $error_message = "Only JPG, JPEG, PNG & GIF files are allowed!";
            }
        }
        
        // Handle password update
        if(isset($_POST['update_password'])){
            $current_password = $_POST['current_password'];
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];
            
            // Verify current password
            if($doctor['docpassword'] == $current_password){
                if($new_password == $confirm_password){
                    $update_sql = "UPDATE doctor SET docpassword = ? WHERE docemail = ?";
                    $update_stmt = $database->prepare($update_sql);
                    $update_stmt->bind_param("ss", $new_password, $doctor_email);
                    
                    if($update_stmt->execute()){
                        $success_message = "Password updated successfully!";
                    }else{
                        $error_message = "Error updating password!";
                    }
                }else{
                    $error_message = "New passwords do not match!";
                }
            }else{
                $error_message = "Current password is incorrect!";
            }
        }
        
        // Handle NIC update
        if(isset($_POST['update_nic'])){
            $new_nic = $_POST['new_nic'];
            
            $update_sql = "UPDATE doctor SET docnic = ? WHERE docemail = ?";
            $update_stmt = $database->prepare($update_sql);
            $update_stmt->bind_param("ss", $new_nic, $doctor_email);
            
            if($update_stmt->execute()){
                $success_message = "NIC updated successfully!";
                $doctor['docnic'] = $new_nic;
            }else{
                $error_message = "Error updating NIC!";
            }
        }
        
        // Refresh doctor data after updates
        $sql = "SELECT d.*, s.sname as specialty_name 
                FROM doctor d 
                LEFT JOIN specialties s ON d.specialties = s.id 
                WHERE d.docemail = ?";
        $stmt = $database->prepare($sql);
        $stmt->bind_param("s", $doctor_email);
        $stmt->execute();
        $result = $stmt->get_result();
        $doctor = $result->fetch_assoc();
    }
    
    include("sidebar.php");
    ?>
    
    <div class="dash-body">
        <table border="0" width="100%" style="border-spacing:0;margin:0;padding:0;margin-top:25px;">
            <tr>
                <td width="13%">
                    <a href="index.php">
                        <button class="login-btn btn-primary-soft btn btn-icon-back" style="padding-top:11px;padding-bottom:11px;margin-left:20px;width:125px">
                            <font class="tn-in-text">Back</font>
                        </button>
                    </a>
                </td>
                <td>
                    <p style="font-size:23px;padding-left:12px;font-weight:600;">Edit Profile - Dr. <?php echo htmlspecialchars($doctor['docname']); ?></p>
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
                        <div class="profile-container-edit">
                            <h2>Doctor Profile Settings</h2>
                            
                            <div class="info-note">
                                <strong>Note:</strong> Your name and specialty cannot be changed. Please contact administrator for these changes.
                            </div>
                            
                            <?php if($success_message): ?>
                                <div class="success-message"><?php echo $success_message; ?></div>
                            <?php endif; ?>
                            
                            <?php if($error_message): ?>
                                <div class="error-message"><?php echo $error_message; ?></div>
                            <?php endif; ?>
                            
                            <!-- Doctor Information Display -->
                            <div class="form-group">
                                <label>Doctor Name</label>
                                <input type="text" value="<?php echo htmlspecialchars($doctor['docname']); ?>" class="readonly-field" readonly>
                            </div>
                            
                            <div class="form-group">
                                <label>Specialty</label>
                                <input type="text" value="<?php echo htmlspecialchars($doctor['specialty_name']); ?>" class="readonly-field" readonly>
                            </div>
                            
                            <div class="form-group">
                                <label>Hospital</label>
                                <input type="text" value="<?php echo htmlspecialchars($doctor['hospital']); ?>" class="readonly-field" readonly>
                            </div>
                            
                            <hr>
                            
                            <!-- Profile Picture Upload -->
                            <div class="profile-pic">
                                <?php 
                                $profile_pic = !empty($doctor['photo']) ? "../uploads/doctors/" . $doctor['photo'] : "../img/user.png";
                                ?>
                                <img src="<?php echo $profile_pic; ?>" alt="Profile Picture" id="profileImage">
                            </div>
                            
                            <form method="POST" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label for="profile_pic">Change Profile Picture</label>
                                    <input type="file" name="profile_pic" id="profile_pic" accept="image/*">
                                    <small style="color:#666;display:block;margin-top:5px;">Allowed formats: JPG, JPEG, PNG, GIF (Max size: 5MB)</small>
                                </div>
                                <button type="submit" class="btn-primary">Upload Picture</button>
                            </form>
                            
                            <hr>
                            
                            <!-- Email Update -->
                            <form method="POST">
                                <div class="form-group">
                                    <label for="new_email">Email Address</label>
                                    <input type="email" name="new_email" id="new_email" value="<?php echo htmlspecialchars($doctor['docemail']); ?>" required>
                                </div>
                                <button type="submit" name="update_email" class="btn-primary">Update Email</button>
                            </form>
                            
                            <hr>
                            
                            <!-- Phone Number Update -->
                            <form method="POST">
                                <div class="form-group">
                                    <label for="new_phone">Phone Number</label>
                                    <input type="tel" name="new_phone" id="new_phone" value="<?php echo htmlspecialchars($doctor['doctel']); ?>" required>
                                </div>
                                <button type="submit" name="update_phone" class="btn-primary">Update Phone Number</button>
                            </form>
                            
                            <hr>
                            
                            <!-- NIC Update -->
                            <form method="POST">
                                <div class="form-group">
                                    <label for="new_nic">NIC Number</label>
                                    <input type="text" name="new_nic" id="new_nic" value="<?php echo htmlspecialchars($doctor['docnic']); ?>" required>
                                </div>
                                <button type="submit" name="update_nic" class="btn-primary">Update NIC</button>
                            </form>
                            
                            <hr>
                            
                            <!-- Password Update -->
                            <form method="POST">
                                <div class="form-group">
                                    <label for="current_password">Current Password</label>
                                    <input type="password" name="current_password" id="current_password" required>
                                </div>
                                <div class="form-group">
                                    <label for="new_password">New Password</label>
                                    <input type="password" name="new_password" id="new_password" required>
                                </div>
                                <div class="form-group">
                                    <label for="confirm_password">Confirm New Password</label>
                                    <input type="password" name="confirm_password" id="confirm_password" required>
                                </div>
                                <button type="submit" name="update_password" class="btn-primary">Update Password</button>
                            </form>
                        </div>
                    </center>
                </td>
            </tr>
        </table>
    </div>
    
    <script>
        document.getElementById('profile_pic').onchange = function(evt) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('profileImage').src = e.target.result;
            };
            reader.readAsDataURL(evt.target.files[0]);
        };
        
        // Form validation for phone number
        document.querySelector('form[method="POST"]:nth-of-type(4)')?.addEventListener('submit', function(e) {
            var phone = document.getElementById('new_phone').value;
            var phoneRegex = /^[0-9]{10,15}$/;
            if(!phoneRegex.test(phone)) {
                alert('Please enter a valid phone number (10-15 digits)');
                e.preventDefault();
            }
        });
    </script>
</body>
</html>