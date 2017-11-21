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
$base_name = $_SESSION['base_pgname'];

session_write_close();

if(file_exists('/home/local/calc_abn/calc')){

  $command = "/home/local/calc_abn/calc $base_name > /dev/null 2>&1 &";  
    
  //$out = shell_exec($command);
  $out = exec($command);
  //sleep(1);
  echo_result(-1, $out );

}
 else {
  echo_result(2,'file not found');    
}


?>