<?php
header('Content-type: text/html; charset=utf-8');

require 'abon_en_func.php';
require 'abon_ded_func.php';

session_name("session_kaa");
session_start();

error_reporting(1);

$Link = get_database_link($_SESSION,1);
$session_user = $_SESSION['ses_usr_id'];
 session_write_close();
$month = $_POST['month']; 
$year = $_POST['year']; 

$SQL = " select * from calendar where date_part('month',c_date) = $month and 
    date_part('year',c_date) = $year and holiday = true order by c_date;";

 
$result = pg_query($Link,$SQL) or die("SQL Error: " .pg_last_error($Link).$SQL );
 if (!$result) { print("<br> no records found");}
 else {

 $i = 0; 
 while($row = pg_fetch_array($result)) {

    $data[$i] = $row['c_date'];

    $i++;
 } 
 $count=$i;
 $data['records'] = $count;

}

header("Content-type: application/json;charset=utf-8");
echo json_encode($data);

?>