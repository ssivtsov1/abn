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


$Query= " (select id,name from syi_user 
        where flag_type =1 order by id) as ss ";

$ldepselect = DbTableSelect($Link,$Query,'id','name');

echo $ldepselect;


?>