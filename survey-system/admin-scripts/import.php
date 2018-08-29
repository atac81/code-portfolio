<?php
  // Executes the following code only if a file was uploaded
	if (($_FILES['importfile']['size'] != 0) && ($_FILES['importfile']['tmp_name'] != "none")) {
		// These are the allowed extensions of the files that are uploaded
		$allowed_ext = "txt";
		
		// Check Entension
		$extension = pathinfo($_FILES['importfile']['name']);
		$extension = $extension['extension'];
		$allowed_paths = $allowed_ext;
		if ($allowed_paths != "$extension") {
			$error_list .= "<li>The file you are trying to upload has an invalid file type. This upload currently only handles txt files.</li>\n";
			displayForm($cdb, $error_list);
		} else {
			if (is_uploaded_file($_FILES['importfile']['tmp_name'])) {
				// Open and read the upload file
				$FileName = $_FILES['importfile']['tmp_name'];
				$FileHandle = fopen($FileName,"r");
				$FileContent = fread ($FileHandle, filesize($FileName));
				fclose($FileHandle);
				unlink($FileName);
				
				// Split the contents of the file into separate lines based on the newline delimiter
				$LineContent = explode("\n", $FileContent);
				 
				// For each line in the file, split that data into a new array based on the tab delimiter
				$SplitContent = array();
				$key = 0;
				foreach ($LineContent as $value) {
				   // Explode that data into a new array:  
				   $SplitContent[$key] = explode("\t", $value);
				   ++$key;
				}

				$key_pin = "";
				$key_name = "";
				$key_email = "";
				$key_school = "";
				$key_course = "";
				$key_department = "";
				$key_instructor = "";
				$key_semester = "";
				$FileSize = sizeof($SplitContent);
				for ($K = 0; $K < $FileSize; ++$K) {
					if ($K != 0) {
						// Set variables based on designated location within each line
						$pin = trim($SplitContent[$K][$key_pin]);
						$name = trim($SplitContent[$K][$key_name], "\" \t\n\r\0\x0B");
						$email = trim($SplitContent[$K][$key_email], "\" \t\n\r\0\x0B");
						$school = trim($SplitContent[$K][$key_school], "\" \t\n\r\0\x0B");
						$course = trim($SplitContent[$K][$key_course], "\" \t\n\r\0\x0B");
						$department = trim($SplitContent[$K][$key_department], "\" \t\n\r\0\x0B");
						$instructor = trim($SplitContent[$K][$key_instructor], "\" \t\n\r\0\x0B");
						$semester = trim($SplitContent[$K][$key_semester], "\" \t\n\r\0\x0B");
						$courseid = trim($SplitContent[$K][$key_courseid], "\" \t\n\r\0\x0B");

						// Checks if the PIN is in database
						$pin_check = $cdb->confirmStudentPin($pin);
						if ( ($pin_check == 0) || ($pin_check == 3) ) {
							// Error - PIN was found in the database
							$error_list .= "<li>The PIN already exists in the database (see PIN $pin).</li>\n";
							break;
						} elseif ($pin_check == 1) {
							// Error - invalid PIN attempted to be used against the database
							$error_list .= "<li>You are attempting to import an invalid PIN value into the database (see PIN $pin).</li>\n";
							break;
						} else {
							// PIN does not exist in the database
							// Add the new student information
							$pin_insert = $cdb->insertStudentPin($pin, $name, $email, $school, $course, $department, $instructor, $semester);
							if (substr($pin_insert, 0, 3) == "bad") {
								$error_list .= "<li>You are attempting to add an invalid " . substr($pin_insert, 3) . " value into the database (see PIN $pin).</li>\n";
								break;
							} elseif (!$pin_insert) {
								$error_list .= "<li>There was a problem with inserting PIN " . $pin . " into the database.&nbsp;&nbsp;Please try again.</li>\n";
								break;
							}
						}
					} else {
						// Split each line into its seperate parts
						foreach ($SplitContent[$K] as $key => $value) {
							// Define keys for each variable type (first line of import file only)
							$value = trim($value);
							if ($value == "PIN") {
								$key_pin = $key;
							} elseif ($value == "NAME") {
								$key_name = $key;
							} elseif ($value == "EMAIL") {
								$key_email = $key;
							} elseif ($value == "SCHOOL") {
								$key_school = $key;
							} elseif ($value == "COURSE") {
								$key_course = $key;
							} elseif ($value == "DEPT") {
								$key_department = $key;
							} elseif ($value == "INSTR") {
								$key_instructor = $key;
							} elseif ($value == "SEMESTER") {
								$key_semester = $key;
							}
						}
					}
				}
			  
				if ($error_list != "") {
					displayForm($cdb, $error_list);
				} else {
					header("Location: import.php");
				}
			} else {
				switch($_FILES['importfile']['error']){
					case 0: //no error; possible file attack!
						$error_list .= "<li>There was a problem with your file upload (possible file upload attack).<br>" . print_r($_FILES, true) . "</li>\n";
						break;
					case 1: //uploaded file exceeds the upload_max_filesize directive in php.ini
						$error_list .= "<li>The file you are trying to upload is too big.</li>\n";
						break;
					case 2: //uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the html form
						$error_list .= "<li>The file you are trying to upload is too big.</li>\n";
						break;
					case 3: //uploaded file was only partially uploaded
						$error_list .= "<li>The file you are trying to upload was only partially uploaded.&nbsp;&nbsp;Please try again.</li>\n";
						break;
					case 4: //no file was uploaded
						$error_list .= "<li>You must select a file for upload.</li>\n";
						break;
					default: //a default error, just in case!  :)
						$error_list .= "<li>There was a problem with your file upload.&nbsp;&nbsp;Please try again.</li>\n";
						break;
				}
				unlink($_FILES['importfile']['tmp_name']);
				displayForm($cdb, $error_list);
			}
		}
	} else {
		$error_list .= "<li>You must select a file for upload.</li>\n";
		displayForm($cdb, $error_list);
	}
?>
