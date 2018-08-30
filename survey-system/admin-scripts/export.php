<?php
ini_set("memory_limit","512M");
include('cdb.inc');

$error_list = "";
$cdb = new CDB;

// Filters string content for any possible characters that will corrupt data output into tab-delimited format
function cleanData(&$str) {
    $str = preg_replace("/\n/", " ", $str);		// Replace Newline Character With Space
    $str = preg_replace("/\r/", " ", $str);		// Replace Carriage Return Character With Space
    $str = preg_replace("/\t/", "\\t", $str);		// Escape Horizontal Tab Character
}

// Create the download file for outputting the data into tab-delimited format
function createExportFile($type, $data) {
    // Set file name for download
    $filename = "survey_results_" . date('Ymd') . ".txt";
    
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header("Content-Type: text/plain");
    
    $flag = false;
    foreach($data as $row) {
        if(!$flag) {
          // Display field/column names as first row
          echo implode("\t", array_keys($row)) . "\r\n";
          $flag = true;
        }
        array_walk($row, 'cleanData');
        foreach($row as $key => $value) {
          $value = html_entity_decode($value, ENT_QUOTES | ENT_XML1, 'UTF-8');	// Convert all HTML entities to their applicable characters
          $row[$key] = $value;
        }
        echo implode("\t", array_values($row)) . "\r\n";
    }
}

// Create the download files for outputting the data into tab-delimited format, then add them into a ZIP archive
function createZipArchive($type, $data) {
    // Create the Zip file
    $zipfile = 'survey_results_' . date('Ymd') . '.zip';
    $zipfilepath = '/tmp/' . $zipfile;
    $zip = new ZipArchive;
    $res = $zip->open($zipfilepath, ZipArchive::CREATE);
    
    if ($res !== TRUE) {
        die('Failed to create ZIP archive file');
    } else {
        $key_num = 0;
        $survey_list = array_keys($data);

        // Loop through data for each survey table
        foreach($data as $survey) {
            $flag = false;
    
            // Set current survey filename
            $survey_file = $survey_list[$key_num];

            // Create a temporary file
            // The memory threshold is set to 1 MB (1024 * 1024).
            // If the file gets larger than that, PHP would create a temporary file; 
            // otherwise, all will happen in memory.
            $fd = fopen('php://temp/maxmemory:1048576', 'w');
            if (false === $fd) {
                die('Failed to create temporary file');
            }
            
            // Loop through rows of a given survey table
            foreach($survey as $row) {
                // Set field/column names as first row in the file
                if(!$flag) {
                    // Format the data as tab-delimited values
                    fputcsv($fd, array_keys($row), "\t");
                    fwrite($fd, "\r\n");
                    $flag = true;
                }
    
                // Apply to every part of the data row a filter for any possible characters that will corrupt data output into tab-delimited format
                array_walk($row, 'cleanData');
    
                // Format the data as tab-delimited values
                foreach($row as $key => $value) {
                    $value = html_entity_decode($value, ENT_QUOTES | ENT_XML1, 'UTF-8');	// Convert all HTML entities to their applicable characters
                    $row[$key] = $value;
                }
                
                // Add the row of data into the file
                fputcsv($fd, array_values($row), "\t");
                fwrite($fd, "\r\n");
            }
            // Return to the start of the file stream
            rewind($fd);
        
            // Add the in-memory file to the archive, giving it a name
            $zip->addFromString('survey_' . $survey_file . '_results_' . date('Ymd') . '.txt', stream_get_contents($fd) );
            
            // Close the file
            fclose($fd);
            
            ++$key_num;
        }

        // Close the Zip archive
        $zip->close();
        
        // Generate the archive file for download
        header('Content-Type: application/zip');
        header('Content-disposition: attachment; filename='.$zipfile);
        header('Content-Length: ' . filesize($zipfilepath));
        readfile($zipfilepath);
        
        // Remove the Zip archive
        unlink($zipfilepath);
    }
}

if (isset($_POST['ResultsSelected'])) {
    /* User selected to export users' survey results */
    
    if ($id == "all") {
        /* Generate and display error message */
        $error_list .= "You cannot select to export users' survey results for all " 
        			. $type . "s.&nbsp;&nbsp;Please select only one " . $type . ".";
        displayMenu($error_list);
    } else {
        /* Retrieve survey results */
        $db_data = $cdb->getSurveyResults($type, $id, $sort);
        if ($db_data == "badtype") {
            /* Generate and display error message */
            $error_list .= "You are attempting to use an invalid list type.";
            displayMenu($error_list);
        } elseif ($db_data == "badcourse") {
            /* Generate and display error message */
            $error_list .= "You are attempting to use an invalid course ID.";
            displayMenu($error_list);
        } elseif ($db_data == "badschool") {
            /* Generate and display error message */
            $error_list .= "You are attempting to use an invalid school name.";
            displayMenu($error_list);
        } elseif ($db_data == "badsort") {
            /* Generate and display error message */
            $error_list .= "You are attempting to use an invalid sort field.";
            displayMenu($error_list);
        } elseif ($db_data == "badselect") {
            /* Generate and display error message */
            $error_list .= "There was an error in retrieving the survey results.&nbsp;&nbsp;Please try again.";
            displayMenu($error_list);
        } else {
            /* Generate the ZIP archive of export files */
            createZipArchive("results", $db_data);
            return;
        }
    }
} else {
    /* No user selection */
    $error_list .= "You have not entered a valid selection.&nbsp;&nbsp;Please try again.";
    displayMenu($error_list);
}
?>
