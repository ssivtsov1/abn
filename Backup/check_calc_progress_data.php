<?php

set_time_limit(6000); 

//header('Content-type: text/html; charset=utf-8');
header("Content-type: application/json;charset=utf-8");

require 'abon_en_func.php';
require 'abon_ded_func.php';

session_name("session_kaa");
session_start();

error_reporting(1);

$Link = get_database_link($_SESSION,2);
$session_user = $_SESSION['ses_usr_id'];

session_write_close();
//sleep(10);
//$id_paccnt = sql_field_val('id_paccnt', 'int'); 
//$mmgg = sql_field_val('mmgg', 'mmgg'); 

$text = '';
$current_status = 1;

$SQL = "select id,file_name,status from syi_calc_progres_tbl where status <> 1 order by id; ";
//$SQL = "select 1; ";

$result = pg_query($Link, $SQL);
if ($result) {
    
    
    while($row = pg_fetch_array($result)) {

         $fullfilename = "/home/local/calc_abn/".$row['file_name'];
         if ($row['status']==0)
         {
            $fh = fopen($fullfilename,'r');
            $cnt = 0;  
            while ($line = fgets($fh)) {

                $text.= trim($line)."<br>" ; 
            }
            
            fclose($fh); 
            
            $SQL2 = "update syi_calc_progres_tbl set status =1 where id = {$row['id']}; ";
            $result2 = pg_query($Link, $SQL2);
            
         }
         
         if ($row['status']==3)
         {
            $current_status = -1;
         }
       
    }    
    
    echo_result($current_status,$text);

} 
else
{
    
}

?>