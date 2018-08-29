<?php
ini_set("max_execution_time","300");
ini_set("memory_limit","512M");

// Generate e-mail message to the given student about completing his/her assigned surveys
function sendEmail($student, $message, $school_id) {
	$html_message = "";
	
	$mailto = $student->email;

	// To send HTML mail, the Content-type header must be set
	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	$headers .= 'From: Administrator <webmaster@sample.com>' . "\r\n";
	
	$html_message = "<html><head><title>" . $message->subject . "</title></head>"
				  . "<body>" . $message->message . "<br><br>\n\n"
				  . "**PLEASE DO NOT REPLY TO THIS E-MAIL**<br>\n"
				  . "This e-mail was sent from an unmonitored e-mail account using an automated system.  Please do not reply to "
				  . "this e-mail as it will not be received."
				  . "</body></html>";
	
	$status = mail($mailto, $message->subject, $html_message, $headers);
	
	return $status;
}

// user defined error handling function
function userErrorHandler($errno, $errmsg, $filename, $linenum)
{
    // timestamp for the error entry
    $dt = date("D M d Y H:i:s T");

    // define an assoc array of error string
    // in reality the only entries we should
    // consider are E_WARNING, E_NOTICE, E_USER_ERROR,
    // E_USER_WARNING and E_USER_NOTICE
    $errortype = array (
                E_ERROR              => 'Error',
                E_WARNING            => 'Warning',
                E_PARSE              => 'Parsing Error',
                E_NOTICE             => 'Notice',
                E_CORE_ERROR         => 'Core Error',
                E_CORE_WARNING       => 'Core Warning',
                E_COMPILE_ERROR      => 'Compile Error',
                E_COMPILE_WARNING    => 'Compile Warning',
                E_USER_ERROR         => 'User Error',
                E_USER_WARNING       => 'User Warning',
                E_USER_NOTICE        => 'User Notice',
                E_STRICT             => 'Runtime Notice',
                E_RECOVERABLE_ERROR  => 'Catchable Fatal Error'
                );

	$err_mail = "The following error was encountered by the Automatic E-mail system:\n\n";
	$err_mail .= "Date: " . $dt . "\n";
	$err_mail .= "Error Number: " . $errno . "\n";
	$err_mail .= "Error Type: " . $errortype[$errno] . "\n";
	$err_mail .= "Error Message: " . $errmsg . "\n";
	$err_mail .= "Script: " . $filename . "\n";
	$err_mail .= "Line Number: " . $linenum . "\n\n";
	$err_mail .= "Error triggered within the main script\n";

	$err_log = "[" . $dt . "] [ERROR " . $errortype[$errno] . " (" . $errno . ")]: " . $errmsg . " in " . $filename . " on line " . $linenum . "\n";

	// save to the error log and send e-mail
	error_log($err_log, 3, "/path/to/server/logs/scheduled_emails.log");

	mail('admin@sample.com', 'Error from Automatic E-mail System', $err_mail, 'From: Server Automated Message <webmaster@sample.com>'); 
} 

// set to the user defined error handler
$old_error_handler = set_error_handler("userErrorHandler");

$path = '/path/to/website/files';
date_default_timezone_set('America/New_York');
include($path . '/includes/cdb.include');

$cdb = new CDB;

/* Retrieve from the database list of follow-up e-mail blasts scheduled for right now */
$db_data = $cdb->listScheduledEmailBlasts(date("Y-m-d"), date("h:i A"));
if ($db_data == "badvar") {
	// Error - invalid variable attempted to be used against the database
	trigger_error("Problem in retrieving the list of scheduled e-mail blasts (invalid variable)", E_USER_ERROR);
	exit;
} elseif ($db_data == "badselect") {
	// Error - SELECT statement failure in the database
	trigger_error("Problem in retrieving the list of scheduled e-mail blasts (SELECT statement failed)", E_USER_ERROR);
	exit;
} elseif (($db_data != "No records") && (sizeof($db_data) > 0)) {
	// At least one e-mail blast has been scheduled - process it accordingly
	foreach($db_data as $row) {
		$error_triggered = false;

		$id = $row->id;
		$school_id = $row->school;
		$name = $row->name;
		$blast = $row->blast;
		$message = $row->message;
		
		// Update the scheduled e-mail blast status from "new" to "processing"
		$status_update = $cdb->updateScheduledEmailBlastStatus($id, "processing");
		if ($status_update == "badvar") {
			// Error - invalid variable attempted to be used against the database
			trigger_error("Problem in updating status of scheduled e-mail blast to 'processing' (invalid variable; see school $school_id, ID $id, name $name)", E_USER_ERROR);
			$error_triggered = true;
			exit;
		} elseif ($status_update == "badupdate") {
			// Error - UPDATE statement failure in the database
			trigger_error("Problem in updating status of scheduled e-mail blast to 'processing' (SELECT statement failed; see school $school_id, ID $id, name $name)", E_USER_ERROR);
			$error_triggered = true;
			exit;
		} else {
			// Retrieve student records for new follow-up e-mail blast
			$blast_students = $cdb->generateEmailBlastsFollowupAdmin($school_id, $blast);
			if ($blast_students == "badvar") {
				// Error - invalid variable attempted to be used against the database
				trigger_error("Problem in retrieving the student records for the new follow-up e-mail blast (invalid variable; see school $school_id, ID $id, name $name)", E_USER_ERROR);
				$error_triggered = true;
				exit;
			} elseif ($blast_students == "badselect") {
				// Error - SELECT statement failure in the database
				trigger_error("Problem in retrieving the student records for the new follow-up e-mail blast (SELECT statement failed; see school $school_id, ID $id, name $name)", E_USER_ERROR);
				$error_triggered = true;
				exit;
			} elseif (sizeof($blast_students) == 0) {
				// Notice - no active student records found within selected upload blast group
				trigger_error("Unable to create new follow-up e-mail blast (no active students remaining; see school $school_id, ID $id, name $name)", E_NOTICE);
				$error_triggered = true;
			} else {
				//  Add new follow-up e-mail blast
				$rc = $cdb->insertEmailBlastFollowupAdmin($school_id, $name, $blast, $message);
				if (!$rc) {
					// Error - INSERT statement failure in the database
					trigger_error("Problem in creating the new follow-up e-mail blast (INSERT statement failed; see school $school_id, ID $id, name $name)", E_USER_ERROR);
					$error_triggered = true;
					exit;
				} else {
					// Set e-mail blast id from newly-created follow-up e-mail blast entry
					$blast_id = $cdb->lookupEmailBlastFollowupIdAdmin($school_id, $name, $blast, $message);
					if ($blast_id == "badvar") {
						// Error - invalid variable attempted to be used against the database
						trigger_error("Problem in retrieving the ID value of the new follow-up e-mail blast (invalid variable; see school $school_id, ID $id, name $name)", E_USER_ERROR);
						$error_triggered = true;
						exit;
					} elseif ($blast_id == "doesnotexist") {
						// Error - no previous e-mail blast matching name or survey
						trigger_error("Problem in retrieving the ID value of the new follow-up e-mail blast (no match based on provided data; see school $school_id, ID $id, name $name)", E_USER_ERROR);
						$error_triggered = true;
						exit;
					} elseif ($blast_id == "badselect") {
						// Error - SELECT statement failure in the database
						trigger_error("Problem in retrieving the ID value of the new follow-up e-mail blast (SELECT statement failed; see school $school_id, ID $id, name $name)", E_USER_ERROR);
						$error_triggered = true;
						exit;
					} else {
						// Add each student record into the follow-up e-mail blast
						foreach($blast_students as $blast_student)	
						{
							// Add the new student information
							$student_insert = $cdb->insertEmailBlastFollowupAdminStudent($school_id, $blast_id, $blast_student->studentid, 
																$blast_student->name, $blast_student->email, $blast_student->school, 
																$blast_student->semester, $blast_student->enddate, 
																$blast_student->pin, $blast_student->course, 
																$blast_student->department, $blast_student->instructor);
							if ($student_insert == "badvar") {
								// Error - invalid variable attempted to be used against the database
								trigger_error("Problem in adding student record to the new follow-up e-mail blast (invalid variable; see school $school_id, blast ID $blast_id, STU_NAME $blast_student->name, STU_CODE $blast_student->studentid)", E_USER_ERROR);
								$error_triggered = true;
								break;
							} elseif ($student_insert == "badinsert") {
								// Error - INSERT statement failure in the database
								trigger_error("Problem in adding student record to the new follow-up e-mail blast (INSERT statement failed; see school $school_id, blast ID $blast_id, STU_NAME $blast_student->name, STU_CODE $blast_student->studentid)", E_USER_ERROR);
								$error_triggered = true;
								break;
							}
						}
					}
				}
			}
		}
		// If we reached this point error-free, continue to next step (send out the e-mails)
		if ($error_triggered != true) {
			// Set the e-mail blast's start time to reflect current time
			$time = $cdb->setEmailBlastFollowupAdminTime($school_id, $blast_id, "email", "start");
			if (!$time) {
				// Error - unable to record the follow-up e-mail blast start time
				trigger_error("Problem in recording the follow-up e-mail blast start time (see school $school_id, blast ID $blast_id, name $name)", E_USER_ERROR);
				$error_triggered = true;
				exit;
			} else {
				// Retrieve list of all unsent follow-up e-mail blast students
				$students = $cdb->listUnsentFollowupStudentEmailsAdmin($school_id, $blast_id);
				if ($students == "badschoolid") {
					// Error - invalid variable attempted to be used against the database
					trigger_error("Problem in retrieving the list of all unsent follow-up e-mail blast students (invalid school ID variable; see school $school_id, blast ID $blast_id, name $name)", E_USER_ERROR);
					$error_triggered = true;
					exit;
				} elseif ($students == "badid") {
					// Error - invalid variable attempted to be used against the database
					trigger_error("Problem in retrieving the list of all unsent follow-up e-mail blast students (invalid blast ID variable; see school $school_id, blast ID $blast_id, name $name)", E_USER_ERROR);
					$error_triggered = true;
					exit;
				} elseif ($students == "badselect") {
					// Error - SELECT statement failure in the database
					trigger_error("Problem in retrieving the list of all unsent follow-up e-mail blast students (SELECT statement failed; see school $school_id, blast ID $blast_id, name $name)", E_USER_ERROR);
					$error_triggered = true;
					exit;
				} else {
					// As long as a student record was found, go through the list and send each e-mail in the blast
					if (sizeof($students) > 0) {
						foreach($students as $student) {
							// Retrieve e-mail message and populate any dynamic fields with given student information
							$email_message = $cdb->lookupEmailMessageAdmin($school_id, $student, $message);
							if ($email_message == "badschool") {
								// Error - invalid variable attempted to be used against the database
								trigger_error("Problem in retrieving the e-mail message (invalid school ID variable; see school $school_id, blast ID $blast_id, student ID $student->studentid)", E_USER_ERROR);
								$error_triggered = true;
								break;
							} elseif ($email_message == "badvar") {
								// Error - invalid variable attempted to be used against the database
								trigger_error("Problem in retrieving the e-mail message (invalid e-mail message ID variable; see school $school_id, blast ID $blast_id, student ID $student->studentid)", E_USER_ERROR);
								$error_triggered = true;
								break;
							} elseif ($email_message == "badselect") {
								// Error - SELECT statement failure in the database
								trigger_error("Problem in retrieving the e-mail message (SELECT statement failed; see school $school_id, blast ID $blast_id, student ID $student->studentid)", E_USER_ERROR);
								$error_triggered = true;
								break;
							} else {
								// Send the e-mail message
								$result = sendEmail($student, $email_message, $school_id);
								if (!$result) {
									// Error - mail function failed (mail was not successfully accepted for delivery)
									trigger_error("Problem in sending the e-mail message (see school $school_id, blast ID $blast_id, student ID $student->studentid, student name $student->name)", E_USER_ERROR);
									$error_triggered = true;
									break;
								} else {
									// Set the student user's sent time to reflect current time
									$time = $cdb->setEmailBlastFollowupAdminTime($school_id, $student->id, "student", "sent");
									if (!$time) {
										// Error - unable to record the e-mail sent time
										trigger_error("Problem in recording the e-mail sent time (see school $school_id, blast ID $blast_id, student ID $student->studentid, student name $student->name)", E_USER_ERROR);
										$error_triggered = true;
										break;
									}
								}
							}
						}
						// If we reached this point error-free, continue to next step (record stop time and mark scheduled blast as "completed")
						if ($error_triggered != true) {
							// Set the e-mail blast's stop time to reflect current time
							$time = $cdb->setEmailBlastFollowupAdminTime($school_id, $blast_id, "email", "stop");
							if (!$time) {
								// Error - unable to record the follow-up e-mail blast stop time
								trigger_error("Problem in recording the follow-up e-mail blast stop time (see school $school_id, blast ID $blast_id, name $name)", E_USER_ERROR);
								$error_triggered = true;
								exit;
							} else {
								// Update the scheduled e-mail blast status from "processing" to "completed"
								$status_update = $cdb->updateScheduledEmailBlastStatus($id, "completed");
								if ($status_update == "badvar") {
									// Error - invalid variable attempted to be used against the database
									trigger_error("Problem in updating status of scheduled e-mail blast to 'completed' (invalid variable; see school $school_id, ID $id, name $name)", E_USER_ERROR);
									$error_triggered = true;
									exit;
								} elseif ($status_update == "badupdate") {
									// Error - UPDATE statement failure in the database
									trigger_error("Problem in updating status of scheduled e-mail blast to 'completed' (SELECT statement failed; see school $school_id, ID $id, name $name)", E_USER_ERROR);
									$error_triggered = true;
									exit;
								} else {
									// Log that e-mail blast successfully completed
									error_log("[" . date("D M d Y H:i:s T") . "] [SUCCESS]: Scheduled e-mail blast successfully completed (school $school_id, blast ID $blast_id)\n", 3, $path . "/sysadmin/scheduled_emails/scheduled_emails.log");
								}
							}
						}
					} else {
						// Notice - no unsent student records found within selected upload blast group
						trigger_error("Unable to complete follow-up e-mail blast (no unsent students remaining; see school $school_id, blast ID $blast_id, name $name)", E_NOTICE);
						$error_triggered = true;
					}
				}
			}
		}
	}
}
?>
