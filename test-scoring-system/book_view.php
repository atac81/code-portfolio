<?php
session_start(); 
include('cdb.inc');

$cdb = new CDB;

function displayError($error_list){
?>
<html>
<head>
    <title>Online Test Scoring System - Error</title>
    <link href="global.css" rel="stylesheet" type="text/css" />
</head>
<body>
<table width="100%" border="0" cellpadding="10" cellspacing="10">
	<tr>
		<td align="center" valign="top">
            <div id="ErrorMsg">
                <img align="left" src="images/icon_warning.gif" height="25" width="28" style="margin-top: 4px; margin-left: 15px; margin-right: 15px;">
                <b>A problem was encountered processing your request:</b><br><? echo $error_list; ?>
            </div>
        </td>
    </tr>
</table>
</body>
</html>
<?
}

function error403() {
    header("HTTP/1.1 403 Forbidden");
    include('error_403.html');
    exit();
}

/* Verify that the current user is logged into the system */
$logged_in = $cdb->checkLogin();

if(!$logged_in){
    /* Redirect back to the login page */
    header("Location: login.php");
} else {
    $book_id = ( isset($_GET['id']) && is_numeric($_GET['id']) ) ? intval($_GET['id']) : 0;
    
    /* Retrieve the reader's book information */
    $book = $cdb->getBookInfo($book_id, $_SESSION['OnlineCode']);

    /* Check error codes */
    if ($book == "badbookid") {
        /* Generate and display error message */
        $error_list .= "You are attempting to use an invalid book ID.";
        displayError($error_list);
    } elseif ($book == "badreaderid") {
        /* Generate and display error message */
        $error_list .= "You are not authorized to view this book.";
        displayError($error_list);
    } elseif ($book == "badselect") {
        /* Generate and display error message */
        $error_list .= "There was an error in retrieving the book information.&nbsp;&nbsp;Please try again.";
        displayError($error_list);
    } else {
        // The location on the server where the files are located
        //$filedir = '/path/to/books/';
        $filename = './books/' . $book->barcode . '.pdf'; 

		/* Check that the file actually exists */
        if (!file_exists($filename)) {
        	/* Redirect to the Error 403 page */
        	error403();
        } else {
        	/* Display the PDF file */
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Content-type: application/pdf"); 
            header("Content-Transfer-Encoding: Binary"); 
            header("Content-length: ".filesize($filename)); 
            readfile("$filename");
        }
    }
}
?>
