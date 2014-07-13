<?php

$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'ipu';

$con = mysql_connect($host, $user, $pass);
mysql_select_db($db, $con);

//Change the line below as per document made
$no_of_useless_lines = 1;
$no_of_useless_lines_college = 3;

?>