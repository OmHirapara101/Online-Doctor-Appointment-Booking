<?php
session_start();

// Check if personal data exists from signup page
if (!isset($_SESSION['personal'])) {
    header("location: signup.php");
    exit();
}

$_SESSION["user"] = "";
$_SESSION["usertype"] = "";

// Set the new timezone
date_default_timezone_set('Asia/Kolkata');
$date = date('Y-m-d');

$_SESSION["date"] = $date;

//import database
include("connection.php");

$error = ''; // Initialize error variable

if ($_POST) {
    $fname = $_SESSION['personal']['fname'];
    $lname = $_SESSION['personal']['lname'];
    $name = $fname . " " . $lname;
    $address = $_SESSION['personal']['address'];
    $nic = $_SESSION['personal']['nic'];
    $dob = $_SESSION['personal']['dob'];
    $email = $_POST['newemail'];
    $tele = $_POST['tele'];
    $newpassword = $_POST['newpassword'];
    $cpassword = $_POST['cpassword'];
    
    if ($newpassword == $cpassword) {
        $sqlmain = "select * from webuser where email=?;";
        $stmt = $database->prepare($sqlmain);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $error = '<label class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Already have an account for this Email address.</label>';
        } else {
            // Insert into patient table
            $stmt = $database->prepare("INSERT INTO patient (pemail, pname, ppassword, paddress, pnic, pdob, ptel) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $email, $name, $newpassword, $address, $nic, $dob, $tele);
            $stmt->execute();
            
            // Insert into webuser table
            $stmt = $database->prepare("INSERT INTO webuser VALUES (?, 'p')");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            
            $_SESSION["user"] = $email;
            $_SESSION["usertype"] = "p";
            $_SESSION["username"] = $fname;
            
            header('Location: patient/index.php');
            exit();
        }
    } else {
        $error = '<label class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Password Confirmation Error! Reconfirm Password</label>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/animations.css">  
    <link rel="stylesheet" href="css/main.css">  
    <link rel="stylesheet" href="css/signup.css">
    <title>Create Account</title>
    <style>
        .container{
            animation: transitionIn-X 0.5s;
        }
    </style>
</head>
<body>

<center>
<div class="container">
    <table border="0" style="width: 69%;">
        <tr>
            <td colspan="2">
                <p class="header-text">Let's Get Started</p>
                <p class="sub-text">It's Okey, Now Create User Account.</p>
            </td>
        </tr>
        <tr>
            <form action="" method="POST">
            <td class="label-td" colspan="2">
                <label for="newemail" class="form-label">Email: </label>
            </td>
        </tr>
        <tr>
            <td class="label-td" colspan="2">
                <input type="email" name="newemail" class="input-text" placeholder="Email Address" required>
            </td>
        </tr>
        <tr>
            <td class="label-td" colspan="2">
                <label for="tele" class="form-label">Mobile Number: </label>
            </td>
        </tr>
        <tr>
            <td class="label-td" colspan="2">
                <input type="tel" name="tele" class="input-text" placeholder="ex: 0712345678" pattern="[0-9]{10}">
            </td>
        </tr>
        <tr>
            <td class="label-td" colspan="2">
                <label for="newpassword" class="form-label">Create New Password: </label>
            </td>
        </tr>
        <tr>
            <td class="label-td" colspan="2">
                <input type="password" name="newpassword" class="input-text" placeholder="New Password" required>
            </td>
        </tr>
        <tr>
            <td class="label-td" colspan="2">
                <label for="cpassword" class="form-label">Confirm Password: </label>
            </td>
        </tr>
        <tr>
            <td class="label-td" colspan="2">
                <input type="password" name="cpassword" class="input-text" placeholder="Confirm Password" required>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <?php echo $error; ?>
            </td>
        </tr>
        <tr>
            <td>
                <input type="reset" value="Reset" class="login-btn btn-primary-soft btn">
            </td>
            <td>
                <input type="submit" value="Sign Up" class="login-btn btn-primary btn">
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <br>
                <label for="" class="sub-text" style="font-weight: 280;">Already have an account&#63; </label>
                <a href="login.php" class="hover-link1 non-style-link">Login</a>
                <br><br><br>
            </td>
        </tr>
        </form>
    </table>
</div>
</center>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    const emailField = $("input[name='newemail']");
    const teleField = $("input[name='tele']");
    const passwordField = $("input[name='newpassword']");
    const confirmField = $("input[name='cpassword']");
    
    // Real-time validation on keyup
    emailField.on("keyup", function() { validateEmail(); });
    teleField.on("keyup", function() { validateTelephone(); });
    passwordField.on("keyup", function() { 
        validatePassword();
        if (confirmField.val() !== "") {
            validateConfirmPassword();
        }
    });
    confirmField.on("keyup", function() { validateConfirmPassword(); });
    
    // Form submit validation
    $("form").on("submit", function(e) {
        let isValid = true;
        if (!validateEmail()) isValid = false;
        if (!validateTelephone()) isValid = false;
        if (!validatePassword()) isValid = false;
        if (!validateConfirmPassword()) isValid = false;
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
            showError(emailField, "Enter a valid email address");
            isValid = false;
        } else {
            emailField.css("border", "");
        }
        return isValid;
    }
    
    function validateTelephone() {
        const telephone = teleField.val().trim();
        let isValid = true;
        removeError(teleField);
        
        if (telephone === "") {
            showError(teleField, "Mobile number is required");
            isValid = false;
        } else if (!/^\d{10}$/.test(telephone)) {
            showError(teleField, "Enter valid 10-digit mobile number");
            isValid = false;
        } else {
            teleField.css("border", "");
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
        } else if (password.length < 6) {
            showError(passwordField, "Password must be at least 6 characters");
            isValid = false;
        } else if (!isStrongPassword(password)) {
            showError(passwordField, "Password must contain uppercase, lowercase & number");
            isValid = false;
        } else {
            passwordField.css("border", "");
        }
        return isValid;
    }
    
    function validateConfirmPassword() {
        const password = passwordField.val();
        const confirm = confirmField.val();
        let isValid = true;
        removeError(confirmField);
        
        if (confirm === "") {
            showError(confirmField, "Please confirm your password");
            isValid = false;
        } else if (password !== confirm) {
            showError(confirmField, "Passwords do not match");
            isValid = false;
        } else {
            confirmField.css("border", "");
        }
        return isValid;
    }
    
    function showError($element, message) {
        $element.css("border", "1px solid #ff3e3e");
        if ($element.next(".error-message").length === 0) {
            $element.after('<span class="error-message" style="color: #ff3e3e; font-size: 12px; display: block; margin-top: 5px;">' + message + '</span>');
        }
    }
    
    function removeError($element) {
        $element.css("border", "");
        $element.next(".error-message").remove();
    }
    
    function isValidEmail(email) {
        return /^[^\s@]+@([^\s@.,]+\.)+[^\s@.,]{2,}$/.test(email);
    }
    
    function isStrongPassword(password) {
        return /[A-Z]/.test(password) && /[a-z]/.test(password) && /[0-9]/.test(password);
    }
});
</script>

</body>
</html>