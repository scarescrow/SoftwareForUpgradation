<?php

	include 'connection.php';
	
	$counter1 = 0;
	$counter2 = 0;
	$counter3 = 0;
	
	/*Reading First CSV*/
	
	//Move CSV
	
	if ($_FILES["myfile"]["error"] > 0)
	{
		die("Error: " . $_FILES["myfile"]["error"] . "<br />");
	}
	else
	{
		move_uploaded_file($_FILES["myfile"]["tmp_name"],"csv/" . $_FILES["myfile"]["name"]);
		$path="csv/" . $_FILES["myfile"]["name"];
	}
	
	//Read CSV
	$counter = 0;
	if(end(explode('.',$path))=='csv')
	{
		mysql_query("TRUNCATE upgradation") or die(mysql_error());
		$file_handle = fopen($path, "r");
		while (!feof($file_handle)) 
		{
			$line = fgetcsv($file_handle, 1024, ",", "\n");
			if($counter >= $no_of_useless_lines && $line[0] != "") {
				
				for($i = 0; $i < count($line) - 3; $i++)
					$line[$i] = clean($line[$i]);
					
				$sno = $line[0];
				$roll = $line[5];
				$name = $line[1];
				$parent_name = $line[2];
				$institute = $line[3];
				$branch = branch($line[4]);
				$percentage = $line[6];
				$cpi = $line[7];
				
				$pref1 = substr($line[9],0,3);
				$pref2 = substr($line[10],0,3);
				$pref3 = substr($line[11],0,3);
				$pref4 = substr($line[12],0,3);
				
				if($pref1 == "CIV") $pref1 = substr($line[9],0,5);
				if($pref2 == "CIV") $pref2 = substr($line[9],0,5);
				if($pref3 == "CIV") $pref3 = substr($line[9],0,5);
				if($pref4 == "CIV") $pref4 = substr($line[9],0,5);
				
				$query = "INSERT INTO upgradation (Name,Parent_Name,College_Code,Branch,Enrollment_No,Percentage,CPI,Pref1,Pref2,Pref3,Pref4)
								VALUES ('$name','$parent_name','$institute','$branch','$roll','$percentage','$cpi','$pref1','$pref2','$pref3','$pref4');";
								
				$result = mysql_query($query, $con) or die(mysql_error());
				
				$counter1++;
				
			}
			$counter++;
		}
		fclose($file_handle);
		
		
	}
	
	/*Reading Second CSV*/
	
	//Move CSV
	
	if ($_FILES["myfile_1"]["error"] > 0)
	{
		die("Error: " . $_FILES["myfile_1"]["error"] . "<br />");
	}
	else
	{
		move_uploaded_file($_FILES["myfile_1"]["tmp_name"],"csv/" . $_FILES["myfile_1"]["name"]);
		$path="csv/" . $_FILES["myfile_1"]["name"];
	}
	
	//Read CSV
	$counter = 0;
	if(end(explode('.',$path))=='csv')
	{
		mysql_query("TRUNCATE upgradation_final") or die(mysql_error());
		$file_handle = fopen($path, "r");
		while (!feof($file_handle)) 
		{
			$line = fgetcsv($file_handle, 1024, ",", "\n");
			if($counter >= $no_of_useless_lines_in_verified_percentage && $line[4] != "") {
				
				for($i = 0; $i < count($line) - 3; $i++)
					$line[$i] = clean($line[$i]);
					
				$sno = $line[0];
				$roll = $line[1];
				$name = $line[2];
				$parent_name = $line[3];
				$institute = $line[4];
				$branch = branch($line[5]);
				$percentage = $line[6];
				$cpi = $line[7];
				
				$query = "INSERT INTO upgradation_final (Roll_No,Name,Parent_Name,Institute,Branch,Percentage)
								VALUES ('$roll','$name','$parent_name','$institute','$branch','$percentage');";
								
				$result = mysql_query($query, $con) or die(mysql_error());
				
				$counter2++;
				
			}
			$counter++;
		}
		fclose($file_handle);
		
		
	}
	
	/*Reading Third CSV*/
	
	
	//Move CSV
	if ($_FILES["myfile_2"]["error"] > 0)
	{
		die("Error: " . $_FILES["myfile_2"]["error"] . "<br />");
	}
	else
	{
		move_uploaded_file($_FILES["myfile_2"]["tmp_name"],"csv/" . $_FILES["myfile_2"]["name"]);
		$path="csv/" . $_FILES["myfile_2"]["name"];
	}
	$college = "";
	
	//Read CSV
	$counter = 0;
	if(end(explode('.',$path))=='csv')
	{
		mysql_query("TRUNCATE upgradation_college") or die(mysql_error());
		$file_handle = fopen($path, "r");
		while (!feof($file_handle)) 
		{
			$line = fgetcsv($file_handle, 1024, ",", "\n");
			if($counter >= $no_of_useless_lines_college && $line[11] != "") {
				
				for($i = 0; $i < count($line) - 3; $i++)
					$line[$i] = clean($line[$i]);
					
				if($line[0] != "") 
					$college = $line[1];
					
				$branch = branch($line[2]);
				
				if(strpos($line[2], "2nd") !== false && strpos($college, "2S") === false)
					$college = $college.'(2S)';
				
				$sanctioned_intake = $line[3];
				if($line[9] == "")
					$permissible_limit = 0;
				else
					$permissible_limit = $line[9];
				
				$total_vacancy = $line[10];
				$sample_roll = $line[11];
				$allowed_students = (intval($total_vacancy) <= intval($permissible_limit)) ? $total_vacancy : $permissible_limit;
				
				
				$query = "INSERT INTO upgradation_college (College, Branch, Sanctioned_Intake, Permissible_Limit, Total_Vacancy, No_Allowed, Sample_Roll_No)
								VALUES ('$college','$branch','$sanctioned_intake','$permissible_limit','$total_vacancy','$allowed_students', '$sample_roll');";
				
				$result = mysql_query($query, $con) or die(mysql_error());
				
				$counter3++;
				
			}
			$counter++;
		}
		fclose($file_handle);
		
		
	}
	
	
	//Cleaner Function
	function clean($str) {
		
		$str = str_replace("'", "", $str);
		$str = str_replace("/", "", $str);
		$str = str_replace("\\", "", $str);
		
		return $str;
	}
	
	//Find Branch From Pragramme
	function branch($str) {
		
		$start = strpos($str, "(") + 1;
		$stop = strpos($str, ")");
		$branch = substr($str, $start, $stop - $start);
		
		return $branch;
	
	}
	
	//Clean Preference 
	function pref_clean($str) {
		
		if(strpos($str, "Shift") !== false) {
			$find = strpos($str, "-");
			$str = substr($str, 0, $find);
		}
		
		return $str;
	}
	
	$final_query = "UPDATE upgradation_final a SET Pref1 = (SELECT Pref1 FROM upgradation b WHERE b.Enrollment_No = a.Roll_No), Pref2 = (SELECT Pref2 FROM upgradation b WHERE b.Enrollment_No = a.Roll_No), Pref3 = (SELECT Pref3 FROM upgradation b WHERE b.Enrollment_No = a.Roll_No), Pref4 = (SELECT Pref4 FROM upgradation b WHERE b.Enrollment_No = a.Roll_No)";
	mysql_query($final_query) or die(mysql_error());

?>


<html>
<head>
<title> Names Uploaded </title>
</head>
<body>
No. of rows in first CSV: <?php echo $counter1; ?><br><br>
No. of rows in second CSV: <?php echo $counter2; ?><br><br>
No. of rows in third CSV: <?php echo $counter3; ?><br><br>
<a href="parser.php">Click Here</a> To Calculate Result.
</body>
</html>