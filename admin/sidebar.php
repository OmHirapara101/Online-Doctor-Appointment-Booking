<?php
$current_page = basename($_SERVER['PHP_SELF']);

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

// Fetch admin data based on session email
$admin_email = isset($_SESSION['user']) ? $_SESSION['user'] : "";
$sql = "SELECT aemail, profile_pic FROM admin WHERE aemail = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $admin_email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $admin = $result->fetch_assoc();
    $admin_name = "Administrator";
    $admin_email_display = $admin['aemail'];
    $profile_pic = !empty($admin['profile_pic']) ? "../uploads/admin/" . $admin['profile_pic'] : "../img/user.png";
} else {
    $admin_name = "Administrator";
    $admin_email_display = "";
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
                            <td width="30%" style="padding-left:20px">
                                <img src="<?php echo $profile_pic; ?>" alt="" width="100%" style="border-radius:50%">
                            </td>
                            <td style="padding:0px;margin:0px;">
                                <p class="profile-title"><?php echo $admin_name; ?></p>
                                <p class="profile-subtitle"><?php echo $admin_email_display; ?></p>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <a href="../logout.php"><input type="button" value="Log out" class="logout-btn btn-primary-soft btn"></a>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr class="menu-row">
                <td class="menu-btn menu-icon-dashbord <?php echo ($current_page == 'index.php') ? 'menu-active menu-icon-dashbord-active' : ''; ?>">
                    <a href="index.php" class="non-style-link-menu <?php echo ($current_page == 'index.php') ? 'non-style-link-menu-active' : ''; ?>">
                        <div>
                            <p class="menu-text">Dashboard</p>
                    </a>
    </div>
    </td>
    </tr>
    <tr class="menu-row">
        <td class="menu-btn menu-icon-doctor <?php echo ($current_page == 'doctors.php') ? 'menu-active menu-icon-doctor-active' : ''; ?>">
            <a href="doctors.php" class="non-style-link-menu <?php echo ($current_page == 'doctors.php') ? 'non-style-link-menu-active' : ''; ?>">
                <div>
                    <p class="menu-text">Doctors</p>
            </a>
</div>
</td>
</tr>
<tr class="menu-row">
    <td class="menu-btn menu-icon-schedule <?php echo ($current_page == 'schedule.php') ? 'menu-active menu-icon-schedule-active' : ''; ?>">
        <a href="schedule.php" class="non-style-link-menu <?php echo ($current_page == 'schedule.php') ? 'non-style-link-menu-active' : ''; ?>">
            <div>
                <p class="menu-text">Schedule</p>
            </div>
        </a>
    </td>
</tr>
<tr class="menu-row">
    <td class="menu-btn menu-icon-appoinment <?php echo ($current_page == 'appointment.php') ? 'menu-active menu-icon-appoinment-active' : ''; ?>">
        <a href="appointment.php" class="non-style-link-menu <?php echo ($current_page == 'appointment.php') ? 'non-style-link-menu-active' : ''; ?>">
            <div>
                <p class="menu-text">Appointment</p>
        </a></div>
    </td>
</tr>
<tr class="menu-row">
    <td class="menu-btn menu-icon-patient <?php echo ($current_page == 'patient.php') ? 'menu-active menu-icon-patient-active' : ''; ?>">
        <a href="patient.php" class="non-style-link-menu <?php echo ($current_page == 'patient.php') ? 'non-style-link-menu-active' : ''; ?>">
            <div>
                <p class="menu-text">Patients</p>
        </a></div>
    </td>
</tr>
<tr class="menu-row">
    <td class="menu-btn menu-icon-settings <?php echo ($current_page == 'edit_profile.php') ? 'menu-active' : ''; ?>">
        <a href="edit_profile.php" class="non-style-link-menu <?php echo ($current_page == 'edit_profile.php') ? 'non-style-link-menu-active' : ''; ?>">
            <div>
                <p class="menu-text">Edit Profile</p>
        </a></div>
    </td>
</tr>
</table>
</div>