<?php
session_start(); 
include('cdb.inc');

header("Cache-Control: no-store, no-cache, must-revalidate"); 

$cdb = new CDB;
$error_type = "";
$error_list = "";

function displayForm($book, $error_type, $error_list) {
    $page_id = ( isset($_GET['page']) && is_numeric($_GET['page']) && preg_match('/^[1|2|3|4|5]{1}$/i', $_GET['page']) ) ? 
    intval($_GET['page']) : 0;
    
    $book_src = "./book_view.html?id=" . $book->id . "#page=" . $page_id . "&navpanes=0&scrollbar=0&statusbar=0&view=FitV";
?>
<html>
<head>
    <title>Online Test Scoring System</title>
    <link href="global.css" rel="stylesheet" type="text/css" />
<?	if ($page_id <= 4) {	?>
	<script language="javascript">
		function resize_iframe()
		{
			var height = 0;
			if( typeof( window.innerWidth ) == 'number' ) {
				//Non-IE
				height = window.innerHeight;
			} else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) {
				//IE 6+ in 'standards compliant mode'
				height = document.documentElement.clientHeight;
			} else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) {
				//IE 4 compatible
				height = document.body.clientHeight;
			}
			
			//resize the iframe according to the size of the
			//window (all these should be on the same line)
			document.getElementById("bookframe").style.height=parseInt(height-document.getElementById("bookframe").offsetTop-150)+"px";
		}
		
		// this will resize the iframe every
		// time you change the size of the window.
		window.onresize=resize_iframe();
    </script>
<?	}	?>
</head>

<body<? echo ($page_id <= 4) ? ' onLoad="resize_iframe();"' : ''; ?>>
<table width="100%" height="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td id="header">
            <table width="950" align="center" border="0" cellpadding="0" cellspacing="0">
            	<tr>
                	<td id="headerCopy" width="158"><img align="left" src="images/logo_ltf.gif" height="60" width="158"></td>
                	<td id="headerCopy" align="left">Online Test Scoring System</td>
                	<td id="headerCopy" align="right"><a href="menu.html">Main Menu</a>&nbsp;&nbsp;&bull;&nbsp;&nbsp;<a id="logout" href="logout.html">Logout</a>
</td>
                </tr>
            </table>
		</td>
    </tr>
	<tr>
		<td align="center" height="100%" valign="top">
        	<table id="ReaderBody" height="100%" border="0" valign="top">
            	<tr>
                	<td align="center" height="100%" valign="top">
                        <?	if ($error_list != "") {	?>
                        <div id="ErrorMsg">
                            <img align="left" src="images/icon_warning.gif" height="25" width="28" style="margin-top: 4px; margin-left: 15px; margin-right: 15px;">
                            <b>A problem was encountered processing your request:</b><br><? echo $error_list; ?>
                        </div><br>
                        <?	}	?>
                        <div id="ReaderBody"><?
                        	if ($page_id == 5) {
                            	if ( ($error_type == "") || (($error_type == "badselect") || ($error_type == "badscore") 
                                														  || ($error_type == "badinsert")) ) {	?>
                            <p>Please enter a score for this book by selecting the corresponding radio button.&nbsp;&nbsp;You may also 
                            	provide any additional comments regarding the book in the space below.&nbsp;&nbsp;When you are 
                            	finished, press the "Submit Score" button to record the score.</p>
                            <form id="score" name="score" method="post" action="book_score.html?id=<? echo $book->id; ?>&page=<? echo $page_id; ?>">
                            <input type="hidden" id="status" name="status" value="">
                            <table align="center" border="0" cellpadding="10" cellspacing="10">
                            	<tr>
                                    <td align="center" valign="middle"><input type="radio" name="book_score" value="0"></td>
                                    <td align="center" valign="middle"><input type="radio" name="book_score" value="1"></td>
                                    <td align="center" valign="middle"><input type="radio" name="book_score" value="2"></td>
                                    <td align="center" valign="middle"><input type="radio" name="book_score" value="3"></td>
                                    <td align="center" valign="middle"><input type="radio" name="book_score" value="4"></td>
                                    <td align="center" valign="middle"><input type="radio" name="book_score" value="5"></td>
                                    <td align="center" valign="middle"><input type="radio" name="book_score" value="6"></td>
                                    <td align="center" valign="middle"><input type="radio" name="book_score" value="7"></td>
                                    <td align="center" valign="middle"><input type="radio" name="book_score" value="8"></td>
                                    <td align="center" valign="middle"><input type="radio" name="book_score" value="9"></td>
                                    <td align="center" valign="middle"><input type="radio" name="book_score" value="-"></td>
                                </tr>
                            	<tr>
                                    <td align="center" valign="middle">0</td>
                                    <td align="center" valign="middle">1</td>
                                    <td align="center" valign="middle">2</td>
                                    <td align="center" valign="middle">3</td>
                                    <td align="center" valign="middle">4</td>
                                    <td align="center" valign="middle">5</td>
                                    <td align="center" valign="middle">6</td>
                                    <td align="center" valign="middle">7</td>
                                    <td align="center" valign="middle">8</td>
                                    <td align="center" valign="middle">9</td>
                                    <td align="center" valign="middle">-</td>
                                </tr>
                            	<tr>
                                    <td colspan="11" align="center" valign="middle">Additional Comments:<br /><textarea rows="10" cols="85" name="comments"></textarea></td>
                                </tr>
                            	<tr>
                                	<td colspan="11" align="center" valign="middle"><input type="submit" id="SubmitScore" name="SubmitScore" value="Submit Score" onClick="document.score.SubmitScore.disabled=true; document.score.status.value='submit'; document.score.submit();" /></td>
                                </tr>
                            </table>
                            </form><?
                            	}
                            } else {
                            	if ($error_type == "") {	?>
                            <p>You are currently viewing page #<? echo $page_id;?> of the book.&nbsp;&nbsp;To select a different page, 
                            	select either the Previous or Next button below.</p>
                            <iframe id="bookframe" src="<? echo $book_src; ?>" width="100%">
                                [Your browser does <em>not</em> support <code>iframe</code>, or it has been configured not to display 
                                inline frames.&nbsp;&nbsp;You must either access this system using an iframe-supported browser 
                                (Internet Explorer, Firefox, Safari, etc.) or configure your browswer to display inline 
                                frames.]</iframe><?
                            	}
                            }	?>
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
	<tr>
		<td id="footer">
            <table width="950" align="center" border="0" cellpadding="0" cellspacing="0">
            	<tr>
                	<td id="footerCopy" align="left">Copyright &copy; <?php echo date('Y'); ?>.&nbsp;&nbsp;All Rights Reserved.</td>
                	<td align="right">
                        <ul id="booknav"><?
                        	if ($page_id >= 2) {
                            	$prev_page = $page_id - 1;	?>
                            <li><a href="book_score.html?id=<? echo $book->id; ?>&page=<? echo $prev_page; ?>">Previous</a></li><?
                            }
                            if ($page_id <= 4) {
                            	$next_page = $page_id + 1;	?>
                            <li><a href="book_score.html?id=<? echo $book->id; ?>&page=<? echo $next_page; ?>">Next</a></li>
                            <li><a href="book_score.html?id=<? echo $book->id; ?>&page=5">End</a></li><?
                            }	?>
                        </ul>
                    </td>
                </tr>
            </table>
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
	/* Redirect back to the login page */
	header("Location: login.php");
} else {
	$book_id = ( isset($_GET['id']) && is_numeric($_GET['id']) ) ? intval($_GET['id']) : 0;

    /* Retrieve the reader's book information */
    $book = $cdb->getBookInfo($book_id, $_SESSION['OnlineCode']);

    /* Check error codes */
    if ($book == "badbookid") {
        /* Generate and display error message */
        $error_type = $book;
        $error_list .= "You are attempting to use an invalid book ID.";
        displayForm($book, $error_type, $error_list);
    } elseif ($book == "badpin") {
        /* Generate and display error message */
        $error_type = $book;
        $error_list .= "You are attempting to use an invalid reader ID.";
        displayForm($book, $error_type, $error_list);
    } elseif ($book == "badreaderid") {
        /* Generate and display error message */
        $error_type = $book;
        $error_list .= "You are not authorized to view this book.";
        displayForm($book, $error_type, $error_list);
    } elseif ($book == "prevscored") {
        /* Generate and display error message */
        $error_type = $book;
        $error_list .= "This book has already been scored.";
        displayForm($book, $error_type, $error_list);
    } elseif ($book == "badselect") {
        /* Generate and display error message */
        $error_type = $book;
        $error_list .= "There was an error in retrieving the book information.&nbsp;&nbsp;Please try again.";
        displayForm($book, $error_type, $error_list);
    } else {
        /* Set start time for review/scoring upon displaying page 1 */
    	  $page = ( isset($_GET['page']) && is_numeric($_GET['page']) && preg_match('/^[1|2|3|4|5]{1}$/i', $_GET['page']) ) ? 
    		intval($_GET['page']) : 0;
        if ($page == 1) {
            /* Set the reader's start time to reflect current time */
            $time = $cdb->setStartTime($book_id);
                
            if ($time == "badbookid") {
                /* Generate and display error message */
                $error_type = $insert;
                $error_list .= "You are attempting to use either an invalid or deactivated book ID.";
                displayForm($book, $error_type, $error_list);
            } elseif ($time == "badupdate") {
                /* Generate and display error message */
                $error_type = $insert;
                $error_list .= "There was an error in recording your review start time.&nbsp;&nbsp;Please try again.";
                displayForm($book, $error_type, $error_list);
            } else {
                /* Load the book information */
                displayForm($book, $error_type, $error_list);
            }
        }
        /* Check if score has been submitted */
        elseif(isset($_POST['status']) && ($_POST['status'] == "submit")){
        
             /* Record the student user's survey responses */
            $insert = $cdb->insertBookScore($book->id, $_POST);
    
            /* Check error codes */
            if ($insert == "badbookid") {
                /* Generate and display error message */
                $error_type = $insert;
                $error_list .= "You are attempting to use either an invalid or deactivated book ID.";
                displayForm($book, $error_type, $error_list);
            } elseif ($insert == "badselect") {
                /* Generate and display error message */
                $error_type = $insert;
                $error_list .= "There was an error in verifying the book's score history.&nbsp;&nbsp;Please try again.";
                displayForm($book, $error_type, $error_list);
            } elseif ($insert == "preventry") {
                /* Generate and display error message */
                $error_type = $insert;
                $error_list .= "This book has already been scored.";
                displayForm($book, $error_type, $error_list);
            } elseif ($insert == "badscore") {
                /* Generate and display error message */
                $error_type = $insert;
                $error_list .= "You are attempting to submit an invalid value for the book's score.&nbsp;&nbsp;"
                	       		.  "Please try again.";
                displayForm($book, $error_type, $error_list);
            } elseif ($insert == "badinsert") {
                /* Generate and display error message */
                $error_type = $insert;
                $error_list .= "There was an error in recording the book's score.&nbsp;&nbsp;Please try again.";
                displayForm($book, $error_type, $error_list);
            } else {
                /* Quick redirect to Main Menu page */
                echo "<meta http-equiv=\"Refresh\" content=\"0;url=menu.php?msg=scoresuccess\">";
                return;
            }
        } else {
            /* Load the book information */
            displayForm($book, $error_type, $error_list);
        }
    }
}
?>
