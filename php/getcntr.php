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

$page = $_POST['page']; // get the requested page
$limit = $_POST['rows']; // get how many rows we want to have into the grid
$sidx = $_POST['sidx']; // get index row - i.e. user click to sort
$sord = $_POST['sord']; // get the direction

 // get how many rows we want to have into the grid

if(!$sidx) $sidx =2;
if(!$limit) $limit = 500;
if(!$page) $page = 1;


$pid_person = $_POST['cntrID'];

$SQL = "select pr.* , p.name as name_post, cl.last_name as name_abon, u1.name as user_name 
        from prs_persons as pr 
        left join clm_abon_tbl as cl on (cl.id = pr.id_abon)
        left join prs_posts as p on (p.id = pr.id_post)
        left join syi_user as u1 on (u1.id = pr.id_person)";
         

//throw new Exception(json_encode($SQL));

$result = pg_query($Link,$SQL) or die("SQL Error: " .pg_last_error($Link) );
 if (!$result) { print("<br> no records found");}
 else {
  
 $cntr=[];
 while($row = pg_fetch_array($result)) {
     $cntr[] = $row;
    
 } 
}

header("Content-type: application/json;charset=utf-8");
echo json_encode($cntr);

?>