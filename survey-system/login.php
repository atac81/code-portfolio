<?php
session_start(); 
include('./includes/cdb.include');

header("Cache-Control: no-store, no-cache, must-revalidate"); 

$error_list = "";
$cdb = new CDB;

/**
 * Determines whether or not to display the login
 * form or to show the user that he is logged in
 * based on if the session variables are set.
 */
function displayLogin($cdb, $error_list){
?>
<html>
<head>
    <title>Online Survey Evaluation - Login</title>
    <meta name="viewport" content="width=device-width; maximum-scale=1.0;" />
    <link href="global.css" rel="stylesheet" type="text/css" />
	  <script language="javascript"> 
    // Disable Enter key from being used as submit button	
    function disableEnterKey(e){
      var key;      
		  if(window.event)
			    key = window.event.keyCode; //IE
		  else
			    key = e.which; //FireFox      

		  if (key == '13'){
		      alert("Enter key is disabled.\nPlease click Continue button.");
		      return false;
		  }
	  }
    </script>
</head>

<body>
<table width="100%" height="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td id="header">
            <table width="98%" align="center" border="0" cellpadding="0" cellspacing="0">
            	<tr>
					<td id="headerCopy">Online Survey Evaluation</td>
                	<td id="headerCopy" align="right">&nbsp;</td>
                </tr>
            </table>
		</td>
    </tr>
	<tr>
		<td align="center" valign="top">
        	<table id="MainBody" border="0">
            	<tr>
                	<td align="center">
                    	
                        <form id="login" name="login" method="post" action="<? echo $_SERVER['PHP_SELF']; ?>">
                        <?	if ( (isset($_POST['CodeEnter']) || isset($_GET['id'])) && ($error_list != "") ) {	?>
                        <div id="ErrorMsg">
                            <img align="left" src="images/icon_warning.gif" height="25" width="28" style="margin-top: 4px; margin-left: 15px; margin-right: 15px;">
                            <b>A problem was encountered processing your request:</b><br><? echo $error_list; ?>
                        </div><br>
                        <?	}	?>
                        <div id="LoginBody">
                            <p>This evaluation provides you with an opportunity to anonymously express your opinions 
                            	regarding the courses and instructors at this institution.</p>
                            <p>How to use the <b>Online Survey Evaluation</b>:</p>
                            <ol>
                                <li>Enter your student ID/User Name and click on the "Continue" button.&nbsp;&nbsp;Your courses that 
                                	are available for online evaluation will appear in a selection screen.<br><br></li>
                                <li>Fill in your responses to the survey questions.&nbsp;&nbsp;You may make changes, exit, 
                                    and return to the survey ONLY if you have not submitted the survey.&nbsp;&nbsp;When you 
                                    have finished the evaluation, click on the "Submit Survey" button.<br><br></li>
                                <li>Your responses will be added to the database, tabulated, and reported anonymously with all 
                                    other responses gathered for this course section.<br><br></li>
                                <li>Any submissions made after the institution's determined deadline will be discarded.<br><br></li>
                            </ol>
                        </div><br>
                        <div id="LoginForm">
                            <h2>Course Evaluation Survey</h2>
                            <p style="text-align: center;">Enter Your Student ID/User Name</p>
                            <p style="text-align: center;"><input class="CenteredTextBox" id="StudentCode" name="StudentCode" type="text" maxlength="11" size="15" onKeyPress="return disableEnterKey(event)" /></p>
                            <p style="text-align: center;"><input type="submit" id="CodeEnter" name="CodeEnter" value="Continue" /></p>
                        </div>
                
                        </form>
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

/**
 * Checks to see if the user has submitted 
 * his/her student ID through either the login 
 * form or passing it through the e-mail URL.
 * If so, checks authenticity in database and
 * creates session.
 */
if(isset($_POST['CodeEnter']) || isset($_GET['id'])){
    /* Set student ID value, as well as strip whitespace from the beginning and end of the student ID */
    $studentid = isset($_POST['StudentCode']) ? trim($_POST['StudentCode']) : ( isset($_GET['id']) ? trim($_GET['id']) : 0 );
    $studentid = strtoupper(stripslashes($studentid));

    /* Check that all fields were typed in */
    if(!$studentid){
        $error_list .= "You have not entered a student ID.";
		    displayLogin($cdb, $error_list);
    } else {
    
        /* Checks that student ID is in database */
        $result = $cdb->confirmStudentID($studentid);
		
		    /* Check error codes */
		    if ($result == 1) {
            $error_list .= "You have not entered a valid student ID.";
            displayLogin($cdb, $error_list);
		    } elseif ($result == 2) {
            $error_list .= "The student ID you provided does not exist in our system.";
            displayLogin($cdb, $error_list);
        } else {
            /* Student ID correct, register session variables */
            $studentid = stripslashes($studentid);
            $_SESSION['StudentCode'] = $studentid;
            
            /* Quick redirect to main menu page to avoid resending data on refresh */
            echo "<meta http-equiv=\"Refresh\" content=\"0;url=menu.html\">";
            return;
        }
	}
} else {
	displayLogin($cdb, $error_list);
}
?>
