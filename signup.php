<?php
session_start();

$_SESSION["user"] = "";
$_SESSION["usertype"] = "";

// Set the new timezone
date_default_timezone_set('Asia/Kolkata');
$date = date('Y-m-d');

$_SESSION["date"] = $date;

if ($_POST) {
    $_SESSION["personal"] = array(
        'fname' => $_POST['fname'],
        'lname' => $_POST['lname'],
        'address' => $_POST['address'],
        'nic' => $_POST['nic'],
        'dob' => $_POST['dob']
    );

    header("location: create-account.php");
    exit(); // Important: Stop script execution after redirect
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
    <title>Sign Up</title>
</head>
<body>

<?php
// Display any session data for debugging (optional, remove in production)
if (isset($_SESSION["personal"])) {
    echo "<!-- Session data stored -->";
}
?>

<center>
<div class="container">
    <table border="0">
        <tr>
            <td colspan="2">
                <p class="header-text">Let's Get Started</p>
                <p class="sub-text">Add Your Personal Details to Continue</p>
            </td>
        </tr>
        <tr>
            <form action="" method="POST">
            <td class="label-td" colspan="2">
                <label for="name" class="form-label">Name: </label>
            </td>
        </tr>
        <tr>
            <td class="label-td">
                <input type="text" name="fname" class="input-text" placeholder="First Name" required>
            </td>
            <td class="label-td">
                <input type="text" name="lname" class="input-text" placeholder="Last Name" required>
            </td>
        </tr>
        <tr>
            <td class="label-td" colspan="2">
                <label for="address" class="form-label">Address: </label>
            </td>
        </tr>
        <tr>
            <td class="label-td" colspan="2">
                <input type="text" name="address" class="input-text" placeholder="Address" required>
            </td>
        </tr>
        <tr>
            <td class="label-td" colspan="2">
                <label for="nic" class="form-label">NIC: </label>
            </td>
        </tr>
        <tr>
            <td class="label-td" colspan="2">
                <input type="text" name="nic" class="input-text" placeholder="NIC Number" required>
            </td>
        </tr>
        <tr>
            <td class="label-td" colspan="2">
                <label for="dob" class="form-label">Date of Birth: </label>
            </td>
        </tr>
        <tr>
            <td class="label-td" colspan="2">
                <input type="date" name="dob" class="input-text" required>
            </td>
        </tr>
        <tr>
            <td class="label-td" colspan="2"></td>
        </tr>
        <tr>
            <td>
                <input type="reset" value="Reset" class="login-btn btn-primary-soft btn">
            </td>
            <td>
                <input type="submit" value="Next" class="login-btn btn-primary btn">
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
    const fnameField = $("input[name='fname']");
    const lnameField = $("input[name='lname']");
    const addressField = $("input[name='address']");
    const nicField = $("input[name='nic']");
    const dobField = $("input[name='dob']");
    
    // Real-time validation on keyup/change
    fnameField.on("keyup", function() { validateFirstName(); });
    lnameField.on("keyup", function() { validateLastName(); });
    addressField.on("keyup", function() { validateAddress(); });
    nicField.on("keyup", function() { validateNIC(); });
    dobField.on("change", function() { validateDOB(); });
    
    // Form submit validation
    $("form").on("submit", function(e) {
        let isValid = true;
        if (!validateFirstName()) isValid = false;
        if (!validateLastName()) isValid = false;
        if (!validateAddress()) isValid = false;
        if (!validateNIC()) isValid = false;
        if (!validateDOB()) isValid = false;
        if (!isValid) e.preventDefault();
    });
    
    function validateFirstName() {
        const fname = fnameField.val().trim();
        let isValid = true;
        removeError(fnameField);
        
        if (fname === "") {
            showError(fnameField, "First name is required");
            isValid = false;
        } else if (fname.length < 2) {
            showError(fnameField, "First name must be at least 2 characters");
            isValid = false;
        } else if (!/^[A-Za-z\s]+$/.test(fname)) {
            showError(fnameField, "First name should contain only letters");
            isValid = false;
        } else {
            fnameField.css("border", "");
        }
        return isValid;
    }
    
    function validateLastName() {
        const lname = lnameField.val().trim();
        let isValid = true;
        removeError(lnameField);
        
        if (lname === "") {
            showError(lnameField, "Last name is required");
            isValid = false;
        } else if (lname.length < 2) {
            showError(lnameField, "Last name must be at least 2 characters");
            isValid = false;
        } else if (!/^[A-Za-z\s]+$/.test(lname)) {
            showError(lnameField, "Last name should contain only letters");
            isValid = false;
        } else {
            lnameField.css("border", "");
        }
        return isValid;
    }
    
    function validateAddress() {
        const address = addressField.val().trim();
        let isValid = true;
        removeError(addressField);
        
        if (address === "") {
            showError(addressField, "Address is required");
            isValid = false;
        } else if (address.length < 5) {
            showError(addressField, "Please enter a complete address");
            isValid = false;
        } else {
            addressField.css("border", "");
        }
        return isValid;
    }
    
    function validateNIC() {
        const nic = nicField.val().trim();
        let isValid = true;
        removeError(nicField);
        
        if (nic === "") {
            showError(nicField, "NIC number is required");
            isValid = false;
        } else if (!isValidNIC(nic)) {
            showError(nicField, "Enter valid NIC (9 digits+V or 12 digits)");
            isValid = false;
        } else {
            nicField.css("border", "");
        }
        return isValid;
    }
    
    function validateDOB() {
        const dob = dobField.val();
        let isValid = true;
        removeError(dobField);
        
        if (dob === "") {
            showError(dobField, "Date of birth is required");
            isValid = false;
        } else if (!isValidAge(dob)) {
            showError(dobField, "You must be at least 18 years old");
            isValid = false;
        } else {
            dobField.css("border", "");
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
    
    function isValidNIC(nic) {
        return /^[0-9]{9}[vVxX]$/.test(nic) || /^[0-9]{12}$/.test(nic);
    }
    
    function isValidAge(dob) {
        const today = new Date();
        const birthDate = new Date(dob);
        let age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        return age >= 18;
    }
});
</script>

</body>
</html>