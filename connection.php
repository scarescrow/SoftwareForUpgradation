<?php

$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'upgradation';

$con = mysql_connect($host, $user, $pass);
mysql_select_db($db, $con);

//Change the line below as per document made
$no_of_useless_lines = 2;
$no_of_useless_lines_in_verified_percentage = 1;
$no_of_useless_lines_college = 3;

?>