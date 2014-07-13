<?php

	include 'connection.php';
	
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
		$file_handle = fopen($path, "r");
		while (!feof($file_handle)) 
		{
			$line = fgetcsv($file_handle, 1024, ",", "\n");
			if($counter >= $no_of_useless_lines && count($line) == 8) {
				
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
				
			}
			$counter++;
		}
		fclose($file_handle);
		
		
	}
	
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
	if(end(explode('.',$path))=='csv')
	{
		$file_handle = fopen($path, "r");
		while (!feof($file_handle)) 
		{
			$line = fgetcsv($file_handle, 1024, ",", "\n");
			if($counter >= $no_of_useless_lines_college) {
				
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
				$allowed_students = (intval($total_vacancy) <= intval($permissible_limit)) ? $total_vacancy : $permissible_limit;
				
				
				$query = "INSERT INTO upgradation_college (College, Branch, Sanctioned_Intake, Permissible_Limit, Total_Vacancy, No_Allowed)
								VALUES ('$college','$branch','$sanctioned_intake','$permissible_limit','$total_vacancy','$allowed_students');";
				
				$result = mysql_query($query, $con) or die(mysql_error());
				
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

?>