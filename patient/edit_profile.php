<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/animations.css">  
    <link rel="stylesheet" href="../css/main.css">  
    <link rel="stylesheet" href="../css/admin.css">
        
    <title>Edit Profile - Patient</title>
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
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }
        .form-group input:focus, .form-group textarea:focus {
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
        .dob-note {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <?php
    session_start();

    // Check if patient is logged in
    if(isset($_SESSION["user"])){
        if(($_SESSION["user"])=="" or $_SESSION['usertype']!='p'){
            header("location: ../login.php");
            exit();
        }
    }else{
        header("location: ../login.php");
        exit();
    }
    
    // Import database connection
    include("../connection.php");
    
    // Get patient email from session
    $patient_email = "";
    if(isset($_SESSION['email'])) {
        $patient_email = $_SESSION['email'];
    } elseif(isset($_SESSION['patient_email'])) {
        $patient_email = $_SESSION['patient_email'];
    } elseif(isset($_SESSION['user']) && $_SESSION['usertype'] == 'p') {
        $patient_email = $_SESSION['user'];
    }
    
    // If still empty, redirect to login
    if(empty($patient_email)){
        header("location: ../login.php");
        exit();
    }
    
    // Fetch current patient data
    $sql = "SELECT * FROM patient WHERE pemail = ?";
    $stmt = $database->prepare($sql);
    $stmt->bind_param("s", $patient_email);
    $stmt->execute();
    $result = $stmt->get_result();
    $patient = $result->fetch_assoc();
    
    if(!$patient){
        header("location: ../login.php");
        exit();
    }
    
    $success_message = "";
    $error_message = "";
    
    // Handle form submission
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        
        // Handle email update
        if(isset($_POST['update_email'])){
            $new_email = trim($_POST['new_email']);
            
            if(empty($new_email)){
                $error_message = "Email cannot be empty!";
            } else {
                // Check if email already exists
                $check_sql = "SELECT pemail FROM patient WHERE pemail = ? AND pemail != ?";
                $check_stmt = $database->prepare($check_sql);
                $check_stmt->bind_param("ss", $new_email, $patient_email);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                
                if($check_result->num_rows > 0){
                    $error_message = "Email already exists!";
                }else{
                    $update_sql = "UPDATE patient SET pemail = ? WHERE pemail = ?";
                    $update_stmt = $database->prepare($update_sql);
                    $update_stmt->bind_param("ss", $new_email, $patient_email);
                    
                    if($update_stmt->execute()){
                        // Update session variables
                        $_SESSION['email'] = $new_email;
                        $_SESSION['patient_email'] = $new_email;
                        if(isset($_SESSION['user']) && $_SESSION['user'] == $patient_email) {
                            $_SESSION['user'] = $new_email;
                        }
                        
                        $patient_email = $new_email;
                        $success_message = "Email updated successfully!";
                        $patient['pemail'] = $new_email;
                        
                        // Update webuser table as well
                        $update_webuser = "UPDATE webuser SET email = ? WHERE email = ?";
                        $webuser_stmt = $database->prepare($update_webuser);
                        $webuser_stmt->bind_param("ss", $new_email, $patient_email);
                        $webuser_stmt->execute();
                    }else{
                        $error_message = "Error updating email!";
                    }
                }
            }
        }
        
        // Handle name update
        if(isset($_POST['update_name'])){
            $new_name = trim($_POST['new_name']);
            
            if(empty($new_name)){
                $error_message = "Name cannot be empty!";
            } else {
                $update_sql = "UPDATE patient SET pname = ? WHERE pemail = ?";
                $update_stmt = $database->prepare($update_sql);
                $update_stmt->bind_param("ss", $new_name, $patient_email);
                
                if($update_stmt->execute()){
                    $success_message = "Name updated successfully!";
                    $patient['pname'] = $new_name;
                    // Update session username if needed
                    $_SESSION['username'] = $new_name;
                }else{
                    $error_message = "Error updating name!";
                }
            }
        }
        
        // Handle phone update
        if(isset($_POST['update_phone'])){
            $new_phone = trim($_POST['new_phone']);
            
            if(empty($new_phone)){
                $error_message = "Phone number cannot be empty!";
            } elseif(!preg_match('/^[0-9]{10,15}$/', $new_phone)) {
                $error_message = "Please enter a valid phone number (10-15 digits)!";
            } else {
                $update_sql = "UPDATE patient SET ptel = ? WHERE pemail = ?";
                $update_stmt = $database->prepare($update_sql);
                $update_stmt->bind_param("ss", $new_phone, $patient_email);
                
                if($update_stmt->execute()){
                    $success_message = "Phone number updated successfully!";
                    $patient['ptel'] = $new_phone;
                }else{
                    $error_message = "Error updating phone number!";
                }
            }
        }
        
        // Handle address update
        if(isset($_POST['update_address'])){
            $new_address = trim($_POST['new_address']);
            
            if(empty($new_address)){
                $error_message = "Address cannot be empty!";
            } else {
                $update_sql = "UPDATE patient SET paddress = ? WHERE pemail = ?";
                $update_stmt = $database->prepare($update_sql);
                $update_stmt->bind_param("ss", $new_address, $patient_email);
                
                if($update_stmt->execute()){
                    $success_message = "Address updated successfully!";
                    $patient['paddress'] = $new_address;
                }else{
                    $error_message = "Error updating address!";
                }
            }
        }
        
        // Handle NIC update
        if(isset($_POST['update_nic'])){
            $new_nic = trim($_POST['new_nic']);
            
            if(empty($new_nic)){
                $error_message = "NIC cannot be empty!";
            } elseif(!preg_match('/^[0-9]{9,12}$/', $new_nic)) {
                $error_message = "Please enter a valid NIC number (9-12 digits)!";
            } else {
                $update_sql = "UPDATE patient SET pnic = ? WHERE pemail = ?";
                $update_stmt = $database->prepare($update_sql);
                $update_stmt->bind_param("ss", $new_nic, $patient_email);
                
                if($update_stmt->execute()){
                    $success_message = "NIC updated successfully!";
                    $patient['pnic'] = $new_nic;
                }else{
                    $error_message = "Error updating NIC!";
                }
            }
        }
        
        // Handle DOB update
        if(isset($_POST['update_dob'])){
            $new_dob = $_POST['new_dob'];
            
            if(empty($new_dob)){
                $error_message = "Date of birth cannot be empty!";
            } else {
                // Validate age (must be at least 1 year old and not future date)
                $dob_timestamp = strtotime($new_dob);
                $today = time();
                $age_years = floor(($today - $dob_timestamp) / (365.25 * 24 * 60 * 60));
                
                if($new_dob > date('Y-m-d')){
                    $error_message = "Date of birth cannot be in the future!";
                } elseif($age_years < 1) {
                    $error_message = "Patient must be at least 1 year old!";
                } else {
                    $update_sql = "UPDATE patient SET pdob = ? WHERE pemail = ?";
                    $update_stmt = $database->prepare($update_sql);
                    $update_stmt->bind_param("ss", $new_dob, $patient_email);
                    
                    if($update_stmt->execute()){
                        $success_message = "Date of birth updated successfully!";
                        $patient['pdob'] = $new_dob;
                    }else{
                        $error_message = "Error updating date of birth!";
                    }
                }
            }
        }
        
        // Handle profile picture upload
        if(isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0){
            $target_dir = "../uploads/patients/";
            
            // Create directory if it doesn't exist
            if(!file_exists($target_dir)){
                mkdir($target_dir, 0777, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES["profile_pic"]["name"], PATHINFO_EXTENSION));
            $allowed_types = array("jpg", "jpeg", "png", "gif");
            $max_file_size = 5 * 1024 * 1024; // 5MB
            
            if($_FILES["profile_pic"]["size"] > $max_file_size){
                $error_message = "File size must be less than 5MB!";
            } elseif(in_array($file_extension, $allowed_types)){
                $new_filename = "patient_" . $patient['pid'] . "_" . time() . "." . $file_extension;
                $target_file = $target_dir . $new_filename;
                
                if(move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file)){
                    // Delete old profile picture if exists
                    if(!empty($patient['pphoto'])){
                        $old_file = "../uploads/patients/" . $patient['pphoto'];
                        if(file_exists($old_file)){
                            unlink($old_file);
                        }
                    }
                    
                    $update_sql = "UPDATE patient SET pphoto = ? WHERE pemail = ?";
                    $update_stmt = $database->prepare($update_sql);
                    $update_stmt->bind_param("ss", $new_filename, $patient_email);
                    
                    if($update_stmt->execute()){
                        $success_message = "Profile picture updated successfully!";
                        $patient['pphoto'] = $new_filename;
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
            
            if(empty($current_password) || empty($new_password) || empty($confirm_password)){
                $error_message = "All password fields are required!";
            } else {
                // Verify current password
                if($patient['ppassword'] == $current_password){
                    if($new_password == $confirm_password){
                        if(strlen($new_password) >= 6){
                            $update_sql = "UPDATE patient SET ppassword = ? WHERE pemail = ?";
                            $update_stmt = $database->prepare($update_sql);
                            $update_stmt->bind_param("ss", $new_password, $patient_email);
                            
                            if($update_stmt->execute()){
                                $success_message = "Password updated successfully!";
                            }else{
                                $error_message = "Error updating password!";
                            }
                        }else{
                            $error_message = "Password must be at least 6 characters long!";
                        }
                    }else{
                        $error_message = "New passwords do not match!";
                    }
                }else{
                    $error_message = "Current password is incorrect!";
                }
            }
        }
        
        // Refresh patient data after updates
        $sql = "SELECT * FROM patient WHERE pemail = ?";
        $stmt = $database->prepare($sql);
        $stmt->bind_param("s", $patient_email);
        $stmt->execute();
        $result = $stmt->get_result();
        $patient = $result->fetch_assoc();
    }
    
    // Set variables for sidebar
    $username = $patient['pname'];
    $useremail = $patient['pemail'];
    
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
                    <p style="font-size:23px;padding-left:12px;font-weight:600;">Edit Profile - <?php echo htmlspecialchars($patient['pname']); ?></p>
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
                            <h2>Patient Profile Settings</h2>
                            
                            <div class="info-note">
                                <strong>Note:</strong> You can update your personal information here.
                            </div>
                            
                            <?php if($success_message): ?>
                                <div class="success-message"><?php echo $success_message; ?></div>
                            <?php endif; ?>
                            
                            <?php if($error_message): ?>
                                <div class="error-message"><?php echo $error_message; ?></div>
                            <?php endif; ?>
                            
                            <!-- Profile Picture Upload -->
                            <div class="profile-pic">
                                <?php 
                                $profile_pic = (!empty($patient['pphoto']) && file_exists("../uploads/patients/" . $patient['pphoto'])) ? "../uploads/patients/" . $patient['pphoto'] : "../img/user.png";
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
                            
                            <!-- Name Update -->
                            <form method="POST">
                                <div class="form-group">
                                    <label for="new_name">Full Name</label>
                                    <input type="text" name="new_name" id="new_name" value="<?php echo htmlspecialchars($patient['pname']); ?>" required>
                                </div>
                                <button type="submit" name="update_name" class="btn-primary">Update Name</button>
                            </form>
                            
                            <hr>
                            
                            <!-- Email Update -->
                            <form method="POST">
                                <div class="form-group">
                                    <label for="new_email">Email Address</label>
                                    <input type="email" name="new_email" id="new_email" value="<?php echo htmlspecialchars($patient['pemail']); ?>" required>
                                </div>
                                <button type="submit" name="update_email" class="btn-primary">Update Email</button>
                            </form>
                            
                            <hr>
                            
                            <!-- Phone Number Update -->
                            <form method="POST">
                                <div class="form-group">
                                    <label for="new_phone">Phone Number</label>
                                    <input type="tel" name="new_phone" id="new_phone" value="<?php echo htmlspecialchars($patient['ptel']); ?>" required>
                                </div>
                                <button type="submit" name="update_phone" class="btn-primary">Update Phone Number</button>
                            </form>
                            
                            <hr>
                            
                            <!-- Address Update -->
                            <form method="POST">
                                <div class="form-group">
                                    <label for="new_address">Address</label>
                                    <textarea name="new_address" id="new_address" rows="3" required><?php echo htmlspecialchars($patient['paddress']); ?></textarea>
                                </div>
                                <button type="submit" name="update_address" class="btn-primary">Update Address</button>
                            </form>
                            
                            <hr>
                            
                            <!-- NIC Update -->
                            <form method="POST">
                                <div class="form-group">
                                    <label for="new_nic">NIC Number</label>
                                    <input type="text" name="new_nic" id="new_nic" value="<?php echo htmlspecialchars($patient['pnic']); ?>" required>
                                    <small class="dob-note">National Identity Card number (9-12 digits)</small>
                                </div>
                                <button type="submit" name="update_nic" class="btn-primary">Update NIC</button>
                            </form>
                            
                            <hr>
                            
                            <!-- Date of Birth Update -->
                            <form method="POST">
                                <div class="form-group">
                                    <label for="new_dob">Date of Birth</label>
                                    <input type="date" name="new_dob" id="new_dob" value="<?php echo htmlspecialchars($patient['pdob']); ?>" required>
                                    <small class="dob-note">Patient must be at least 1 year old</small>
                                </div>
                                <button type="submit" name="update_dob" class="btn-primary">Update Date of Birth</button>
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
                                    <small class="dob-note">Minimum 6 characters</small>
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
        // Preview profile picture before upload
        document.getElementById('profile_pic').onchange = function(evt) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('profileImage').src = e.target.result;
            };
            reader.readAsDataURL(evt.target.files[0]);
        };
        
        // Form validation for phone number
        document.getElementById('new_phone')?.addEventListener('change', function() {
            var phone = this.value;
            var phoneRegex = /^[0-9]{10,15}$/;
            if(!phoneRegex.test(phone)) {
                alert('Please enter a valid phone number (10-15 digits)');
                this.value = '';
            }
        });
        
        // Form validation for NIC
        document.getElementById('new_nic')?.addEventListener('change', function() {
            var nic = this.value;
            var nicRegex = /^[0-9]{9,12}$/;
            if(!nicRegex.test(nic)) {
                alert('Please enter a valid NIC number (9-12 digits)');
                this.value = '';
            }
        });
    </script>
</body>
</html>