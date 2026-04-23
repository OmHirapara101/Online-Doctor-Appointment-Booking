<?php

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "edoc";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get current page name
$current_page = basename($_SERVER['PHP_SELF']);

// Get doctor email from session (you need to set this during login)
$useremail = isset($_SESSION['user']) ? $_SESSION['user'] : "";

// Fetch doctor details including photo
if (!empty($useremail)) {
    // Use prepared statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM doctor WHERE docemail = ?");
    $stmt->bind_param("s", $useremail);
    $stmt->execute();
    $userrow = $stmt->get_result();
    $userfetch = $userrow->fetch_assoc();
    
    if ($userfetch) {
        $userid = $userfetch["docid"];
        $username = $userfetch["docname"];
        $useremail = $userfetch["docemail"];
            $profile_pic = !empty($userfetch["photo"]) ? "../uploads/doctors/" . $userfetch["photo"] : "../img/user.png";
            // $profile_pic = "../uploads/doctors/".$doctor_photo;
        
    } else {
        $username = "Doctor";
        $useremail = "";
        $profile_pic = "../img/user.png";
    }
    $stmt->close();
} else {
    $username = "Doctor";
    $useremail = "";
    $profile_pic = "../img/user.png";
}
?>

<div class="container">
        <div class="menu">
            <table class="menu-container" border="0">
                 <tr>
                    <td style="padding:10px" colspan="2">
                        <table border="0" class="profile-container">
                            <tr>
                                <td width="30%" style="padding-left:20px" >
                                    <img src="<?php echo $profile_pic; ?>" alt="" width="100%" style="border-radius:50%">
                                  </td>
                                <td style="padding:0px;margin:0px;">
                                    <p class="profile-title"><?php echo substr($username,0,13)  ?></p>
                                    <p class="profile-subtitle"><?php echo substr($useremail,0,22)  ?></p>
                                  </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <a href="../logout.php" ><input type="button" value="Log out" class="logout-btn btn-primary-soft btn"></a>
                                  </td>
                            </tr>
                        </table>
                      </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-dashbord <?php echo ($current_page == 'index.php') ? 'menu-active menu-icon-dashbord-active' : ''; ?>">
                        <a href="index.php" class="non-style-link-menu <?php echo ($current_page == 'index.php') ? 'non-style-link-menu-active' : ''; ?>"><div><p class="menu-text">Dashboard</p></div></a>
                      </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-appoinment <?php echo ($current_page == 'appointment.php') ? 'menu-active menu-icon-appoinment-active' : ''; ?>">
                        <a href="appointment.php" class="non-style-link-menu <?php echo ($current_page == 'appointment.php') ? 'non-style-link-menu-active' : ''; ?>"><div><p class="menu-text">My Appointments</p></div></a>
                      </td>
                </tr>
                
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-session <?php echo ($current_page == 'schedule.php' || $current_page == 'add-session.php') ? 'menu-active menu-icon-session-active' : ''; ?>">
                        <a href="schedule.php" class="non-style-link-menu <?php echo ($current_page == 'schedule.php') ? 'non-style-link-menu-active' : ''; ?>"><div><p class="menu-text">My Sessions</p></div></a>
                      </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-patient <?php echo ($current_page == 'patient.php') ? 'menu-active menu-icon-patient-active' : ''; ?>">
                        <a href="patient.php" class="non-style-link-menu <?php echo ($current_page == 'patient.php') ? 'non-style-link-menu-active' : ''; ?>"><div><p class="menu-text">My Patients</p></div></a>
                      </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-settings <?php echo ($current_page == 'settings.php' || $current_page == 'edit_profile.php') ? 'menu-active menu-icon-settings-active' : ''; ?>">
                        <a href="settings.php" class="non-style-link-menu <?php echo ($current_page == 'settings.php') ? 'non-style-link-menu-active' : ''; ?>"><div><p class="menu-text">Settings</p></div></a>
                      </td>
                </tr>
                
            </table>
        </div>