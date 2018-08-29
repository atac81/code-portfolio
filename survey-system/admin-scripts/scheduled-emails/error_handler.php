<?php 
function error_alert() 
{ 
	if(is_null($err = error_get_last()) === false) 
	{ 
		// setup error variables
		$dt = date("D M d Y H:i:s T");
		$errno = $err['type'];
		$errmsg = $err['message'];
		$filename = $err['file'];
		$linenum = $err['line'];

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
		$err_mail .= "Error triggered within the error_handler script\n";

		$err_log = "[" . $dt . "] [ERROR " . $errortype[$errno] . " (" . $errno . ")]: " . $errmsg . " in " . $filename . " on line " . $linenum . "\n";

		// save to the error log and send e-mail
		error_log($err_log, 3, "/path/to/server/logs/scheduled_emails.log");

		mail('admin@sample.com', 'Error from Automatic E-mail System', $err_mail, 'From: Server Automated Message <webmaster@sample.com>'); 
	} 
} 

register_shutdown_function('error_alert'); 
?> 
