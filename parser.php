<?php

	//Connect to database
	include 'connection.php';

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
	$arr_of_colleges = array();
	
	//Keep an array of all upgraded roll numbers
	$arr_roll_nos = array();

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

	$query_branch = "SELECT College, Branch, No_Allowed, Permissible_Limit FROM upgradation_college";
	$result = mysql_query($query_branch);
	$num_rows = mysql_num_rows($result);

	for($i = 0; $i < $num_rows; $i++) {

		$arr_of_colleges[mysql_result($result, $i, "College")][mysql_result($result, $i, "Branch")] = Array(intval(mysql_result($result, $i, "No_Allowed")),intval(mysql_result($result, $i, "Permissible_Limit"))) ;

	}
	
	//Now, we have all colleges. So, first we iterate over all colleges.

	$colleges = array_keys($arr_of_colleges);
	for($i = 0;$i < count($colleges); $i++) {

		$college = $colleges[$i];
		
		$diff = 1;
		//Now, get all students from this college in descending order.
			$diff = parse($college);
			
		
	}
	echo count($arr_roll_nos).'<br>';
	print_r($arr_of_colleges);

	//Cleaner Function
	function clean($str) {
		
		$str = str_replace("'", "", $str);
		$str = str_replace("/", "", $str);
		$str = str_replace("\\", "", $str);
		$str = str_replace(" ", "", $str);
		
		return $str;
	}
	
	function parse($college) {
	
		global $arr_of_colleges, $arr_roll_nos, $con;
		$old_array = $arr_of_colleges;
		
		$roll_nos = implode(',', $arr_roll_nos);
		if(strlen($roll_nos) < 1)
			$roll_nos = "1";
			
		$query_students = "SELECT * FROM upgradation_final WHERE Institute='$college' AND Roll_No NOT IN ('$roll_nos') ORDER BY Percentage DESC";
		$result = mysql_query($query_students, $con) or die(mysql_error());
		$num_rows = mysql_num_rows($result);
		
		for ($j = 0; $j < $num_rows; $j++) {
			
			$branch = mysql_result($result, $j, "Branch");
			$roll_no = mysql_result($result, $j, "Roll_No");
			
			//Now, check for each preference, and check if seats are available.
			//If they are, then update branch, and push to database.
			
			$pref1 = clean(mysql_result($result, $j, "Pref1"));
			$pref2 = clean(mysql_result($result, $j, "Pref2"));
			$pref3 = clean(mysql_result($result, $j, "Pref3"));
			$pref4 = clean(mysql_result($result, $j, "Pref4"));
			
			$found = false;
			
			
			if ($arr_of_colleges[$college][$pref1][0] > 0 && $arr_of_colleges[$college][$pref1][0] < $arr_of_colleges[$college][$pref1][1]) {

				$found = true;
				$new_branch = $pref1;
				
				//Add roll no to array.
				$arr_roll_nos[] = $roll_no; 
			
			} else if ($pref2 != '--' && $pref2 != $pref1 && $pref2 != $branch) {
			
				if ($arr_of_colleges[$college][$pref2][0] > 0 && $arr_of_colleges[$college][$pref2][0] < $arr_of_colleges[$college][$pref2][1]) {
				
					$found = true;
					$new_branch = $pref2;
				
				} else if ($pref3 != '--' && $pref3 != $pref2 && $pref3 != $branch) {
				
					if ($arr_of_colleges[$college][$pref3][0] > 0 && $arr_of_colleges[$college][$pref3][0] < $arr_of_colleges[$college][$pref3][1]) {
					
						$found = true;
						$new_branch = $pref3;
					
					} else if ($pref4 != '--' && $pref4 != $pref3 && $pref4 != $branch) {
					
						if($arr_of_colleges[$college][$pref4][0] > 0 && $arr_of_colleges[$college][$pref4][0] < $arr_of_colleges[$college][$pref4][1]) {

							$found = true;
							$new_branch = $pref4;
						
						}
					
					}
				
				}
			
			}
			
			if($found == true) {
			
				if(!isset($arr_of_colleges[$college][$branch][0]))
					echo $college;
				if ($arr_of_colleges[$college][$branch][0] < $arr_of_colleges[$college][$branch][1])
					$arr_of_colleges[$college][$branch][0] += 1;
				if ($arr_of_colleges[$college][$new_branch][0] > 0)
					$arr_of_colleges[$college][$new_branch][0] -= 1;
				
				$name = mysql_result($result, $j, "Name");
				
				$parent_name = mysql_result($result, $j, "Parent_Name");
				$percentage = mysql_result($result, $j, "Percentage");
				
				$query_final = "INSERT INTO upgradation_result (Roll_No, Name, Parent_Name, Institute, Branch_Old, Branch_New, Percentage)
									VALUES ('$roll_no', '$name', '$parent_name', '$college', '$branch', '$new_branch', '$percentage')
									ON DUPLICATE KEY UPDATE Branch_New = '$new_branch';";
				
				$result_final = mysql_query($query_final, $con) or die(mysql_error().'<br>'.$query_final);
			
			}
		
		}
		
		$arr_roll_nos = array_unique($arr_roll_nos);
		return 0;
	
	}

?>