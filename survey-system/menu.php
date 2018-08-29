<?php
session_start(); 
include('./includes/cdb.include');

header("Cache-Control: no-store, no-cache, must-revalidate"); 

$cdb = new CDB;
$msg_type = ( isset($_GET['msg']) && (($_GET['msg'] == "error1") || ($_GET['msg'] == "error2") || ($_GET['msg'] == "error3"))  ) ? $_GET['msg'] : "";

if ($msg_type == "error1") {
    $msg_type = "error";
    $msg_list .= "<br />You have not entered a valid student ID.";
} elseif ($msg_type == "error2") {
    $msg_type = "error";
    $msg_list .= "<br />You have not entered a valid PIN code.&nbsp;&nbsp;The PIN code must be a 10-digit combination of " .
                 "letters and numbers.";
} elseif ($msg_type == "error3") {
    $msg_type = "error";
    $msg_list .= "<br />The PIN code you entered does not exist in our system.";
} elseif ($msg_type == "error4") {
    $msg_type = "error";
    $msg_list .= "<br />The PIN code you entered has already been used to complete the survey.";
} else {
    $msg_list = "";
}

function displayForm($cdb, $msg_type, $msg_list) {
    $html_surveys_list = '';
    
    $surveys = $cdb->listSurveysByStudent($_SESSION['StudentCode']);
    
    if ($surveys == "badid") {
        /* Generate and display error message */
        $msg_type = "error";
        $msg_list .= "<br />You are attempting to use an invalid student ID to retrieve your list of courses.";
    } elseif ($surveys == "badselect") {
        /* Generate and display error message */
        $msg_type = "error";
        $msg_list .= "<br />There was an error in retrieving the list of courses.&nbsp;&nbsp;Please try again.";
    } else {
        if (sizeof($surveys) > 0) {
            foreach($surveys as $survey) {
            	// Display survey that has not reached an end date greater than 14 days
            	if (date_diff(date_create($survey->enddate), date_create("today"))->format("%R%a") <= 14) {
                    $html_surveys_list .= "<tr>
                        <td align=\"justify\">
                            <span style=\"font-weight: bold;\">Title of Course:</span>&nbsp;&nbsp;$survey->course<br />
                            <span style=\"font-weight: bold;\">Discipline, Number, and Section:</span>&nbsp;&nbsp;$survey->dns<br />
                            <span style=\"font-weight: bold;\">School:</span>&nbsp;&nbsp;$survey->department<br />
                            <span style=\"font-weight: bold;\">Instructor:</span>&nbsp;&nbsp;$survey->instructor<br />
                            <span style=\"font-weight: bold;\">Current Semester and Year:</span>&nbsp;&nbsp;$survey->semester
                        </td>";
                    if ( ($survey->active == "N") && ($survey->stoptime != "") ) {
                        $html_surveys_list .= "<td align=\"center\" valign=\"middle\"><span style=\"font-style: italic;\">Completed 
                            on<br />" . date("m/d/Y \a\\t h:i A T", strtotime(substr($survey->stoptime,0,19))) . "</span><br />
                            <a href=\"thankyou.html?pin=$survey->pin\">View Receipt</a></td></tr>\n";
                    } elseif ( (strtotime(date("Y-m-d"))) > (strtotime($survey->enddate)) ) {
                        $html_surveys_list .= "<td align=\"center\" valign=\"middle\"><span style=\"font-style: italic;\">Expired 
                            <br />" . date("m/d/Y", strtotime($survey->enddate)) . "</span></td></tr>\n";
                    } else {
                        $html_surveys_list .= "<td align=\"center\"><a href=\"survey.html?pin=$survey->pin\">View Survey</a></td></tr>\n";
                    }
                }
            }
        }
    }
    
    $msg_welcome = $cdb->lookupStudentName($_SESSION['StudentCode']);
    
    if ($msg_welcome == "badid") {
        /* Invalid student ID - generate and display error message */
        $msg_type = "error";
        $msg_list .= "<br />You are attempting to use an invalid student ID to retrieve your name.";
        $msg_welcome = "";
    } elseif ( ($msg_welcome == "badselect") || ($msg_welcome->name == "Unknown User") ) {
        /* Either error in retrieving name or name not listed in database - set empty value */
        $msg_welcome = "";
    } else {
    	/* Display name */
		$msg_welcome = ", " . $msg_welcome->name;
    }
?>
<html>
<head>
    <title>Online Course Evaluation - Main Menu</title>
    <meta name="viewport" content="width=device-width; maximum-scale=1.0;" />
    <link href="global.css" rel="stylesheet" type="text/css" />
</head>

<body>
<table width="100%" height="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td id="header">
            <table width="98%" align="center" border="0" cellpadding="0" cellspacing="0">
            	<tr>
					<td id="headerCopy">Online Survey Evaluation</td>
                	<td id="headerCopy" align="right"><a href="menu.html">Main Menu</a>&nbsp;&nbsp;&bull;&nbsp;&nbsp;<a 
                    	href="logout.html">Logout</a></td>
                </tr>
            </table>
		</td>
    </tr>
	<tr>
		<td align="center" valign="top">
        	<table id="MainBody" border="0">
            	<tr>
                	<td align="center">
						<table width="100%" border="0">
                        	<tr>
                            	<td width="50%"><p class="heading">Main Menu</p></td>
                            	<td width="50%" align="right" valign="bottom" 
                                	style="font-size: 14px; font-weight: bold; line-height: 22px;"></td>
                        	</tr>
						</table>
                        <?	if ($msg_type != "") {	?>
                        <div id="ErrorMsg">
                        <?		if ($msg_type == "error") {	?>
                            <img align="left" src="images/icon_warning.gif" height="25" width="28" style="margin-top: 4px; margin-left: 15px; margin-right: 15px;">
                            <b>A problem was encountered processing your request:</b><? echo $msg_list; ?>
                        <?		}	?>
                        </div><br>
                        <?	}	?>
                        <div id="MainBody">
                            <p>Welcome to the Main Menu.&nbsp;&nbsp;Below is a list of your classes for which you can complete an 
                               online survey.&nbsp;&nbsp;Select the "View Survey" link to access the survey.&nbsp;&nbsp;You may 
                               return to the Main Menu to complete other surveys available to you or to select the receipt page 
                               for a completed survey.</p>
                            <blockquote>
                              <?php
                                	if ($html_surveys_list != "") { ?>
                              <table id="StudentTable" align="center" border="1" cellpadding="5" cellspacing="3">
                                  <tr align="center">
                                    <th class="heading">Course Information</th>
                                    <th class="heading">Options</th>
                                  </tr>
                              <?php echo $html_surveys_list; ?>
                              </table>
                              <?php
                                	} else { ?>
                              <p style="text-align: center;"><strong>There are currently no courses/surveys assigned to you in the 
                              	system.</strong><br />
                                Please check back again for further updates.</p>
                              <?php
                                	}	?>
                            </blockquote>
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
	<tr>
		<td id="footer">
            Copyright &copy; <?php echo date('Y'); ?>.&nbsp;&nbsp;All Rights Reserved.
        </td>
	</tr>
</table>

</body>

</html>
<?
}

/* Verify that the current user is logged into the system */
$logged_in = $cdb->checkLogin();

if(!$logged_in){
	/* Redirect back to the home page */
	header("Location: index.html");
} else {
	displayForm($cdb, $msg_type, $msg_list);
}
?>
