<!DOCTYPE html>
<html lang="en">
    <style>
        .error-msg {
            color: red;
            font-size: 13px;
            margin-top: 5px;
            display: block;
        }

        .error-border {
            border: 2px solid red !important;
            transition: 0.3s;
        }

        .success-border {
            border: 2px solid green !important;
            transition: 0.3s;
        }
    </style>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/animations.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/login.css">
        
    <title>Login</title>
    
</head>
<script src="jq/jq.min.js"></script>
<script>
$(document).ready(function() {
    const emailField = $("input[name='useremail']");
    const passwordField = $("input[name='userpassword']");
    
    // Real-time validation on keyup
    emailField.on("keyup", function() {
        validateEmail();
    });
    
    passwordField.on("keyup", function() {
        validatePassword();
    });
    
    // Form submit validation
    $("form").on("submit", function(e) {
        let isValid = true;
        if (!validateEmail()) isValid = false;
        if (!validatePassword()) isValid = false;
        if (!isValid) e.preventDefault();
    });
    
    function validateEmail() {
        const email = emailField.val().trim();
        let isValid = true;
        removeError(emailField);
        
        if (email === "") {
            showError(emailField, "Email is required");
            isValid = false;
        } else if (!isValidEmail(email)) {
            showError(emailField, "Please enter a valid email address");
            isValid = false;
        } else {
            emailField.css("border", "");
        }
        return isValid;
    }
    
    function validatePassword() {
        const password = passwordField.val();
        let isValid = true;
        removeError(passwordField);
        
        if (password === "") {
            showError(passwordField, "Password is required");
            isValid = false;
        } else {
            passwordField.css("border", "");
        }
        return isValid;
    }
    
    function showError($element, message) {
        $element.css("border", "1px solid #ff3e3e");
        if ($element.next(".error-message").length === 0) {
            $element.after('<span class="error-message" style="color: #ff3e3e; font-size: 12px; display: block; margin-top: 5px; text-align: left;">' + message + '</span>');
        }
    }
    
    function removeError($element) {
        $element.css("border", "");
        $element.next(".error-message").remove();
    }
    
    function isValidEmail(email) {
        return /^[^\s@]+@([^\s@.,]+\.)+[^\s@.,]{2,}$/.test(email);
    }
});
</script>
<body>
    <?php

    //learn from w3schools.com
    //Unset all the server side variables

    session_start();

    $_SESSION["user"]="";
    $_SESSION["usertype"]="";
    
    // Set the new timezone
    date_default_timezone_set('Asia/Kolkata');
    $date = date('Y-m-d');

    $_SESSION["date"]=$date;
    

    //import database
    include("connection.php");

    



    if($_POST){

        $email=$_POST['useremail'];
        $password=$_POST['userpassword'];
        
        $error='<label for="promter" class="form-label"></label>';

        $result= $database->query("select * from webuser where email='$email'");
        if($result->num_rows==1){
            $utype=$result->fetch_assoc()['usertype'];
            if ($utype=='p'){
                //TODO
                $checker = $database->query("select * from patient where pemail='$email' and ppassword='$password'");
                if ($checker->num_rows==1){


                    //   Patient dashbord
                    $_SESSION['user']=$email;
                    $_SESSION['usertype']='p';
                    
                    header('location: patient/index.php');

                }else{
                    $error='<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Wrong credentials: Invalid email or password</label>';
                }

            }elseif($utype=='a'){
                //TODO
                $checker = $database->query("select * from admin where aemail='$email' and apassword='$password'");
                if ($checker->num_rows==1){


                    //   Admin dashbord
                    $_SESSION['user']=$email;
                    $_SESSION['usertype']='a';
                    
                    header('location: admin/index.php');

                }else{
                    $error='<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Wrong credentials: Invalid email or password</label>';
                }


            }elseif($utype=='d'){
                //TODO
                $checker = $database->query("select * from doctor where docemail='$email' and docpassword='$password'");
                if ($checker->num_rows==1){


                    //   doctor dashbord
                    $_SESSION['user']=$email;
                    $_SESSION['usertype']='d';
                    header('location: doctor/index.php');

                }else{
                    $error='<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Wrong credentials: Invalid email or password</label>';
                }

            }
            
        }else{
            $error='<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">We cant found any acount for this email.</label>';
        }






        
    }else{
        $error='<label for="promter" class="form-label">&nbsp;</label>';
    }

    ?>





    <center>
    <div class="container">
        <table border="0" style="margin: 0;padding: 0;width: 60%;">
            <tr>
                <td>
                    <p class="header-text">Welcome Back!</p>
                </td>
            </tr>
        <div class="form-body">
            <tr>
                <td>
                    <p class="sub-text">Login with your details to continue</p>
                </td>
            </tr>
            <tr>
                <form action="" method="POST" >
                <td class="label-td">
                    <label for="useremail" class="form-label">Email: </label>
                </td>
            </tr>
            <tr>
                <td class="label-td">
                    <input type="email" name="useremail" class="input-text" placeholder="Email Address" required>
                </td>
            </tr>
            <tr>
                <td class="label-td">
                    <label for="userpassword" class="form-label">Password: </label>
                </td>
            </tr>

            <tr>
                <td class="label-td">
                    <input type="Password" name="userpassword" class="input-text" placeholder="Password" required>
                </td>
            </tr>


            <tr>
                <td><br>
                <?php echo $error ?>
                </td>
            </tr>
            <tr>
                <td class="label-td" style="text-align: right;">
                    <a href="forgot_password.php" class="hover-link1 non-style-link" style="font-size: 12px;">Forgot Password?</a>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="submit" value="Login" class="login-btn btn-primary btn">
                </td>
            </tr>
        </div>
            <tr>
                <td>
                    <br>
                    <label for="" class="sub-text" style="font-weight: 280;">Don't have an account&#63; </label>
                    <a href="signup.php" class="hover-link1 non-style-link">Sign Up</a>
                    <br><br><br>
                </td>
            </tr>
                        
                        
    
                        
                    </form>
        </table>

    </div>
</center>
</body>
</html>