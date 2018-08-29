<?php

/* Sets the value of the logged_in variable, which can be used in your code */
$logged_in = $cdb->checkLogin();

if(!$logged_in){
	/* Redirect back to the login page */
	header("Location: login.php");
} else {
    $OnlineCode = ( isset($_POST['pin']) && is_numeric($_POST['pin']) ) ? $_POST['pin'] : ( ( isset($_GET['pin']) && is_numeric($_GET['pin']) ) ? $_GET['pin'] : 0 );
    
    /* Strip whitespace from the beginning and end of the PIN code */
    $OnlineCode = trim($OnlineCode);

    /* Set the PIN code value as the first 10 characters returned in the form */
    $OnlineCode = substr($OnlineCode, 0, 10);

    /* Checks that online code is in database */
    $result = $cdb->confirmStudentPinByID($_SESSION['StudentCode'], $OnlineCode);
    
    /* Check error codes */
    if ($result == 1) {
        /* Quick redirect to survey page to avoid resending data on refresh */
        echo "<meta http-equiv=\"Refresh\" content=\"0;url=menu.php?msg=error1\">";
        return;
    } elseif ($result == 2) {
        /* Quick redirect to survey page to avoid resending data on refresh */
        echo "<meta http-equiv=\"Refresh\" content=\"0;url=menu.php?msg=error2\">";
        return;
    } elseif ($result == 3) {
        /* Quick redirect to survey page to avoid resending data on refresh */
        echo "<meta http-equiv=\"Refresh\" content=\"0;url=menu.php?msg=error3\">";
        return;
    } elseif ($result == 4) {
        /* Quick redirect to survey page to avoid resending data on refresh */
        echo "<meta http-equiv=\"Refresh\" content=\"0;url=menu.php?msg=error4\">";
        return;
    } else {
        /* Online Code correct */
        $OnlineCode = strtoupper(stripslashes($OnlineCode));
        
        /* Obtain the prefix from the student user's online code */
        $prefix = substr($OnlineCode, 0, 3);
        
        /* Check if survey has been submitted */
        if(isset($_POST['status']) && ($_POST['status'] == "submit")){
        
            /* If online code is not for training purposes, record survey responses and stop time */
            if ( ($prefix != "990") && ($prefix != "991") ) {
             
                /* Retrieve the student user's survey information */
                $survey = $cdb->getSurveyInfo($OnlineCode);
                
                /* Check error codes */
                if ($survey == "badpin") {
                    /* Generate and display error message */
                    $error_list .= "You are attempting to use an invalid online code.";
                    displayForm($cdb, $error_list, $OnlineCode);
                } elseif ($survey == "badprefix") {
                    /* Generate and display error message */
                    $error_list .= "You are attempting to use invalid prefix information.";
                    displayForm($cdb, $error_list, $OnlineCode);
                } elseif ($survey == "badselect") {
                    /* Generate and display error message */
                    $error_list .= "There was an error in retrieving the survey information.&nbsp;&nbsp;Please try again.";
                    displayForm($cdb, $error_list, $OnlineCode);
                } else {
                
                    /* Record the student user's survey responses */
                    $insert = $cdb->insertSurveyInfo($OnlineCode, $survey->survey, $_POST);
            
                    /* Check error codes */
                    if ($insert == "badpin") {
                        /* Generate and display error message */
                        $error_list .= "You are attempting to use either an invalid or deactivated online code.";
                        displayForm($cdb, $error_list, $OnlineCode, $survey);
                    } elseif ($insert == "badselect") {
                        /* Generate and display error message */
                        $error_list .= "There was an error in verifying your survey response history.&nbsp;&nbsp;Please try again.";
                        displayForm($cdb, $error_list, $OnlineCode, $survey);
                    } elseif ($insert == "preventry") {
                        /* Generate and display error message */
                        $error_list .= "The online code you entered has already been used to complete the survey.";
                        displayForm($cdb, $error_list, $OnlineCode, $survey);
                    } elseif ($insert == "baddropdown") {
                        /* Generate and display error message */
                        $error_list .= "You are attempting to submit an invalid value for a drop-down menu.&nbsp;&nbsp;"
                                    .  "Please try again.";
                        displayForm($cdb, $error_list, $OnlineCode, $survey);
                    } elseif ($insert == "badstatement") {
                        /* Generate and display error message */
                        $error_list .= "You are attempting to submit an invalid value for one of the statements.&nbsp;&nbsp;"
                                    .  "Please try again.";
                        displayForm($cdb, $error_list, $OnlineCode, $survey);
                    } elseif ($insert == "badmultiplechoice") {
                        /* Generate and display error message */
                        $error_list .= "You are attempting to submit an invalid value for a multiple choice question.&nbsp;&nbsp;"
                                    .  "Please try again.";
                        displayForm($cdb, $error_list, $OnlineCode, $survey);
                    } elseif ($insert == "badopenended") {
                        /* Generate and display error message */
                        $error_list .= "You are attempting to submit an invalid value for an open-ended question.&nbsp;&nbsp;"
                                    .  "Please try again.";
                        displayForm($cdb, $error_list, $OnlineCode, $survey);
                    } elseif ($insert == "badinsert") {
                        /* Generate and display error message */
                        $error_list .= "There was an error in recording the survey responses.&nbsp;&nbsp;Please try again.";
                        displayForm($cdb, $error_list, $OnlineCode, $survey);
                    } else {
                    
                        /* Set the student user's stop time to reflect current time */
                        $time = $cdb->setTime($OnlineCode, "stop");
                        
                        if (!$time) {
                            /* Generate and display error message */
                            $error_list .= "There was an error in recording your survey stop time.&nbsp;&nbsp;Please try again.";
                            displayForm($cdb, $error_list, $OnlineCode, $survey);
                        } else {
                            /* Quick redirect to "Thank You" page */
                            echo "<meta http-equiv=\"Refresh\" content=\"0;url=thankyou.php?pin=" . $OnlineCode . "\">";
                            return;
                        }
                    }
                }
            } else {
                /* Quick redirect to "Thank You" page */
                echo "<meta http-equiv=\"Refresh\" content=\"0;url=thankyou.php?pin=" . $OnlineCode . "\">";
                return;
            }
        } else {
    
            /* Retrieve the student user's prefix information */
            $pin_prefix = $cdb->getPrefixInfo($prefix);
        
            /* Check error codes */
            if ($pin_prefix == "badprefix") {
                /* Generate and display error message */
                $error_list .= "You are attempting to use an invalid PIN prefix value.";
                displayForm($cdb, $error_list, $OnlineCode);
            } elseif ($pin_prefix == "badselect") {
                /* Generate and display error message */
                $error_list .= "There was an error in retrieving the PIN prefix information.&nbsp;&nbsp;Please try again.";
                displayForm($cdb, $error_list, $OnlineCode);
            } else {
    
                /* If online code is not for training purposes, record start time and display survey form information */
                if ( ($prefix != "990") && ($prefix != "991") ) {
                
                    /* Begin display of survey form information */
                    /* Set the student user's start time to reflect current time */
                    $time = $cdb->setTime($OnlineCode, "start", $pin_prefix->surveyid);
                        
                    if (!$time) {
                        /* Generate and display error message */
                        $error_list .= "There was an error in recording your survey start time.&nbsp;&nbsp;Please try again.";
                        displayForm($cdb, $error_list, $OnlineCode);
                    } else {
    
                        /* Retrieve the student user's survey information */
                        $survey = $cdb->getSurveyInfo($OnlineCode, $pin_prefix);
                        
                        /* Check error codes */
                        if ($survey == "badpin") {
                            /* Generate and display error message */
                            $error_list .= "You are attempting to use an invalid online code.";
                            displayForm($cdb, $error_list, $OnlineCode);
                        } elseif ($survey == "badprefix") {
                            /* Generate and display error message */
                            $error_list .= "You are attempting to use invalid prefix information.";
                            displayForm($cdb, $error_list, $OnlineCode);
                        } elseif ($survey == "badselect") {
                            /* Generate and display error message */
                            $error_list .= "There was an error in retrieving the survey information.&nbsp;&nbsp;Please try again.";
                            displayForm($cdb, $error_list, $OnlineCode);
                        } else {
                            /* Load the survey form information */
                            displayForm($cdb, $error_list, $OnlineCode, $survey);
                        }
                    }
                } else {
                    /* Retrieve the student user's survey information */
                    $survey = $cdb->getSurveyInfo($OnlineCode);
                
                    /* Check error codes */
                    if ($survey == "badpin") {
                        /* Generate and display error message */
                        $error_list .= "You are attempting to use an invalid online code.";
                        displayForm($cdb, $error_list, $OnlineCode);
                    } elseif ($survey == "badprefix") {
                        /* Generate and display error message */
                        $error_list .= "You are attempting to use invalid prefix information.";
                        displayForm($cdb, $error_list, $OnlineCode);
                    } elseif ($survey == "badselect") {
                        /* Generate and display error message */
                        $error_list .= "There was an error in retrieving the survey information.&nbsp;&nbsp;Please try again.";
                        displayForm($cdb, $error_list, $OnlineCode);
                    } else {
                        /* Load the survey form information */
                        displayForm($cdb, $error_list, $OnlineCode, $survey);
                    }
                }
            }
        }
    }
}
?>
