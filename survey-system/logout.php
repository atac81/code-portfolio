<?
session_start(); 
include('./includes/cdb.include');

header("Cache-Control: no-store, no-cache, must-revalidate"); 

$cdb = new CDB;

/* Verify that the current user is logged into the system */
$logged_in = $cdb->checkLogin();

if(!$logged_in){
	/* Quick redirect to login page to avoid resending data on refresh */
	echo "<meta http-equiv=\"Refresh\" content=\"0;url=login.php\">";
	return;
} else {
	/* Kill session variables */
	unset($_SESSION['StudentCode']);
	$_SESSION = array(); // Reset session array
  session_unset();     // Free all session variables
	session_destroy();   // Destroy session
  
	/* Quick redirect to login page to avoid resending data on refresh */
	echo "<meta http-equiv=\"Refresh\" content=\"0;url=login.php\">";
	return;
?>
