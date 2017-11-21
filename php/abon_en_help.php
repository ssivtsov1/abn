<?php
header('Content-type: text/html; charset=utf-8');
//
require 'abon_en_func.php';
	session_name("session_kaa");
	session_start();
 error_reporting(0);


 $nm1="АЕ-Допомога)";
 start_mpage($nm1);
 head2_mpage();
 middle_mpage();
 middle_help();
 end_mpage();
;


?>