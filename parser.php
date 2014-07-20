<?php

	//Connect to database
	include 'connection.php';
	
	error_reporting(0);

	/*

	Get list of all colleges, and branches
	(*** Better DS is dictionary ***)
	Make 2 arrays, 1 with only college names,
	And second with all branches in a particular college
	and no of allowed upgrades in each branch

	Parse list of colleges. 
	For each college, with each branch
	get list of students, in descending order of marks.

	Then, iterate over each student, and 
	check for preferences, use the stage plan
	and break if any preference is '--'

	If any student is upgraded, reduce value of vacancies in
	new branch, and increase value of vacancies in old branch.
	Finally push name into final table.
	***Enrollment No to be done at last***

	*/

	//First, Empty table
	
	mysql_query("TRUNCATE upgradation_result");
	
	//First, get all colleges in dictionary format
	// Array("college_name" => array("branch_name"=>"no_allowed",...),...)
	$arr_of_colleges = Array();
	
	//Array of upgraded students
	$final_arr = Array();
	
	$query_get_colleges = "SELECT College FROM upgradation_college GROUP BY College";
	$result = mysql_query($query_get_colleges, $con);
	$num_rows = mysql_num_rows($result);

	//First, get all college names.

	for($i = 0; $i < $num_rows; $i++) {

		$final_array = Array();
		$temp_array = Array();
		
		$arr_of_colleges[mysql_result($result, $i, "College")] = array();

	}
	
	//Next get all branches and allowed upgradations, and put in array at appropriate position.

	$query_branch = "SELECT * FROM upgradation_college";
	$result = mysql_query($query_branch);
	$num_rows = mysql_num_rows($result);

	for($i = 0; $i < $num_rows; $i++) {

		$arr_of_colleges[mysql_result($result, $i, "College")][mysql_result($result, $i, "Branch")] = Array(intval(mysql_result($result, $i, "No_Allowed")), intval(mysql_result($result, $i, "Permissible_Limit")), mysql_result($result, $i, "Sample_Roll_No"), mysql_result($result, $i, "Total_Vacancy"), 0) ;

	}
	
	//Now, we have all colleges. So, first we iterate over all colleges.
	
	$colleges = array_keys($arr_of_colleges);
	for($i = 0;$i < count($colleges); $i++) {	//count($colleges)
		//if($i == 2) {
		
		$college = $colleges[$i];
		//echo $college;
		$diff = 1;
		//Now, get all students from this college in descending order.
		$query_students = "SELECT * FROM upgradation_final WHERE Institute='$college' ORDER BY Percentage DESC";
		$result = mysql_query($query_students, $con) or die(mysql_error());
		$num_rows = mysql_num_rows($result);
		//Array to store student data of each college.
		$arr_of_students = Array();
		
		for ($j = 0; $j < $num_rows; $j++) {
			
			//Array to store data of each student
			$student = Array();
			
			$student['roll'] = mysql_result($result, $j, "Roll_No");
			$student['branch'] = mysql_result($result, $j, "Branch");
			$student['college'] = $college;
			$student['name'] = mysql_result($result, $j, "Name");
			$student['parent'] = mysql_result($result, $j, "Parent_Name");
			$student['percentage'] = mysql_result($result, $j, "Percentage");
			$student['pref1'] = mysql_result($result, $j, "Pref1");
			$student['pref2'] = mysql_result($result, $j, "Pref2");
			$student['pref3'] = mysql_result($result, $j, "Pref3");
			$student['pref4'] = mysql_result($result, $j, "Pref4");
			$student['new_branch'] = "";
			$student['new_roll_no'] = "";
			
			$arr_of_students[] = $student;
		
		}
		//print_r($arr_of_colleges);
		$arr_of_students = parse($arr_of_students, count($arr_of_students));	
		
		for($c = 0; $c < count($arr_of_students); $c++) {
		
			if(strlen($arr_of_students[$c]["new_branch"]) >= 2) {
			
				$roll = $arr_of_students[$c]['roll'];
				$old_branch = $arr_of_students[$c]['branch'];
				$new_branch = $arr_of_students[$c]['new_branch'];
				$name = $arr_of_students[$c]['name'];
				$parent = $arr_of_students[$c]['parent'];
				$percentage = $arr_of_students[$c]['percentage'];
				$new_roll = strval($arr_of_colleges[$college][$new_branch][2]);
				$arr_of_colleges[$college][$new_branch][2] = increment($arr_of_colleges[$college][$new_branch][2]);
				
				$query_final = "INSERT INTO upgradation_result (Roll_No, Name, Parent_Name, Institute, Branch_Old, Branch_New, Percentage, New_Enrollment_No)
									VALUES ('$roll', '$name', '$parent', '$college', '$old_branch', '$new_branch', '$percentage', '$new_roll');";
									
				$result_final = mysql_query($query_final) or die(mysql_error());
				
				$arr_of_colleges[$college][$new_branch][4] += 1;
				$arr_of_colleges[$college][$old_branch][3] += 1;
			
			}
			
			
	
		}	
		
		//print_r($arr_fo_colleges);	
		
	}//}
	update_colleges();
	
	
	
	
	
	
	//Main Parser Function
	function parse($arr_of_students, $main_count) {
	
		global $arr_of_colleges, $con;
		
		for ($k = 0; $k < $main_count; $k++) {
			
			$branch = $arr_of_students[$k]["branch"];
			$roll_no = $arr_of_students[$k]["roll"];
			$college = $arr_of_students[$k]["college"];
			
			//Now, check for each preference, and check if seats are available.
			//If they are, then update branch, and push to database.
			
			$pref1 = clean($arr_of_students[$k]["pref1"]);
			$pref2 = clean($arr_of_students[$k]["pref2"]);
			$pref3 = clean($arr_of_students[$k]["pref3"]);
			$pref4 = clean($arr_of_students[$k]["pref4"]);
			
			$found = false;
			
			if ($arr_of_colleges[$college][$pref1][0] > 0) {

				$found = true;
				$new_branch = $pref1;
				
			} else if ($pref2 != '--' && $pref2 != $pref1 && $pref2 != $branch) {
			
				if ($arr_of_colleges[$college][$pref2][0] > 0 && $arr_of_students[$k]['new_branch'] != $pref1) {
				
					$found = true;
					$new_branch = $pref2;
				
				} else if ($pref3 != '--' && $pref3 != $pref2 && $pref3 != $branch) {
				
					if ($arr_of_colleges[$college][$pref3][0] > 0 && $arr_of_students[$k]['new_branch'] != $pref1 && $arr_of_students[$k]['new_branch'] != $pref2) {
					
						$found = true;
						$new_branch = $pref3;
					
					} else if ($pref4 != '--' && $pref4 != $pref3 && $pref4 != $branch) {
					
						if($arr_of_colleges[$college][$pref4][0] > 0 && $arr_of_students[$k]['new_branch'] != $pref1 && $arr_of_students[$k]['new_branch'] != $pref2 && $arr_of_students[$k]['new_branch'] != $pref3) {

							$found = true;
							$new_branch = $pref4;
						
						}
					
					}
				
				}
			
			}
			
			if($found == true) {
			
				if($arr_of_students[$k]["new_branch"] != $new_branch) {
				
					if(strlen($arr_of_students[$k]["new_branch"] >= 2))	{					
						
						$branch = $arr_of_students[$k]["new_branch"];
						
					}
					
					if ($arr_of_colleges[$college][$branch][0] < $arr_of_colleges[$college][$branch][1])
						$arr_of_colleges[$college][$branch][0] += 1;
					$arr_of_colleges[$college][$new_branch][0] -= 1;
					$arr_of_colleges[$college][$new_branch][1] -= 1;
					
					
					$arr_of_students[$k]["new_branch"] = $new_branch;
					
					$arr_of_students = parse($arr_of_students, $k);
					
				}
				
						
			}
		
		}
		
		return $arr_of_students;
	}
	
	
	
	//Cleaner Function
	function clean($str) {
		
		$str = str_replace("'", "", $str);
		$str = str_replace("/", "", $str);
		$str = str_replace("\\", "", $str);
		$str = str_replace(" ", "", $str);
		if($str != '--')
			$str = str_replace("-", "", $str);
		return $str;
	}
	
	function increment($str) {
	
		if(strlen($str) < 3)
			echo strlen($str);
		$second_half = substr($str, 3, 8);
		
		$no = strval(intval(substr($str, 0, 3)) + 1);
		
		if(strlen($no) == 2) {
		
			$first_half = '0'.$no;
		
		} else {
		
			$first_half = $no;
		
		}
		
		$new_str = $first_half.$second_half;
		return $new_str;
	
	}
	
	function update_colleges() {
	
		global $arr_of_colleges;
		$colleges = array_keys($arr_of_colleges);
		for($i = 0; $i < count($colleges); $i ++) {
		
			$college = $colleges[$i];
			$branches = array_keys($arr_of_colleges[$college]);
			
			for($j = 0; $j < count($branches); $j ++) {
			
				$branch = $branches[$j];
				$no_allotted = $arr_of_colleges[$college][$branch][4];
				$total_vacancy = $arr_of_colleges[$college][$branch][3];				
				$new_roll = $arr_of_colleges[$college][$branch][2];
				
				$remaining_vacancy = $total_vacancy - $no_allotted;
				
				$query = "UPDATE upgradation_college SET No_Allotted = '$no_allotted', No_Vacant = '$remaining_vacancy', New_Sample_Roll_No = '$new_roll' WHERE College='$college' AND Branch = '$branch'";
				
				mysql_query($query) or die(mysql_error());
			
			}
		
		}
	
	}

?>

<html>
<head>
<title>Results Declared</title>
</head>
<body>
The upgradation process has been done. <br><br><a href="display.php">Click Here</a> To Generate Excel File.
</body>
</html>