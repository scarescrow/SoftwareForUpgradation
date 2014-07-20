<?php

	include 'connection.php';

	/*
	* First, create first csv file
	*/

	$query = "SELECT * FROM upgradation_result";
	$result = mysql_query($query) or die(mysql_error());
	$num = mysql_num_rows($result);
	
	$fn = 'csv/Upgradation_Result.csv';
	$file = fopen($fn,"w");
	$headers = array("S.No","Name","Parent's Name","College", "Percentage", "Current Branch", "New Branch", "Current Roll No.", "New Roll No.");
	fputcsv($file, $headers);
	for($i = 0; $i < $num; $i ++) {
	
		$content = Array();
		$content[] = mysql_result($result, $i, "id");
		$content[] = mysql_result($result, $i, "Name");
		$content[] = mysql_result($result, $i, "Parent_Name");
		$content[] = mysql_result($result, $i, "Institute");
		$content[] = mysql_result($result, $i, "Percentage");
		$content[] = mysql_result($result, $i, "Branch_Old");
		$content[] = mysql_result($result, $i, "Branch_New");
		$content[] = '\''.mysql_result($result, $i, "Roll_No");
		$content[] = '\''.mysql_result($result, $i, "New_Enrollment_No");
		
		fputcsv($file, $content);
	
	}
	fclose($file);
	
	/*
	* Now, create second csv file
	*/

	$query = "SELECT * FROM upgradation_college";
	$result = mysql_query($query) or die(mysql_error());
	$num = mysql_num_rows($result);
	
	$fn_2 = 'csv/New_College_Status.csv';
	$file_2 = fopen($fn_2, "w");
	$headers_2 = array("S.No","College","Branch","Sanctioned Intake", "Permissible Limit", "Total Vacancy", "Sample Roll No.", "No Allotted For Upgradation", "Final Vacancy", "New Enrollment No");
	fputcsv($file_2, $headers_2);
	for($i = 0; $i < $num; $i ++) {
	
		$content = Array();
		$content[] = mysql_result($result, $i, "id");
		$content[] = mysql_result($result, $i, "College");
		$content[] = mysql_result($result, $i, "Branch");
		$content[] = mysql_result($result, $i, "Sanctioned_Intake");
		$content[] = mysql_result($result, $i, "Permissible_Limit");
		$content[] = mysql_result($result, $i, "Total_Vacancy");
		$content[] = '\''.mysql_result($result, $i, "Sample_Roll_No");
		$content[] = mysql_result($result, $i, "No_Allotted");
		$content[] = mysql_result($result, $i, "No_Vacant");
		$content[] = '\''.mysql_result($result, $i, "New_Sample_Roll_No");
		
		fputcsv($file_2, $content);
	
	}
	fclose($file_2);

?>

<html>
<head>
<title>Results Declared</title>
</head>
<body>
The excel files have been generated. <br><br><a href="<?php echo $fn; ?>">Click Here</a> To Download Excel File for the Upgradation Result.
<br><br><a href="<?php echo $fn_2; ?>">Click Here</a> To Download Excel File for Final Upgradation Result.
</body>
</html>