<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/animations.css">  
    <link rel="stylesheet" href="../css/main.css">  
    <link rel="stylesheet" href="../css/admin.css">
        
    <title>Edit Profile</title>
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
    </style>
</head>
<body>
    <?php
    session_start();

    if(isset($_SESSION["user"])){
        if(($_SESSION["user"])=="" or $_SESSION['usertype']!='a'){
            header("location: ../login.php");
        }
    }else{
        header("location: ../login.php");
    }
    
    //import database
    include("../connection.php");
    
    // Get admin email from session
    $admin_email = isset($_SESSION['email']) ? $_SESSION['email'] : "om@gmail.com";
    
    // Fetch current admin data
    $sql = "SELECT aemail, apassword, profile_pic FROM admin WHERE aemail = ?";
    $stmt = $database->prepare($sql);
    $stmt->bind_param("s", $admin_email);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();
    
    $success_message = "";
    $error_message = "";
    
    // Handle form submission
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        if(isset($_POST['update_email'])){
            $new_email = $_POST['new_email'];
            
            // Check if email already exists
            $check_sql = "SELECT aemail FROM admin WHERE aemail = ?";
            $check_stmt = $database->prepare($check_sql);
            $check_stmt->bind_param("s", $new_email);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if($check_result->num_rows > 0 && $new_email != $admin_email){
                $error_message = "Email already exists!";
            }else{
                $update_sql = "UPDATE admin SET aemail = ? WHERE aemail = ?";
                $update_stmt = $database->prepare($update_sql);
                $update_stmt->bind_param("ss", $new_email, $admin_email);
                
                if($update_stmt->execute()){
                    $_SESSION['email'] = $new_email;
                    $admin_email = $new_email;
                    $success_message = "Email updated successfully!";
                    $admin['aemail'] = $new_email;
                }else{
                    $error_message = "Error updating email!";
                }
            }
        }
        
        if(isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0){
            $target_dir = "../uploads/admin/";
            
            // Create directory if it doesn't exist
            if(!file_exists($target_dir)){
                mkdir($target_dir, 0777, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES["profile_pic"]["name"], PATHINFO_EXTENSION));
            $allowed_types = array("jpg", "jpeg", "png", "gif");
            
            if(in_array($file_extension, $allowed_types)){
                $new_filename = time() . "_" . md5($admin_email) . "." . $file_extension;
                $target_file = $target_dir . $new_filename;
                
                if(move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file)){
                    // Delete old profile picture if exists
                    if(!empty($admin['profile_pic'])){
                        $old_file = "../uploads/admin/" . $admin['profile_pic'];
                        if(file_exists($old_file)){
                            unlink($old_file);
                        }
                    }
                    
                    $update_sql = "UPDATE admin SET profile_pic = ? WHERE aemail = ?";
                    $update_stmt = $database->prepare($update_sql);
                    $update_stmt->bind_param("ss", $new_filename, $admin_email);
                    
                    if($update_stmt->execute()){
                        $success_message = "Profile picture updated successfully!";
                        $admin['profile_pic'] = $new_filename;
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
        
        if(isset($_POST['update_password'])){
            $current_password = $_POST['current_password'];
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];
            
            // Verify current password
            if($admin['apassword'] == $current_password){
                if($new_password == $confirm_password){
                    $update_sql = "UPDATE admin SET apassword = ? WHERE aemail = ?";
                    $update_stmt = $database->prepare($update_sql);
                    $update_stmt->bind_param("ss", $new_password, $admin_email);
                    
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
        
        // Refresh admin data after updates
        $sql = "SELECT aemail, apassword, profile_pic FROM admin WHERE aemail = ?";
        $stmt = $database->prepare($sql);
        $stmt->bind_param("s", $admin_email);
        $stmt->execute();
        $result = $stmt->get_result();
        $admin = $result->fetch_assoc();
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
                    <p style="font-size:23px;padding-left:12px;font-weight:600;">Edit Profile</p>
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
                            <h2>Edit Profile</h2>
                            
                            <?php if($success_message): ?>
                                <div class="success-message"><?php echo $success_message; ?></div>
                            <?php endif; ?>
                            
                            <?php if($error_message): ?>
                                <div class="error-message"><?php echo $error_message; ?></div>
                            <?php endif; ?>
                            
                            <div class="profile-pic">
                                <?php 
                                $profile_pic = !empty($admin['profile_pic']) ? "../uploads/admin/" . $admin['profile_pic'] : "../img/user.png";
                                ?>
                                <img src="<?php echo $profile_pic; ?>" alt="Profile Picture" id="profileImage">
                            </div>
                            
                            <form method="POST" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label for="profile_pic">Change Profile Picture</label>
                                    <input type="file" name="profile_pic" id="profile_pic" accept="image/*">
                                    <small style="color:#666;display:block;margin-top:5px;">Allowed formats: JPG, JPEG, PNG, GIF</small>
                                </div>
                                <button type="submit" class="btn-primary">Upload Picture</button>
                            </form>
                            
                            <hr>
                            
                            <form method="POST">
                                <div class="form-group">
                                    <label for="new_email">Email Address</label>
                                    <input type="email" name="new_email" id="new_email" value="<?php echo htmlspecialchars($admin['aemail']); ?>" required>
                                </div>
                                <button type="submit" name="update_email" class="btn-primary">Update Email</button>
                            </form>
                            
                            <hr>
                            
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
    </script>
</body>
</html>