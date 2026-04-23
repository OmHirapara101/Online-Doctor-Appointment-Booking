<?php
// forgot_password.php
session_start();
require 'connection.php';

// Set timezone
date_default_timezone_set('Asia/Kolkata');

// Clear any existing reset session if coming fresh
if (!isset($_GET['step']) && !isset($_POST['send_otp']) && !isset($_POST['verify_otp']) && !isset($_POST['update_password'])) {
    unset($_SESSION['reset_email']);
    unset($_SESSION['reset_usertype']);
    unset($_SESSION['reset_otp']);
    unset($_SESSION['otp_expiry']);
}

$step = isset($_GET['step']) ? $_GET['step'] : 'request';
$error = '';
$success = '';
$email = '';

// Step 1: Send OTP
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_otp'])) {
    $email = trim($_POST['email']);
    
    if (empty($email)) {
        $error = 'Please enter your email address.';
    } else {
        // Check if email exists in webuser table
        $result = $database->query("SELECT usertype FROM webuser WHERE email='$email'");
        
        if ($result->num_rows == 1) {
            $userType = $result->fetch_assoc()['usertype'];
            
            // Store email in session
            $_SESSION['reset_email'] = $email;
            $_SESSION['reset_usertype'] = $userType;
            
            // Generate 6-digit OTP
            $otp = rand(100000, 999999);
            $_SESSION['reset_otp'] = $otp;
            $_SESSION['otp_expiry'] = time() + 300; // 5 minutes expiry
            
            // Send OTP via email using PHPMailer
            require 'send_otp.php';

            if (sendOtpEmail($email, $otp)) {
                $success = 'OTP has been sent to your email address. Please check your inbox.';
                $step = 'verify';
            } else {
                // Show OTP on screen for testing
                $error = 'Email sending failed. For testing, use OTP: <strong>' . $otp . '</strong>';
                $step = 'verify'; // Still go to verify step
                // Don't unset session for testing
            }
        } else {
            $error = 'No account found with this email address.';
        }
    }
}

// Step 2: Verify OTP
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['verify_otp'])) {
    $entered_otp = trim($_POST['otp']);
    
    if (empty($entered_otp)) {
        $error = 'Please enter the OTP.';
    } elseif (!isset($_SESSION['reset_otp']) || !isset($_SESSION['otp_expiry'])) {
        $error = 'Session expired. Please request a new OTP.';
        $step = 'request';
    } elseif (time() > $_SESSION['otp_expiry']) {
        $error = 'OTP has expired. Please request a new one.';
        unset($_SESSION['reset_otp']);
        unset($_SESSION['otp_expiry']);
        $step = 'request';
    } elseif ($entered_otp == $_SESSION['reset_otp']) {
        // OTP verified successfully
        $_SESSION['otp_verified'] = true;
        $success = 'OTP verified successfully!';
        $step = 'reset';
    } else {
        $error = 'Invalid OTP. Please try again.';
    }
}

// Step 3: Update Password
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_password'])) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($new_password) || empty($confirm_password)) {
        $error = 'Please fill in all fields.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($new_password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif (!isset($_SESSION['otp_verified']) || $_SESSION['otp_verified'] !== true) {
        $error = 'Invalid session. Please start over.';
        $step = 'request';
    } elseif (!isset($_SESSION['reset_email']) || !isset($_SESSION['reset_usertype'])) {
        $error = 'Session expired. Please start over.';
        $step = 'request';
    } else {
        $email = $_SESSION['reset_email'];
        $userType = $_SESSION['reset_usertype'];
        
        // Update password based on user type
        if ($userType == 'p') {
            $update = $database->query("UPDATE patient SET ppassword='$new_password' WHERE pemail='$email'");
        } elseif ($userType == 'd') {
            $update = $database->query("UPDATE doctor SET docpassword='$new_password' WHERE docemail='$email'");
        } elseif ($userType == 'a') {
            $update = $database->query("UPDATE admin SET apassword='$new_password' WHERE aemail='$email'");
        }
        
        if ($update) {
            // Clear session variables
            unset($_SESSION['reset_email']);
            unset($_SESSION['reset_usertype']);
            unset($_SESSION['reset_otp']);
            unset($_SESSION['otp_expiry']);
            unset($_SESSION['otp_verified']);
            
            $success = 'Password updated successfully! You can now login with your new password.';
            $step = 'success';
        } else {
            $error = 'Failed to update password. Please try again.';
        }
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
    <link rel="stylesheet" href="css/login.css">
    <title>Forgot Password</title>
    <style>
        .otp-input {
            letter-spacing: 10px;
            font-size: 24px;
            text-align: center;
            font-weight: bold;
        }
        .timer-text {
            color: #666;
            font-size: 12px;
            margin-top: 5px;
        }
        .resend-link {
            margin-top: 10px;
            text-align: center;
        }
        .resend-link a {
            color: #2c3e50;
            text-decoration: none;
            font-size: 14px;
        }
        .resend-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <center>
        <div class="container">
            <table border="0" style="margin: 0;padding: 0;width: 60%;">
                <tr>
                    <td>
                        <p class="header-text">Forgot Password</p>
                    </td>
                </tr>
                <div class="form-body">
                    <?php if ($step == 'request'): ?>
                        <!-- Step 1: Request OTP -->
                        <tr>
                            <td>
                                <p class="sub-text">Enter your registered email to receive OTP</p>
                            </td>
                        </tr>
                        <form action="forgot_password.php?step=request" method="POST">
                            <tr>
                                <td class="label-td">
                                    <label for="email" class="form-label">Email: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td">
                                    <input type="email" name="email" class="input-text" placeholder="Email Address" value="<?php echo htmlspecialchars($email); ?>" required>
                                </td>
                            </tr>
                            <tr>
                                <td><br>
                                    <?php if ($error): ?>
                                        <label class="form-label" style="color:rgb(255, 62, 62);text-align:center;"><?php echo $error; ?></label>
                                    <?php endif; ?>
                                    <?php if ($success): ?>
                                        <label class="form-label" style="color:green;text-align:center;"><?php echo $success; ?></label>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <input type="submit" name="send_otp" value="Send OTP" class="login-btn btn-primary btn">
                                </td>
                            </tr>
                        </form>
                        <tr>
                            <td>
                                <br>
                                <label class="sub-text" style="font-weight: 280;">Remember your password? </label>
                                <a href="login.php" class="hover-link1 non-style-link">Login</a>
                                <br><br><br>
                            </td>
                        </tr>

                    <?php elseif ($step == 'verify'): ?>
                        <!-- Step 2: Verify OTP -->
                        <tr>
                            <td>
                                <p class="sub-text">Enter the OTP sent to <?php echo htmlspecialchars($_SESSION['reset_email']); ?></p>
                            </td>
                        </tr>
                        <form action="forgot_password.php?step=verify" method="POST">
                            <tr>
                                <td class="label-td">
                                    <label for="otp" class="form-label">OTP: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td">
                                    <input type="text" name="otp" class="input-text otp-input" placeholder="000000" maxlength="6" required>
                                </td>
                            </tr>
                            <tr>
                                <td class="timer-text" id="timer">OTP expires in: 05:00</td>
                            </tr>
                            <tr>
                                <td><br>
                                    <?php if ($error): ?>
                                        <label class="form-label" style="color:rgb(255, 62, 62);text-align:center;"><?php echo $error; ?></label>
                                    <?php endif; ?>
                                    <?php if ($success): ?>
                                        <label class="form-label" style="color:green;text-align:center;"><?php echo $success; ?></label>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <input type="submit" name="verify_otp" value="Verify OTP" class="login-btn btn-primary btn">
                                </td>
                            </tr>
                        </form>
                        <tr>
                            <td class="resend-link">
                                <a href="forgot_password.php?step=request">Didn't receive OTP? Request Again</a>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <br>
                                <label class="sub-text" style="font-weight: 280;">Remember your password? </label>
                                <a href="login.php" class="hover-link1 non-style-link">Login</a>
                                <br><br><br>
                            </td>
                        </tr>
                        
                        <script>
                            // Timer for OTP expiry
                            let expiryTime = <?php echo $_SESSION['otp_expiry']; ?> * 1000;
                            function updateTimer() {
                                let now = new Date().getTime();
                                let distance = expiryTime - now;
                                
                                if (distance < 0) {
                                    document.getElementById('timer').innerHTML = "OTP has expired! Please request a new one.";
                                    document.getElementById('timer').style.color = "red";
                                    return;
                                }
                                
                                let minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                                let seconds = Math.floor((distance % (1000 * 60)) / 1000);
                                
                                document.getElementById('timer').innerHTML = "OTP expires in: " + 
                                    (minutes < 10 ? "0" + minutes : minutes) + ":" + 
                                    (seconds < 10 ? "0" + seconds : seconds);
                            }
                            
                            updateTimer();
                            setInterval(updateTimer, 1000);
                        </script>

                    <?php elseif ($step == 'reset'): ?>
                        <!-- Step 3: Reset Password -->
                        <tr>
                            <td>
                                <p class="sub-text">Set your new password</p>
                            </td>
                        </tr>
                        <form action="forgot_password.php?step=reset" method="POST">
                            <tr>
                                <td class="label-td">
                                    <label for="new_password" class="form-label">New Password: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td">
                                    <input type="password" name="new_password" class="input-text" placeholder="New Password (min. 6 characters)" required>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td">
                                    <label for="confirm_password" class="form-label">Confirm Password: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td">
                                    <input type="password" name="confirm_password" class="input-text" placeholder="Confirm Password" required>
                                </td>
                            </tr>
                            <tr>
                                <td><br>
                                    <?php if ($error): ?>
                                        <label class="form-label" style="color:rgb(255, 62, 62);text-align:center;"><?php echo $error; ?></label>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <input type="submit" name="update_password" value="Update Password" class="login-btn btn-primary btn">
                                </td>
                            </tr>
                        </form>
                        <tr>
                            <td>
                                <br>
                                <label class="sub-text" style="font-weight: 280;">Remember your password? </label>
                                <a href="login.php" class="hover-link1 non-style-link">Login</a>
                                <br><br><br>
                            </td>
                        </tr>

                    <?php elseif ($step == 'success'): ?>
                        <!-- Success Message -->
                        <tr>
                            <td>
                                <p class="sub-text" style="color:green;"><?php echo $success; ?></p>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <a href="login.php" class="login-btn btn-primary btn" style="text-decoration:none; display:inline-block;">Go to Login</a>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <br><br><br>
                            </td>
                        </tr>
                    <?php endif; ?>
                </div>
            </table>
        </div>
    </center>
</body>
</html>