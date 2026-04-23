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
    <title>Doctors</title>
    <style>
        .popup{
            animation: transitionIn-Y-bottom 0.5s;
        }
        .sub-table{
            animation: transitionIn-Y-bottom 0.5s;
        }
        /* jQuery Validation Styles */
        label.error {
            color: #dc3545 !important;
            font-size: 12px !important;
            margin-top: 5px !important;
            display: block !important;
            font-weight: normal !important;
        }
        input.error, select.error {
            border-color: #dc3545 !important;
            background-color: #fff8f8 !important;
        }
        input.valid, select.valid {
            border-color: #28a745 !important;
            background-color: #f0fff4 !important;
        }
        .validation-message {
            color: #dc3545;
            font-size: 12px;
            margin-top: 5px;
            display: none;
        }
    </style>
</head>
<body>
    <?php

    //learn from w3schools.com

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

    
    ?>
    <?php include("sidebar.php");?>
        <div class="dash-body">
            <table border="0" width="100%" style=" border-spacing: 0;margin:0;padding:0;margin-top:25px; ">
                <tr >
                    <td width="13%">
                        <a href="doctors.php" ><button  class="login-btn btn-primary-soft btn btn-icon-back"  style="padding-top:11px;padding-bottom:11px;margin-left:20px;width:125px"><font class="tn-in-text">Back</font></button></a>
                     </td>
                    <td>
                        
                        <form action="" method="post" class="header-search">

                            <input type="search" name="search" class="input-text header-searchbar" placeholder="Search Doctor name or Email" list="doctors">&nbsp;&nbsp;
                            
                            <?php
                                echo '<datalist id="doctors">';
                                $list11 = $database->query("select  docname,docemail from  doctor;");

                                for ($y=0;$y<$list11->num_rows;$y++){
                                    $row00=$list11->fetch_assoc();
                                    $d=$row00["docname"];
                                    $c=$row00["docemail"];
                                    echo "<option value='$d'><br/>";
                                    echo "<option value='$c'><br/>";
                                };

                            echo ' </datalist>';
?>
                            
                       
                            <input type="Submit" value="Search" class="login-btn btn-primary btn" style="padding-left: 25px;padding-right: 25px;padding-top: 10px;padding-bottom: 10px;">
                        
                        </form>
                        
                     </td>
                    <td width="15%">
                        <p style="font-size: 14px;color: rgb(119, 119, 119);padding: 0;margin: 0;text-align: right;">
                            Today's Date
                        </p>
                        <p class="heading-sub12" style="padding: 0;margin: 0;">
                            <?php 
                        date_default_timezone_set('Asia/Kolkata');

                        $date = date('Y-m-d');
                        echo $date;
                        ?>
                        </p>
                     </td>
                    <td width="10%">
                        <button  class="btn-label"  style="display: flex;justify-content: center;align-items: center;"><img src="../img/calendar.svg" width="100%"></button>
                     </td>


                 </tr>
               
                <tr >
                    <td colspan="2" style="padding-top:30px;">
                        <p class="heading-main12" style="margin-left: 45px;font-size:20px;color:rgb(49, 49, 49)">Add New Doctor</p>
                     </td>
                    <td colspan="2">
                        <a href="?action=add&id=none&error=0" class="non-style-link"><button  class="login-btn btn-primary btn button-icon"  style="display: flex;justify-content: center;align-items: center;margin-left:75px;background-image: url('../img/icons/add.svg');">Add New</font></button>
                            </a> </td>
                <tr>
                <tr >
                    <td colspan="4" style="padding-top:10px;">
                        <p class="heading-main12" style="margin-left: 45px;font-size:18px;color:rgb(49, 49, 49)">All Doctors (<?php echo $list11->num_rows; ?>)</p>
                     </td>
                    
                 </tr>
                <?php
                    if($_POST){
                        $keyword=$_POST["search"];
                        
                        $sqlmain= "select * from doctor where docemail='$keyword' or docname='$keyword' or docname like '$keyword%' or docname like '%$keyword' or docname like '%$keyword%'";
                    }else{
                        $sqlmain= "select * from doctor order by docid desc";

                    }



                ?>
                  
                 <tr>
                   <td colspan="4">
                       <center>
                        <div class="abc scroll">
                        <table width="93%" class="sub-table scrolldown" border="0">
                        <thead>
                         <tr>
                                <th class="table-headin">
                                    
                                
                                Doctor Name
                                
                                </th>
                                <th class="table-headin">
                                    Email
                                </th>
                                <th class="table-headin">
                                    
                                    Specialties
                                    
                                </th>
                                <th class="table-headin">
                                    
                                    Events
                                    
                                 </th>
                        </thead>
                        <tbody>
                        
                            <?php

                                
                                $result= $database->query($sqlmain);

                                if($result->num_rows==0){
                                    echo '<tr>
                                    <td colspan="4">
                                    <br><br><br><br>
                                    <center>
                                    <img src="../img/notfound.svg" width="25%">
                                    
                                    <br>
                                    <p class="heading-main12" style="margin-left: 45px;font-size:20px;color:rgb(49, 49, 49)">We  couldnt find anything related to your keywords !</p>
                                    <a class="non-style-link" href="doctors.php"><button  class="login-btn btn-primary-soft btn"  style="display: flex;justify-content: center;align-items: center;margin-left:20px;">&nbsp; Show all Doctors &nbsp;</font></button>
                                    </a>
                                    </center>
                                    <br><br><br><br>
                                    </td>
                                     </tr>';
                                    
                                }
                                else{
                                for ( $x=0; $x<$result->num_rows;$x++){
                                    $row=$result->fetch_assoc();
                                    $docid=$row["docid"];
                                    $name=$row["docname"];
                                    $email=$row["docemail"];
                                    $spe=$row["specialties"];
                                    $spcil_res= $database->query("select sname from specialties where id='$spe'");
                                    $spcil_array= $spcil_res->fetch_assoc();
                                    $spcil_name=$spcil_array["sname"];
                                    echo '<tr>
                                         <td> &nbsp;'.
                                        substr($name,0,30)
                                        .'</a></td>
                                         <td>
                                        '.substr($email,0,20).'
                                         </td>
                                         <td>
                                            '.substr($spcil_name,0,20).'
                                         </td>

                                         <td>
                                        <div style="display:flex;justify-content: center;">
                                        <a href="?action=edit&id='.$docid.'&error=0" class="non-style-link"><button  class="btn-primary-soft btn button-icon btn-edit"  style="padding-left: 40px;padding-top: 12px;padding-bottom: 12px;margin-top: 10px;"><font class="tn-in-text">Edit</font></button></a>
                                        &nbsp;&nbsp;&nbsp;
                                        <a href="?action=view&id='.$docid.'" class="non-style-link"><button  class="btn-primary-soft btn button-icon btn-view"  style="padding-left: 40px;padding-top: 12px;padding-bottom: 12px;margin-top: 10px;"><font class="tn-in-text">View</font></button></a>
                                       &nbsp;&nbsp;&nbsp;
                                       <a href="?action=drop&id='.$docid.'&name='.$name.'" class="non-style-link"><button  class="btn-primary-soft btn button-icon btn-delete"  style="padding-left: 40px;padding-top: 12px;padding-bottom: 12px;margin-top: 10px;"><font class="tn-in-text">Remove</font></button></a>
                                        </div>
                                        </td>
                                     </tr>';
                                    
                                }
                            }
                                 
                            ?>
 
                            </tbody>

                        </table>
                        </div>
                        </center>
                    </td> 
                </tr>
                       
                        
                        
             </table>
        </div>
    </div>
    <?php 
    if($_GET){
        
        $id=$_GET["id"];
        $action=$_GET["action"];
        if($action=='drop'){
            $nameget=$_GET["name"];
            echo '
            <div id="popup1" class="overlay">
                    <div class="popup">
                    <center>
                        <h2>Are you sure?</h2>
                        <a class="close" href="doctors.php">&times;</a>
                        <div class="content">
                            You want to delete this record<br>('.substr($nameget,0,40).').
                            
                        </div>
                        <div style="display: flex;justify-content: center;">
                        <a href="delete-doctor.php?id='.$id.'" class="non-style-link"><button  class="btn-primary btn"  style="display: flex;justify-content: center;align-items: center;margin:10px;padding:10px;"<font class="tn-in-text">&nbsp;Yes&nbsp;</font></button></a>&nbsp;&nbsp;&nbsp;
                        <a href="doctors.php" class="non-style-link"><button  class="btn-primary btn"  style="display: flex;justify-content: center;align-items: center;margin:10px;padding:10px;"><font class="tn-in-text">&nbsp;&nbsp;No&nbsp;&nbsp;</font></button></a>

                        </div>
                    </center>
            </div>
            </div>
            ';
        }elseif($action=='view'){
            $sqlmain= "select * from doctor where docid='$id'";
            $result= $database->query($sqlmain);
            $row=$result->fetch_assoc();
            $name=$row["docname"];
            $email=$row["docemail"];
            $spe=$row["specialties"];
            
            $spcil_res= $database->query("select sname from specialties where id='$spe'");
            $spcil_array= $spcil_res->fetch_assoc();
            $spcil_name=$spcil_array["sname"];
            $nic=$row['docnic'];
            $tele=$row['doctel'];
            echo '
            <div id="popup1" class="overlay">
                    <div class="popup">
                    <center>
                        <h2></h2>
                        <a class="close" href="doctors.php">&times;</a>
                        <div class="content">
                            eDoc Web App<br>
                            
                        </div>
                        <div style="display: flex;justify-content: center;">
                        <table width="80%" class="sub-table scrolldown add-doc-form-container" border="0">
                        
                             <tr>
                                <td>
                                    <p style="padding: 0;margin: 0;text-align: left;font-size: 25px;font-weight: 500;">View Details.</p><br><br>
                                </td>
                             </tr>
                            
                             <tr>
                                
                                <td class="label-td" colspan="2">
                                    <label for="name" class="form-label">Name: </label>
                                </td>
                             </tr>
                             <tr>
                                <td class="label-td" colspan="2">
                                    '.$name.'<br><br>
                                </td>
                                
                             </tr>
                             <tr>
                                <td class="label-td" colspan="2">
                                    <label for="Email" class="form-label">Email: </label>
                                </td>
                             </tr>
                             <tr>
                                <td class="label-td" colspan="2">
                                '.$email.'<br><br>
                                </td>
                             </tr>
                             <tr>
                                <td class="label-td" colspan="2">
                                    <label for="nic" class="form-label">NIC: </label>
                                </td>
                             </tr>
                             <tr>
                                <td class="label-td" colspan="2">
                                '.$nic.'<br><br>
                                </td>
                             </tr>
                             <tr>
                                <td class="label-td" colspan="2">
                                    <label for="Tele" class="form-label">Telephone: </label>
                                </td>
                             </tr>
                             <tr>
                                <td class="label-td" colspan="2">
                                '.$tele.'<br><br>
                                </td>
                             </tr>
                             <tr>
                                <td class="label-td" colspan="2">
                                    <label for="spec" class="form-label">Specialties: </label>
                                    
                                </td>
                             </tr>
                             <tr>
                            <td class="label-td" colspan="2">
                            '.$spcil_name.'<br><br>
                            </td>
                             </tr>
                             <tr>
                                <td colspan="2">
                                    <a href="doctors.php"><input type="button" value="OK" class="login-btn btn-primary-soft btn" ></a>
                                
                                    
                                </td>
                
                             </tr>
                           

                        </table>
                        </div>
                    </center>
                    <br><br>
            </div>
            </div>
            ';
        }elseif($action=='add'){
                $error_1=$_GET["error"];
                $errorlist= array(
                    '1'=>'<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Already have an account for this Email address.</label>',
                    '2'=>'<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Password Conformation Error! Reconform Password</label>',
                    '3'=>'<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;"></label>',
                    '4'=>"",
                    '0'=>'',

                );
                if($error_1!='4'){
                echo '
            <div id="popup1" class="overlay">
                    <div class="popup">
                    <center>
                    
                        <a class="close" href="doctors.php">&times;</a> 
                        <div style="display: flex;justify-content: center;">
                        <div class="abc">
                        <table width="80%" class="sub-table scrolldown add-doc-form-container" border="0">
                         <tr>
                                <td class="label-td" colspan="2">'.
                                    $errorlist[$error_1]
                                .'</a></td>
                             </tr>
                             <tr>
                                <td>
                                    <p style="padding: 0;margin: 0;text-align: left;font-size: 25px;font-weight: 500;">Add New Doctor.</p><br><br>
                                 </a>
                             </tr>
                            
                             <tr>
                                <form action="add-new.php" method="POST" class="add-new-form" id="addDoctorForm">
                                <td class="label-td" colspan="2">
                                    <label for="name" class="form-label">Name: </label>
                                 </a>
                             </tr>
                             <tr>
                                <td class="label-td" colspan="2">
                                    <input type="text" name="name" id="name" class="input-text" placeholder="Doctor Name">
                                    <label for="name" class="validation-message" id="name-error" generated="true" style="display: none;"></label>
                                    <br>
                                 </a>
                                
                             </tr>
                             <tr>
                                <td class="label-td" colspan="2">
                                    <label for="Email" class="form-label">Email: </label>
                                 </a>
                             </tr>
                             <tr>
                                <td class="label-td" colspan="2">
                                    <input type="email" name="email" id="email" class="input-text" placeholder="Email Address">
                                    <label for="email" class="validation-message" id="email-error" generated="true" style="display: none;"></label>
                                    <br>
                                 </a>
                             </tr>
                             <tr>
                                <td class="label-td" colspan="2">
                                    <label for="nic" class="form-label">NIC: </label>
                                 </a>
                             </tr>
                             <tr>
                                <td class="label-td" colspan="2">
                                    <input type="text" name="nic" id="nic" class="input-text" placeholder="NIC Number">
                                    <label for="nic" class="validation-message" id="nic-error" generated="true" style="display: none;"></label>
                                    <br>
                                 </a>
                             </tr>
                             <tr>
                                <td class="label-td" colspan="2">
                                    <label for="Tele" class="form-label">Telephone: </label>
                                 </a>
                             </tr>
                             <tr>
                                <td class="label-td" colspan="2">
                                    <input type="tel" name="Tele" id="Tele" class="input-text" placeholder="Telephone Number">
                                    <label for="Tele" class="validation-message" id="Tele-error" generated="true" style="display: none;"></label>
                                    <br>
                                 </a>
                             </tr>
                             <tr>
                                <td class="label-td" colspan="2">
                                    <label for="spec" class="form-label">Choose specialties: </label>
                                    
                                 </a>
                             </tr>
                             <tr>
                                <td class="label-td" colspan="2">
                                    <select name="spec" id="spec" class="box">';
                                        
        
                                        $list11 = $database->query("select  * from  specialties order by sname asc;");
        
                                        for ($y=0;$y<$list11->num_rows;$y++){
                                            $row00=$list11->fetch_assoc();
                                            $sn=$row00["sname"];
                                            $id00=$row00["id"];
                                            echo "<option value=".$id00.">$sn</option><br/>";
                                        };
        
        
        
                                        
                        echo     '       </select>
                                    <label for="spec" class="validation-message" id="spec-error" generated="true" style="display: none;"></label>
                                    <br>
                                 </a>
                             </tr>
                             <tr>
                                <td class="label-td" colspan="2">
                                    <label for="password" class="form-label">Password: </label>
                                 </a>
                             </tr>
                             <tr>
                                <td class="label-td" colspan="2">
                                    <input type="password" name="password" id="password" class="input-text" placeholder="Define a Password">
                                    <label for="password" class="validation-message" id="password-error" generated="true" style="display: none;"></label>
                                    <br>
                                 </a>
                             </tr>
                             <tr>
                                <td class="label-td" colspan="2">
                                    <label for="cpassword" class="form-label">Confirm Password: </label>
                                 </a>
                             </tr>
                             <tr>
                                <td class="label-td" colspan="2">
                                    <input type="password" name="cpassword" id="cpassword" class="input-text" placeholder="Confirm Password">
                                    <label for="cpassword" class="validation-message" id="cpassword-error" generated="true" style="display: none;"></label>
                                    <br>
                                 </a>
                             </tr>
                            

                             <tr>
                                <td colspan="2">
                                    <input type="reset" value="Reset" class="login-btn btn-primary-soft btn" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                
                                    <input type="submit" value="Add" class="login-btn btn-primary btn">
                                 </a>
                
                             </tr>
                           
                            </form>
                             <tr>
                        </table>
                        </div>
                        </div>
                    </center>
                    <br><br>
            </div>
            </div>
            ';

            }else{
                echo '
                    <div id="popup1" class="overlay">
                            <div class="popup">
                            <center>
                            <br><br><br><br>
                                <h2>New Record Added Successfully!</h2>
                                <a class="close" href="doctors.php">&times;</a>
                                <div class="content">
                                    
                                    
                                </div>
                                <div style="display: flex;justify-content: center;">
                                
                                <a href="doctors.php" class="non-style-link"><button  class="btn-primary btn"  style="display: flex;justify-content: center;align-items: center;margin:10px;padding:10px;"><font class="tn-in-text">&nbsp;&nbsp;OK&nbsp;&nbsp;</font></button></a>

                                </div>
                                <br><br>
                            </center>
                    </div>
                    </div>
        ';
            }
        }elseif($action=='edit'){
            $sqlmain= "select * from doctor where docid='$id'";
            $result= $database->query($sqlmain);
            $row=$result->fetch_assoc();
            $name=$row["docname"];
            $email=$row["docemail"];
            $spe=$row["specialties"];
            
            $spcil_res= $database->query("select sname from specialties where id='$spe'");
            $spcil_array= $spcil_res->fetch_assoc();
            $spcil_name=$spcil_array["sname"];
            $nic=$row['docnic'];
            $tele=$row['doctel'];

            $error_1=$_GET["error"];
                $errorlist= array(
                    '1'=>'<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Already have an account for this Email address.</label>',
                    '2'=>'<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Password Conformation Error! Reconform Password</label>',
                    '3'=>'<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;"></label>',
                    '4'=>"",
                    '0'=>'',

                );

            if($error_1!='4'){
                    echo '
                    <div id="popup1" class="overlay">
                            <div class="popup">
                            <center>
                            
                                <a class="close" href="doctors.php">&times;</a> 
                                <div style="display: flex;justify-content: center;">
                                <div class="abc">
                                <table width="80%" class="sub-table scrolldown add-doc-form-container" border="0">
                                 <tr>
                                        <td class="label-td" colspan="2">'.
                                            $errorlist[$error_1]
                                        .'</a></td>
                                     </tr>
                                     <tr>
                                        <td>
                                            <p style="padding: 0;margin: 0;text-align: left;font-size: 25px;font-weight: 500;">Edit Doctor Details.</p>
                                        Doctor ID : '.$id.' (Auto Generated)<br><br>
                                         </a>
                                     </tr>
                                     <tr>
                                        <td class="label-td" colspan="2">
                                            <form action="edit-doc.php" method="POST" class="add-new-form" id="editDoctorForm">
                                            <label for="Email" class="form-label">Email: </label>
                                            <input type="hidden" value="'.$id.'" name="id00">
                                            <input type="hidden" name="oldemail" value="'.$email.'" >
                                         </a>
                                     </tr>
                                     <tr>
                                        <td class="label-td" colspan="2">
                                        <input type="email" name="email" id="email" class="input-text" placeholder="Email Address" value="'.$email.'">
                                        <label for="email" class="validation-message" id="email-error" generated="true" style="display: none;"></label>
                                        <br>
                                         </a>
                                     </tr>
                                     <tr>
                                        
                                        <td class="label-td" colspan="2">
                                            <label for="name" class="form-label">Name: </label>
                                         </a>
                                     </tr>
                                     <tr>
                                        <td class="label-td" colspan="2">
                                            <input type="text" name="name" id="name" class="input-text" placeholder="Doctor Name" value="'.$name.'">
                                            <label for="name" class="validation-message" id="name-error" generated="true" style="display: none;"></label>
                                            <br>
                                         </a>
                                        
                                     </tr>
                                    
                                     <tr>
                                        <td class="label-td" colspan="2">
                                            <label for="nic" class="form-label">NIC: </label>
                                         </a>
                                     </tr>
                                     <tr>
                                        <td class="label-td" colspan="2">
                                            <input type="text" name="nic" id="nic" class="input-text" placeholder="NIC Number" value="'.$nic.'">
                                            <label for="nic" class="validation-message" id="nic-error" generated="true" style="display: none;"></label>
                                            <br>
                                         </a>
                                     </tr>
                                     <tr>
                                        <td class="label-td" colspan="2">
                                            <label for="Tele" class="form-label">Telephone: </label>
                                         </a>
                                     </tr>
                                     <tr>
                                        <td class="label-td" colspan="2">
                                            <input type="tel" name="Tele" id="Tele" class="input-text" placeholder="Telephone Number" value="'.$tele.'">
                                            <label for="Tele" class="validation-message" id="Tele-error" generated="true" style="display: none;"></label>
                                            <br>
                                         </a>
                                     </tr>
                                     <tr>
                                        <td class="label-td" colspan="2">
                                            <label for="spec" class="form-label">Choose specialties: (Current: '.$spcil_name.')</label>
                                            
                                         </a>
                                     </tr>
                                     <tr>
                                        <td class="label-td" colspan="2">
                                            <select name="spec" id="spec" class="box">';
                                                
                
                                                $list11 = $database->query("select  * from  specialties;");
                
                                                for ($y=0;$y<$list11->num_rows;$y++){
                                                    $row00=$list11->fetch_assoc();
                                                    $sn=$row00["sname"];
                                                    $id00=$row00["id"];
                                                    $selected = ($id00 == $spe) ? 'selected' : '';
                                                    echo "<option value=".$id00." $selected>$sn</option><br/>";
                                                };
                
                
                
                                                
                                echo     '       </select>
                                            <label for="spec" class="validation-message" id="spec-error" generated="true" style="display: none;"></label>
                                            <br><br>
                                         </a>
                                     </tr>
                                     <tr>
                                        <td class="label-td" colspan="2">
                                            <label for="password" class="form-label">Password: </label>
                                         </a>
                                     </tr>
                                     <tr>
                                        <td class="label-td" colspan="2">
                                            <input type="password" name="password" id="password" class="input-text" placeholder="Define a Password">
                                            <label for="password" class="validation-message" id="password-error" generated="true" style="display: none;"></label>
                                            <small> (Leave blank to keep current password)</small>
                                            <br>
                                         </a>
                                     </tr>
                                     <tr>
                                        <td class="label-td" colspan="2">
                                            <label for="cpassword" class="form-label">Confirm Password: </label>
                                         </a>
                                     </tr>
                                     <tr>
                                        <td class="label-td" colspan="2">
                                            <input type="password" name="cpassword" id="cpassword" class="input-text" placeholder="Confirm Password">
                                            <label for="cpassword" class="validation-message" id="cpassword-error" generated="true" style="display: none;"></label>
                                            <br>
                                         </a>
                                     </tr>
                                    
                        
                                     <tr>
                                        <td colspan="2">
                                            <input type="reset" value="Reset" class="login-btn btn-primary-soft btn" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                        
                                            <input type="submit" value="Save" class="login-btn btn-primary btn">
                                         </a>
                        
                                     </tr>
                                
                                    </form>
                                     </tr>
                                </table>
                                </div>
                                </div>
                            </center>
                            <br><br>
                    </div>
                    </div>
                    ';
        }else{
            echo '
                <div id="popup1" class="overlay">
                        <div class="popup">
                        <center>
                        <br><br><br><br>
                            <h2>Edit Successfully!</h2>
                            <a class="close" href="doctors.php">&times;</a>
                            <div class="content">
                                
                                
                            </div>
                            <div style="display: flex;justify-content: center;">
                            
                            <a href="doctors.php" class="non-style-link"><button  class="btn-primary btn"  style="display: flex;justify-content: center;align-items: center;margin:10px;padding:10px;"><font class="tn-in-text">&nbsp;&nbsp;OK&nbsp;&nbsp;</font></button></a>

                            </div>
                            <br><br>
                        </center>
                </div>
                </div>
    ';



        }; };
    };

?>

<script>
$(document).ready(function() {
    // Custom method for password confirmation
    $.validator.addMethod("valueNotEquals", function(value, element, arg){
        return arg !== value;
    }, "Please select an option");

    // Add validation rules for Add Doctor Form
    $("#addDoctorForm").validate({
        rules: {
            name: {
                required: true,
                minlength: 3,
                maxlength: 100
            },
            email: {
                required: true,
                email: true
            },
            nic: {
                required: true,
                minlength: 10,
                maxlength: 20
            },
            Tele: {
                required: true,
                digits: true,
                minlength: 10,
                maxlength: 15
            },
            spec: {
                required: true,
                valueNotEquals: ""
            },
            password: {
                required: true,
                minlength: 6,
                maxlength: 20
            },
            cpassword: {
                required: true,
                equalTo: "#password"
            }
        },
        messages: {
            name: {
                required: "Please enter doctor name",
                minlength: "Name must be at least 3 characters",
                maxlength: "Name cannot exceed 100 characters"
            },
            email: {
                required: "Please enter email address",
                email: "Please enter a valid email address (e.g., name@example.com)"
            },
            nic: {
                required: "Please enter NIC number",
                minlength: "NIC must be at least 10 characters",
                maxlength: "NIC cannot exceed 20 characters"
            },
            Tele: {
                required: "Please enter telephone number",
                digits: "Please enter only digits (0-9)",
                minlength: "Telephone number must be at least 10 digits",
                maxlength: "Telephone number cannot exceed 15 digits"
            },
            spec: {
                required: "Please select a specialty"
            },
            password: {
                required: "Please enter a password",
                minlength: "Password must be at least 6 characters",
                maxlength: "Password cannot exceed 20 characters"
            },
            cpassword: {
                required: "Please confirm your password",
                equalTo: "Passwords do not match"
            }
        },
        errorClass: "error",
        validClass: "valid",
        errorElement: "label",
        errorPlacement: function(error, element) {
            // Place error message in the hidden label
            var errorId = element.attr("id") + "-error";
            $("#" + errorId).text(error.text()).show();
        },
        highlight: function(element) {
            $(element).addClass("error").removeClass("valid");
            // Hide the hidden label error
            var errorId = $(element).attr("id") + "-error";
            $("#" + errorId).hide();
        },
        unhighlight: function(element) {
            $(element).removeClass("error").addClass("valid");
            // Hide the hidden label error
            var errorId = $(element).attr("id") + "-error";
            $("#" + errorId).hide();
        },
        onkeyup: function(element) {
            this.element(element);
        },
        onclick: function(element) {
            this.element(element);
        },
        submitHandler: function(form) {
            form.submit();
        }
    });

    // Add validation rules for Edit Doctor Form
    $("#editDoctorForm").validate({
        rules: {
            name: {
                required: true,
                minlength: 3,
                maxlength: 100
            },
            email: {
                required: true,
                email: true
            },
            nic: {
                required: true,
                minlength: 10,
                maxlength: 20
            },
            Tele: {
                required: true,
                digits: true,
                minlength: 10,
                maxlength: 15
            },
            spec: {
                required: true,
                valueNotEquals: ""
            },
            cpassword: {
                equalTo: "#password"
            }
        },
        messages: {
            name: {
                required: "Please enter doctor name",
                minlength: "Name must be at least 3 characters",
                maxlength: "Name cannot exceed 100 characters"
            },
            email: {
                required: "Please enter email address",
                email: "Please enter a valid email address (e.g., name@example.com)"
            },
            nic: {
                required: "Please enter NIC number",
                minlength: "NIC must be at least 10 characters",
                maxlength: "NIC cannot exceed 20 characters"
            },
            Tele: {
                required: "Please enter telephone number",
                digits: "Please enter only digits (0-9)",
                minlength: "Telephone number must be at least 10 digits",
                maxlength: "Telephone number cannot exceed 15 digits"
            },
            spec: {
                required: "Please select a specialty"
            },
            cpassword: {
                equalTo: "Passwords do not match"
            }
        },
        errorClass: "error",
        validClass: "valid",
        errorElement: "label",
        errorPlacement: function(error, element) {
            // Place error message in the hidden label
            var errorId = element.attr("id") + "-error";
            $("#" + errorId).text(error.text()).show();
        },
        highlight: function(element) {
            $(element).addClass("error").removeClass("valid");
            // Hide the hidden label error
            var errorId = $(element).attr("id") + "-error";
            $("#" + errorId).hide();
        },
        unhighlight: function(element) {
            $(element).removeClass("error").addClass("valid");
            // Hide the hidden label error
            var errorId = $(element).attr("id") + "-error";
            $("#" + errorId).hide();
        },
        onkeyup: function(element) {
            this.element(element);
        },
        onclick: function(element) {
            this.element(element);
        },
        submitHandler: function(form) {
            form.submit();
        }
    });
});
</script>

</body>
</html>